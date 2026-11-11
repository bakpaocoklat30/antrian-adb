<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\ServiceDate;
use App\Models\Setting;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TicketController extends Controller
{
    public function create() { 
        $setting = Setting::first();
        return view('frontend.ticket.create', compact('setting')); 
    }

    public function store(Request $request)
    {
        $setting = Setting::first();
        if ($setting) {
            $isMaintenance = $setting->is_maintenance == 1;
            $openAt = $setting->spmb_open_at ? Carbon::parse($setting->spmb_open_at) : null;
            if ($isMaintenance || ($openAt && now()->lt($openAt))) {
                return back()->with('error', 'AKSES DITOLAK! Pendaftaran SPMB masih ditutup atau sedang dalam perbaikan.');
            }
        }

        $request->validate([
            'nisn' => 'required|numeric|unique:tickets,nisn',
            'nama_cpd' => 'required|string|max:255',
            'asal_sekolah' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'email' => 'required|email',
            'alamat' => 'required|string',
            'jalur_pendaftaran' => 'required|in:KIP,DTKS,KIP & DTKS,REGULER',
        ], ['nisn.unique' => 'NISN ini sudah terdaftar. 1 Siswa hanya boleh mengambil 1 antrian.']);

        $todayAbsolute = Carbon::now('Asia/Jakarta')->toDateString();

        $availableSession = ServiceDate::where('is_active', true)
                            ->whereDate('service_date', '>=', $todayAbsolute) 
                            ->whereColumn('booked_count', '<', 'quota')
                            ->orderBy('service_date', 'asc')
                            ->orderBy('start_time', 'asc')
                            ->first();

        if (!$availableSession) {
            return back()->with('error', 'Mohon maaf, kuota antrian saat ini sudah penuh atau belum ada jadwal baru yang dibuka.');
        }

        $prefix = $availableSession->session_code ? strtoupper($availableSession->session_code) : strtoupper(substr($availableSession->session_name, 0, 1));
        
        // 🔥 FITUR BARU: Menggunakan Generator Anti Tabrakan 🔥
        $generated = $this->generateSequenceNumber($prefix);
        $ticketNumber = $generated->full;

        if (!Storage::disk('public')->exists('qrcodes')) {
            Storage::disk('public')->makeDirectory('qrcodes');
        }
        
        $qrPath = 'qrcodes/' . $ticketNumber . '.svg';
        QrCode::format('svg')->size(300)->generate(route('ticket.download', $ticketNumber), storage_path('app/public/' . $qrPath));

        $ticket = Ticket::create([
            'ticket_number' => $ticketNumber, 
            'nisn' => $request->nisn, 
            'nama_cpd' => $request->nama_cpd,
            'asal_sekolah' => $request->asal_sekolah, 
            'whatsapp' => $request->whatsapp, 
            'email' => $request->email,
            'alamat' => $request->alamat, 
            'jalur_pendaftaran' => $request->jalur_pendaftaran,
            'service_date_id' => $availableSession->id, 
            'status' => 'menunggu', 
            'qr_code_path' => $qrPath,
        ]);
        
        $availableSession->increment('booked_count');
        
        return redirect()->route('ticket.success', $ticket->ticket_number)->with('success', 'Tiket berhasil digenerate!');
    }

    public function success($ticket_number) { 
        $ticket = Ticket::with('serviceDate')->where('ticket_number', $ticket_number)->firstOrFail(); 
        return view('frontend.ticket.success', compact('ticket')); 
    }
    
    public function download($ticket_number) { 
        $ticket = Ticket::with('serviceDate')->where('ticket_number', $ticket_number)->firstOrFail(); 
        $pdf = Pdf::loadView('frontend.ticket.pdf_template', compact('ticket'))->setPaper('A4', 'portrait'); 
        return $pdf->download('Tiket_SPMB_' . $ticket->nama_cpd . '_' . $ticket->nisn . '.pdf'); 
    }
    
    public function reprint(Request $request) { 
        $ticket = Ticket::where('nisn', $request->nisn)->first(); 
        if($ticket) return response()->json(['success' => true, 'redirect' => route('ticket.success', $ticket->ticket_number)]); 
        return response()->json(['success' => false, 'message' => 'NISN tidak terdaftar.']); 
    }

    public function layar() { 
        $setting = Setting::firstOrCreate(['id' => 1], [
            'running_text' => 'Selamat Datang Calon Peserta Didik Baru!', 
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ?autoplay=1&mute=0&loop=1&playlist=dQw4w9WgXcQ', 
            'video_type' => 'youtube',
            'video_volume' => 60,
            'announcer_text' => 'Nomor Antrian... {nomor}... Silakan menuju ke... Loket... {loket}'
        ]);
        return view('frontend.ticket.layar', compact('setting')); 
    }
    
    public function apiLayar() {
        $dipanggil = Ticket::where('status', 'dipanggil')->orderBy('updated_at', 'desc')->first();
        
        $activeData = null;
        if ($dipanggil) { 
            $loketUser = \App\Models\User::find($dipanggil->loket_id); 
            $activeData = [
                'ticket' => explode('-', $dipanggil->ticket_number)[0], 
                'loket' => $loketUser ? $loketUser->loket_number : '-',
                'call_count' => $dipanggil->call_count 
            ]; 
        }
        
        $loketsData = [];
        for ($i = 1; $i <= 7; $i++) {
            $userLoket = \App\Models\User::where('loket_number', $i)->first();
            
            $latest = Ticket::join('users', 'tickets.loket_id', '=', 'users.id')
                ->where('users.loket_number', $i)
                ->whereIn('tickets.status', ['dipanggil', 'menghadap', 'selesai'])
                ->orderBy('tickets.updated_at', 'desc')
                ->select('tickets.ticket_number')
                ->first();

            $isTutup = $userLoket && isset($userLoket->status_loket) && $userLoket->status_loket === 'tutup';
            $keterangan = $isTutup ? ($userLoket->keterangan_loket ?? 'ISTIRAHAT') : ($latest ? explode('-', $latest->ticket_number)[0] : '---');

            $loketsData[] = [
                'loket' => $i,
                'is_tutup' => $isTutup, 
                'ticket' => $keterangan
            ];
        }

        $waitingList = Ticket::where('status', 'hadir_di_lokasi')
            ->orderBy('updated_at', 'asc') 
            ->take(8) 
            ->get()
            ->map(function($t) {
                return [
                    'ticket' => explode('-', $t->ticket_number)[0],
                    'nama' => strtoupper(\Illuminate\Support\Str::limit($t->nama_cpd, 20))
                ];
            });

        $history = Ticket::whereIn('status', ['dipanggil', 'menghadap'])
                    ->orderBy('updated_at', 'desc')->take(5)->get();
        $historyData = $history->map(function($t) { 
            $loketUser = \App\Models\User::find($t->loket_id); 
            return [
                'ticket' => explode('-', $t->ticket_number)[0], 
                'loket' => $loketUser ? $loketUser->loket_number : '-', 
                'status' => $t->status,
                'call_count' => $t->call_count 
            ]; 
        });

        $setting = \App\Models\Setting::first();
        $template = $setting->announcer_text ?? 'Nomor Antrian, {nomor}, silakan menuju ke, loket, {loket}';

        return response()->json([
            'success' => true, 
            'active' => $activeData, 
            'lokets' => $loketsData,
            'waiting' => $waitingList,
            'history' => $historyData, 
            'template' => $template,
            'setting' => $setting 
        ]);
    }
}