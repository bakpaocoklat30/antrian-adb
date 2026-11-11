<?php

namespace App\Http\Controllers;

use App\Models\ServiceDate;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function create()
    {
        $setting = Setting::first();

        return view('frontend.ticket.create', compact('setting'));
    }

    public function store(Request $request)
    {
        $setting = Setting::first();
        if ($setting) {
            $tz = 'Asia/Jakarta';
            $isMaintenance = (bool) $setting->is_maintenance;
            $isExhausted = (bool) $setting->is_ticket_exhausted;
            $openAt = $setting->spmb_open_at ? Carbon::parse($setting->spmb_open_at, $tz) : null;

            if ($isMaintenance || $isExhausted || ($openAt && now($tz)->lt($openAt))) {
                return back()->with('error', 'AKSES DITOLAK! Pendaftaran antrian saat ini ditutup atau sedang dalam perbaikan.');
            }
        }

        $validated = $request->validate([
            'nisn' => 'required|numeric|unique:tickets,nisn',
            'nama_cpd' => 'required|string|max:255',
            'asal_sekolah' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'email' => 'required|email',
            'alamat' => 'required|string',
            'jalur_pendaftaran' => 'required|in:KIP,DTKS,KIP & DTKS,REGULER',
        ], ['nisn.unique' => 'NISN ini sudah terdaftar. 1 Siswa hanya boleh mengambil 1 antrian.']);

        $todayAbsolute = Carbon::now('Asia/Jakarta')->toDateString();

        $ticket = DB::transaction(function () use ($validated, $todayAbsolute) {
            $availableSession = ServiceDate::where('is_active', true)
                ->whereDate('service_date', '>=', $todayAbsolute)
                ->whereColumn('booked_count', '<', 'quota')
                ->orderBy('service_date', 'asc')
                ->orderBy('start_time', 'asc')
                ->lockForUpdate()
                ->first();

            if (!$availableSession) {
                return null;
            }

            $generated = $this->generateSequenceNumber($this->sessionPrefix($availableSession));

            $created = Ticket::create([
                'ticket_number' => $generated->full,
                'nisn' => $validated['nisn'],
                'nama_cpd' => $validated['nama_cpd'],
                'asal_sekolah' => $validated['asal_sekolah'],
                'whatsapp' => $validated['whatsapp'],
                'email' => $validated['email'],
                'alamat' => $validated['alamat'],
                'jalur_pendaftaran' => $validated['jalur_pendaftaran'],
                'service_date_id' => $availableSession->id,
                'status' => 'menunggu',
                'qr_code_path' => null,
            ]);

            $availableSession->increment('booked_count');

            return $created;
        });

        if (!$ticket) {
            return back()
                ->withInput()
                ->with('error', 'Mohon maaf, kuota antrian saat ini sudah penuh atau belum ada jadwal baru yang dibuka.');
        }

        return redirect()->route('ticket.success', $ticket->ticket_number)
            ->with('success', 'Tiket berhasil digenerate!');
    }

    public function success(string $ticket_number)
    {
        $ticket = Ticket::with('serviceDate')->where('ticket_number', $ticket_number)->firstOrFail();

        return view('frontend.ticket.success', compact('ticket'));
    }

    public function download(string $ticket_number)
    {
        $ticket = Ticket::with('serviceDate')->where('ticket_number', $ticket_number)->firstOrFail();
        $pdf = Pdf::loadView('frontend.ticket.pdf_template', compact('ticket'))->setPaper('A4', 'portrait');

        return $pdf->download('Tiket_SPMB_' . $ticket->nama_cpd . '_' . $ticket->nisn . '.pdf');
    }

    public function reprint(Request $request)
    {
        $request->validate(['nisn' => 'required|string']);

        $ticket = Ticket::where('nisn', $request->nisn)->first();
        if ($ticket) {
            return response()->json([
                'success' => true,
                'redirect' => route('ticket.success', $ticket->ticket_number),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'NISN tidak terdaftar.']);
    }

    public function layar()
    {
        $setting = Setting::firstOrCreate(['id' => 1], [
            'running_text' => 'Selamat Datang Calon Peserta Didik Baru!',
            'video_url' => '',
            'video_type' => 'youtube',
            'video_volume' => 60,
            'announcer_text' => 'Nomor Antrian... {nomor}... Silakan menuju ke... Loket... {loket}',
            'voice_speed' => 0.80,
            'voice_pitch' => 1.00,
        ]);

        return view('frontend.ticket.layar', compact('setting'));
    }

    public function apiLayar()
    {
        $dipanggil = Ticket::with('verifikator')
            ->where('status', 'dipanggil')
            ->orderBy('updated_at', 'desc')
            ->first();

        $activeData = null;
        if ($dipanggil) {
            $activeData = [
                'ticket' => explode('-', $dipanggil->ticket_number)[0],
                'loket' => $dipanggil->verifikator ? $dipanggil->verifikator->loket_number : '-',
                'call_count' => $dipanggil->call_count,
            ];
        }

        $verifikators = User::where('role', 'verifikator')
            ->where('is_active', true)
            ->whereNotNull('loket_number')
            ->orderBy('loket_number', 'asc')
            ->get();

        $latestTicketsByLoket = collect();
        if ($verifikators->isNotEmpty()) {
            $latestTicketsByLoket = Ticket::whereIn('loket_id', $verifikators->pluck('id'))
                ->whereIn('status', ['dipanggil', 'menghadap', 'selesai'])
                ->orderBy('updated_at', 'desc')
                ->get()
                ->unique('loket_id')
                ->keyBy('loket_id');
        }

        $loketsData = $verifikators->map(function ($userLoket) use ($latestTicketsByLoket) {
            $latest = $latestTicketsByLoket->get($userLoket->id);
            $isTutup = $userLoket->status_loket === 'tutup';
            $keterangan = $isTutup
                ? ($userLoket->keterangan_loket ?? 'ISTIRAHAT')
                : ($latest ? explode('-', $latest->ticket_number)[0] : '---');

            return [
                'loket' => $userLoket->loket_number,
                'is_tutup' => $isTutup,
                'ticket' => $keterangan,
            ];
        })->values();

        $waitingList = Ticket::where('status', 'hadir_di_lokasi')
            ->orderBy('updated_at', 'asc')
            ->take(8)
            ->get()
            ->map(function ($t) {
                return [
                    'ticket' => explode('-', $t->ticket_number)[0],
                    'nama' => strtoupper(Str::limit($t->nama_cpd, 20)),
                ];
            });

        $historyData = Ticket::with('verifikator')
            ->whereIn('status', ['dipanggil', 'menghadap'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($t) {
                return [
                    'ticket' => explode('-', $t->ticket_number)[0],
                    'loket' => $t->verifikator ? $t->verifikator->loket_number : '-',
                    'status' => $t->status,
                    'call_count' => $t->call_count,
                ];
            });

        $setting = Setting::first();
        $template = $setting->announcer_text ?? 'Nomor Antrian, {nomor}, silakan menuju ke, loket, {loket}';

        return response()->json([
            'success' => true,
            'active' => $activeData,
            'lokets' => $loketsData,
            'waiting' => $waitingList,
            'history' => $historyData,
            'template' => $template,
            'setting' => $setting,
        ]);
    }
}
