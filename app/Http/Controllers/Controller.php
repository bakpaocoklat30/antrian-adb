<?php

namespace App\Http\Controllers;

use App\Models\ServiceDate;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class Controller
{
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
     * Estimasi waktu tunggu (menit) berdasarkan antrian di depan dan jumlah loket aktif.
     */
    protected function estimateWaitMinutes(int $aheadCount): int
    {
        return (int) ceil(($aheadCount / $this->activeCounterCount()) * 5);
    }
}
