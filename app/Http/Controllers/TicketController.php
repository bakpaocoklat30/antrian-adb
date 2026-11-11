<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ServiceDate;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    public function create()
    {
        $setting = Setting::first();
        $activeEvents = Event::where('is_active', true)
            ->withSum(['serviceDates as total_quota' => fn ($q) => $q->where('is_active', true)->whereDate('service_date', '>=', Carbon::now('Asia/Jakarta')->toDateString())], 'quota')
            ->withSum(['serviceDates as total_booked' => fn ($q) => $q->where('is_active', true)->whereDate('service_date', '>=', Carbon::now('Asia/Jakarta')->toDateString())], 'booked_count')
            ->orderBy('id', 'asc')
            ->get();

        if ($this->isGloballyLocked($setting)) {
            $event = $activeEvents->first();

            return view('frontend.ticket.create', compact('setting', 'event'));
        }

        if ($activeEvents->count() === 1) {
            $event = $activeEvents->first();

            return view('frontend.ticket.create', compact('setting', 'event'));
        }

        return view('frontend.ticket.events', compact('setting', 'activeEvents'));
    }

    public function showEvent(string $slug)
    {
        $setting = Setting::first();
        $event = Event::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('frontend.ticket.create', compact('setting', 'event'));
    }

    public function store(Request $request)
    {
        $setting = Setting::first();
        if ($this->isGloballyLocked($setting)) {
            return back()->with('error', 'AKSES DITOLAK! Pendaftaran antrian saat ini ditutup atau sedang dalam perbaikan.');
        }

        $event = Event::where('is_active', true)->findOrFail($request->input('event_id'));

        $payload = $this->validateEventSubmission(
            $event,
            $request,
            ($event->identifier_label ?: 'Identitas') . ' ini sudah terdaftar pada event ' . $event->name . '. 1 peserta hanya boleh mengambil 1 antrian per event.'
        );

        $todayAbsolute = Carbon::now('Asia/Jakarta')->toDateString();

        $ticket = DB::transaction(function () use ($event, $payload, $todayAbsolute) {
            $availableSession = ServiceDate::where('event_id', $event->id)
                ->where('is_active', true)
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
                'event_id' => $event->id,
                'ticket_number' => $generated->full,
                'nisn' => $payload['nisn'],
                'nama_cpd' => $payload['nama_cpd'],
                'asal_sekolah' => $payload['asal_sekolah'],
                'whatsapp' => $payload['whatsapp'],
                'email' => $payload['email'],
                'alamat' => $payload['alamat'],
                'jalur_pendaftaran' => $payload['jalur_pendaftaran'],
                'form_data' => $payload['form_data'],
                'service_date_id' => $availableSession->id,
                'status' => $event->requires_checkin ? 'menunggu' : 'hadir_di_lokasi',
                'qr_code_path' => null,
            ]);

            $availableSession->increment('booked_count');

            return $created;
        });

        if (!$ticket) {
            return back()
                ->withInput()
                ->with('error', 'Mohon maaf, kuota antrian untuk event "' . $event->name . '" saat ini sudah penuh atau belum ada jadwal baru yang dibuka.');
        }

        return redirect()->route('ticket.success', $ticket->ticket_number)
            ->with('success', 'Tiket berhasil digenerate!');
    }

    public function success(string $ticket_number)
    {
        $ticket = Ticket::with(['serviceDate', 'event'])->where('ticket_number', $ticket_number)->firstOrFail();
        $antrianDiDepan = $ticket->status === 'hadir_di_lokasi' ? $this->countAheadInQueue($ticket) : 0;
        $estimasiWaktu = $this->estimateWaitMinutes($antrianDiDepan);

        return view('frontend.ticket.success', compact('ticket', 'antrianDiDepan', 'estimasiWaktu'));
    }

    public function download(string $ticket_number)
    {
        $ticket = Ticket::with(['serviceDate', 'event'])->where('ticket_number', $ticket_number)->firstOrFail();
        $pdf = Pdf::loadView('frontend.ticket.pdf_template', compact('ticket'))->setPaper('A4', 'portrait');

        return $pdf->download('Tiket_' . Str::slug($ticket->event?->name ?? 'Antrian') . '_' . $ticket->nama_cpd . '_' . $ticket->nisn . '.pdf');
    }

    public function reprint(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string',
            'event_id' => 'nullable|integer',
        ]);

        $query = Ticket::where('nisn', trim($request->nisn));
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        } else {
            $query->orderBy('id', 'desc');
        }

        $ticket = $query->first();
        if ($ticket) {
            return response()->json([
                'success' => true,
                'redirect' => route('ticket.success', $ticket->ticket_number),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Data identitas tidak ditemukan dalam antrian.']);
    }

    public function layar()
    {
        $setting = Setting::firstOrCreate(['id' => 1], [
            'running_text' => 'Selamat Datang! Silakan Pantau Nomor Antrian Anda di Layar.',
            'video_url' => '',
            'video_type' => 'youtube',
            'video_volume' => 60,
            'announcer_text' => 'Nomor antrian, {nomor}, silakan menuju ke loket {loket}',
            'voice_engine' => 'server_id',
            'voice_speed' => 1.00,
            'voice_pitch' => 1.00,
        ]);

        $activeEvents = Event::where('is_active', true)->orderBy('id', 'asc')->get();

        return view('frontend.ticket.layar', compact('setting', 'activeEvents'));
    }

    /**
     * Endpoint proxy + cache lokal audio TTS Bahasa Indonesia (MP3).
     */
    public function apiTts(Request $request)
    {
        $validated = $request->validate([
            'q' => 'required|string|max:240',
        ]);

        $cleanText = preg_replace('/\s+/', ' ', trim($validated['q']));
        if ($cleanText === '') {
            return response()->json(['success' => false, 'message' => 'Teks kosong.'], 422);
        }

        $cacheDir = storage_path('app/tts-cache');
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }

        $cachePath = $cacheDir . DIRECTORY_SEPARATOR . sha1('id:' . $cleanText) . '.mp3';
        $headers = [
            'Content-Type' => 'audio/mpeg',
            'Cache-Control' => 'public, max-age=86400',
        ];

        if (is_file($cachePath) && filesize($cachePath) > 256) {
            return response()->file($cachePath, $headers);
        }

        try {
            $upstream = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36',
                    'Referer' => 'https://translate.google.com/',
                ])
                ->get('https://translate.google.com/translate_tts', [
                    'ie' => 'UTF-8',
                    'q' => $cleanText,
                    'tl' => 'id',
                    'client' => 'tw-ob',
                ]);

            $body = $upstream->body();
            $contentType = strtolower((string) $upstream->header('Content-Type'));
            $looksLikeMp3 = strlen($body) > 256
                && (str_contains($contentType, 'audio') || str_starts_with($body, 'ID3') || ord($body[0]) === 0xFF);

            if ($upstream->successful() && $looksLikeMp3) {
                @file_put_contents($cachePath, $body, LOCK_EX);

                return response($body, 200, $headers);
            }
        } catch (\Throwable $e) {
            // Biarkan frontend fallback ke voice Bahasa Indonesia lokal jika offline/gagal
        }

        return response()->json(['success' => false, 'message' => 'Server TTS sedang tidak terjangkau.'], 503);
    }

    public function apiLayar()
    {
        $dipanggil = Ticket::with(['verifikator', 'event'])
            ->where('status', 'dipanggil')
            ->orderBy('updated_at', 'desc')
            ->first();

        $activeData = null;
        if ($dipanggil) {
            $activeData = [
                'ticket' => explode('-', $dipanggil->ticket_number)[0],
                'loket' => $dipanggil->verifikator ? $dipanggil->verifikator->loket_number : '-',
                'call_count' => $dipanggil->call_count,
                'event_name' => $dipanggil->event?->name,
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

        $waitingList = Ticket::with('event')
            ->where('status', 'hadir_di_lokasi')
            ->orderBy('updated_at', 'asc')
            ->take(8)
            ->get()
            ->map(function ($t) {
                return [
                    'ticket' => explode('-', $t->ticket_number)[0],
                    'nama' => strtoupper(Str::limit($t->nama_cpd, 20)),
                    'event_name' => $t->event?->name,
                ];
            });

        $historyData = Ticket::with(['verifikator', 'event'])
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
                    'event_name' => $t->event?->name,
                ];
            });

        $setting = Setting::first();
        $template = $setting->announcer_text ?? 'Nomor antrian, {nomor}, silakan menuju ke loket {loket}';

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

    private function isGloballyLocked(?Setting $setting): bool
    {
        if (!$setting) {
            return false;
        }

        $tz = 'Asia/Jakarta';
        $isMaintenance = (bool) $setting->is_maintenance;
        $isExhausted = (bool) $setting->is_ticket_exhausted;
        $openAt = $setting->spmb_open_at ? Carbon::parse($setting->spmb_open_at, $tz) : null;

        return $isMaintenance || $isExhausted || ($openAt && now($tz)->lt($openAt));
    }
}
