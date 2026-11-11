<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ServiceDate;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

abstract class Controller
{
    /**
     * Validasi dan ekstrak data form dinamis berdasarkan konfigurasi Event.
     */
    protected function validateEventSubmission(Event $event, Request $request, string $duplicateMessage): array
    {
        $rules = array_merge([
            'nisn' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tickets', 'nisn')->where('event_id', $event->id),
            ],
            'nama_cpd' => ['required', 'string', 'max:255'],
        ], $event->dynamicValidationRules());

        $attributes = [
            'nisn' => $event->identifier_label ?: 'Identitas',
            'nama_cpd' => $event->name_label ?: 'Nama Lengkap',
        ];
        foreach ($event->form_fields ?? [] as $field) {
            if (!empty($field['key'])) {
                $attributes['fields.' . $field['key']] = $field['label'] ?? $field['key'];
            }
        }

        $validated = $request->validate($rules, [
            'nisn.unique' => $duplicateMessage,
        ], $attributes);

        $formEntries = $event->extractFormData($request);
        $flatByKey = [];
        foreach ($formEntries as $entry) {
            $flatByKey[$entry['key']] = $entry['value'] === '-' ? null : $entry['value'];
        }

        return [
            'nisn' => trim((string) $validated['nisn']),
            'nama_cpd' => trim((string) $validated['nama_cpd']),
            'asal_sekolah' => $flatByKey['asal_sekolah'] ?? null,
            'whatsapp' => $flatByKey['whatsapp'] ?? null,
            'email' => $flatByKey['email'] ?? null,
            'alamat' => $flatByKey['alamat'] ?? null,
            'jalur_pendaftaran' => $flatByKey['jalur_pendaftaran'] ?? null,
            'form_data' => $formEntries,
        ];
    }

    /**
     * Generate nomor urut tiket secara atomik di dalam transaksi database.
     */
    protected function generateSequenceNumber(string $prefix): object
    {
        return DB::transaction(function () use ($prefix) {
            DB::table('settings')->insertOrIgnore([
                'id' => 1,
                'global_sequence' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $currentSeq = (int) DB::table('settings')
                ->where('id', 1)
                ->lockForUpdate()
                ->value('global_sequence');

            $recentTickets = DB::table('tickets')
                ->orderBy('id', 'desc')
                ->take(100)
                ->pluck('ticket_number');

            $trueMax = 0;
            foreach ($recentTickets as $tn) {
                $parts = explode('-', $tn);
                if (isset($parts[0])) {
                    $num = (int) preg_replace('/[^0-9]/', '', $parts[0]);
                    if ($num > $trueMax) {
                        $trueMax = $num;
                    }
                }
            }

            $nextSequence = max($currentSeq, $trueMax) + 1;

            DB::table('settings')
                ->where('id', 1)
                ->update([
                    'global_sequence' => $nextSequence,
                    'updated_at' => now(),
                ]);

            $shortNumber = $prefix . str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
            $fullNumber = $shortNumber . '-' . strtoupper(Str::random(4));

            return (object) [
                'short' => $shortNumber,
                'full' => $fullNumber,
            ];
        });
    }

    /**
     * Ambil prefix kode sesi dari session_code atau huruf pertama nama sesi.
     */
    protected function sessionPrefix(ServiceDate $session): string
    {
        return $session->session_code
            ? strtoupper($session->session_code)
            : strtoupper(substr($session->session_name, 0, 1));
    }

    /**
     * Jumlah loket verifikator aktif untuk perhitungan estimasi tunggu.
     */
    protected function activeCounterCount(): int
    {
        $count = User::where('role', 'verifikator')
            ->where('is_active', true)
            ->whereNotNull('loket_number')
            ->count();

        return max(1, $count);
    }

    /**
     * Hitung jumlah antrian di depan tiket ini pada sesi yang sama.
     */
    protected function countAheadInQueue(Ticket $ticket): int
    {
        return Ticket::where('service_date_id', $ticket->service_date_id)
            ->where('status', 'hadir_di_lokasi')
            ->where(function ($query) use ($ticket) {
                $timestamp = $ticket->updated_at ?? now();
                $query->where('updated_at', '<', $timestamp)
                    ->orWhere(function ($sub) use ($timestamp, $ticket) {
                        $sub->where('updated_at', $timestamp)->where('id', '<', $ticket->id);
                    });
            })
            ->count();
    }

    /**
     * Estimasi waktu tunggu (menit) berdasarkan antrian di depan dan jumlah loket aktif.
     */
    protected function estimateWaitMinutes(int $aheadCount): int
    {
        return (int) ceil(($aheadCount / $this->activeCounterCount()) * 5);
    }
}
