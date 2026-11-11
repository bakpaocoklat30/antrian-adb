<?php

namespace App\Http\Controllers;

use App\Models\ServiceDate;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        if (Auth::user()->role === 'super_admin') {
            $stats = [
                'total' => Ticket::count(),
                'hadir' => Ticket::whereIn('status', ['hadir_di_lokasi', 'dipanggil', 'menghadap', 'selesai'])->count(),
                'selesai' => Ticket::where('status', 'selesai')->count(),
            ];
            $setting = Setting::first();

            return view('backend.dashboard.superadmin', compact('stats', 'setting'));
        }

        $aPerPage = $request->query('a_per_page', 5);

        $antrianHadir = Ticket::where('status', 'hadir_di_lokasi')
            ->orderBy('updated_at', 'asc')
            ->paginate($aPerPage, ['*'], 'a_page')
            ->appends($request->except('a_page'));

        $riwayatHariIni = Ticket::where('loket_id', Auth::id())
            ->whereIn('status', ['dipanggil', 'menghadap', 'selesai', 'dilewati'])
            ->whereDate('updated_at', Carbon::today('Asia/Jakarta'))
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('backend.dashboard.index', compact('antrianHadir', 'riwayatHariIni'));
    }

    public function scanner()
    {
        return view('backend.scanner.index');
    }

    public function scannerCheckin(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|string|max:100',
        ]);

        $input = trim($request->ticket_number);
        if (str_contains($input, '/ticket/download/')) {
            $parts = explode('/', rtrim($input, '/'));
            $input = end($parts);
        }

        $ticket = Ticket::with('serviceDate')
            ->where('ticket_number', $input)
            ->orWhere('nisn', $input)
            ->first();

        if (!$ticket || !$ticket->serviceDate) {
            return response()->json(['success' => false, 'message' => 'Tiket Tidak Dikenali / Tidak Terdaftar!']);
        }

        if (!$ticket->serviceDate->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'DITOLAK! Sesi "' . $ticket->serviceDate->session_name . '" untuk tiket ini SUDAH DITUTUP oleh Admin. Siswa tidak dapat melakukan Check-in.',
            ]);
        }

        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $ticketDate = Carbon::parse($ticket->serviceDate->service_date)->format('Y-m-d');
        $formattedTicketDate = Carbon::parse($ticketDate)->translatedFormat('d F Y');

        if ($ticketDate < $today) {
            return response()->json([
                'success' => false,
                'message' => "Tiket KADALUARSA!\nJadwal siswa ini adalah $formattedTicketDate. Silakan arahkan siswa ke Meja Panitia untuk mutasi jadwal.",
            ]);
        }

        if ($ticketDate > $today) {
            return response()->json([
                'success' => false,
                'message' => "Tiket BELUM AKTIF!\nJadwal siswa ini untuk tanggal $formattedTicketDate. Tidak bisa Check-In hari ini.",
            ]);
        }

        if ($ticket->status === 'hadir_di_lokasi') {
            return response()->json(['success' => false, 'message' => 'Siswa ini SUDAH Check-In sebelumnya!']);
        }

        if ($ticket->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Status tiket sudah tidak valid (mungkin sudah dipanggil atau selesai diproses).']);
        }

        $ticket->status = 'hadir_di_lokasi';
        $ticket->save();

        $antrianDiDepan = Ticket::where('service_date_id', $ticket->service_date_id)
            ->where('status', 'hadir_di_lokasi')
            ->where('updated_at', '<', $ticket->updated_at ?? now())
            ->count();

        return response()->json([
            'success' => true,
            'message' => 'Check-In Berhasil! ' . $ticket->nama_cpd,
            'ticket_short' => explode('-', $ticket->ticket_number)[0],
            'antrian_di_depan' => $antrianDiDepan,
            'estimasi_waktu' => $this->estimateWaitMinutes($antrianDiDepan),
        ]);
    }

    public function manageSessions(Request $request)
    {
        $query = ServiceDate::query();

        if ($request->filled('search')) {
            $query->where('session_name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('date')) {
            $query->whereDate('service_date', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $sdPerPage = $request->query('sd_per_page', 5);
        $serviceDates = $query->orderBy('service_date', 'desc')
            ->paginate($sdPerPage, ['*'], 'sd_page')
            ->appends($request->except('sd_page'));

        return view('backend.dashboard.sessions', compact('serviceDates'));
    }

    public function manageUsers(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('role', 'like', '%' . $request->search . '%');
            });
        }

        $perPage = $request->u_per_page ?? 5;
        $users = $query->orderByRaw("CASE WHEN role = 'super_admin' THEN 1 ELSE 0 END")
            ->orderBy('role', 'asc')
            ->orderBy('loket_number', 'asc')
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        $usedLokets = User::whereNotNull('loket_number')->pluck('loket_number')->toArray();

        return view('backend.dashboard.users', compact('users', 'usedLokets'));
    }

    public function manageDisplay()
    {
        $setting = Setting::first() ?? new Setting();

        $existingVideos = [];
        if (Storage::disk('public')->exists('videos')) {
            $existingVideos = Storage::disk('public')->files('videos');
        }

        return view('backend.dashboard.display', compact('setting', 'existingVideos'));
    }

    public function updateDisplay(Request $request)
    {
        $request->validate([
            'running_text' => 'required|string',
            'announcer_text' => 'required|string',
            'video_type' => 'required|in:youtube,local',
            'video_url_youtube' => 'nullable|string|max:500',
            'video_file' => 'nullable|file|mimetypes:video/mp4|max:512000',
            'existing_video' => 'nullable|string|max:500',
        ]);

        $setting = Setting::first() ?? new Setting();
        $setting->running_text = $request->running_text;
        $setting->announcer_text = $request->announcer_text;
        $setting->video_type = $request->video_type;

        if ($request->video_type === 'youtube') {
            $setting->video_url = $request->video_url_youtube;
        } else {
            if ($request->hasFile('video_file')) {
                $file = $request->file('video_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $setting->video_url = $file->storeAs('videos', $filename, 'public');
            } elseif ($request->filled('existing_video') && str_starts_with($request->existing_video, 'videos/')) {
                $setting->video_url = $request->existing_video;
            }
        }
        $setting->save();

        return redirect()->back()->with('success', 'Pengaturan Teks & Video berhasil disimpan!');
    }

    public function updateLiveSetting(Request $request)
    {
        $setting = Setting::first();

        if ($setting) {
            $field = $request->field;
            $allowed = ['video_volume', 'voice_speed', 'voice_pitch'];

            if (in_array($field, $allowed, true)) {
                $setting->$field = $request->value;
                $setting->save();
            }
        }

        return response()->json(['success' => true]);
    }

    public function attendanceTracker(Request $request)
    {
        $query = Ticket::with('serviceDate');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_cpd', 'like', '%' . $request->search . '%')
                    ->orWhere('nisn', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('date_id')) {
            $query->where('service_date_id', $request->date_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->orderByRaw("CASE status
                WHEN 'menunggu' THEN 1
                WHEN 'dilewati' THEN 2
                WHEN 'hadir_di_lokasi' THEN 3
                WHEN 'dipanggil' THEN 4
                WHEN 'menghadap' THEN 5
                WHEN 'selesai' THEN 6
                ELSE 7
            END");
        }

        if ($request->has('export') && $request->export === 'true') {
            $tickets = $query->orderBy('nama_cpd', 'asc')->get();
            $filename = 'Rekap_Kehadiran_Peserta_' . date('Y-m-d_H-i') . '.csv';

            $headers = [
                'Content-type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=$filename",
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $columns = [
                'ID Tiket Penuh',
                'Nomor Antrian',
                'Nama Lengkap',
                'NISN',
                'Asal Sekolah',
                'WhatsApp',
                'Email',
                'Alamat Lengkap',
                'Data Kesejahteraan',
                'Tanggal Sesi',
                'Waktu Sesi',
                'Status Kehadiran',
                'Catatan Verifikator',
            ];

            $callback = function () use ($tickets, $columns) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF");
                fputcsv($file, $columns);

                foreach ($tickets as $ticket) {
                    $sd = $ticket->serviceDate;
                    fputcsv($file, [
                        $ticket->ticket_number,
                        explode('-', $ticket->ticket_number)[0],
                        strtoupper($ticket->nama_cpd),
                        "'" . $ticket->nisn,
                        $ticket->asal_sekolah,
                        "'" . $ticket->whatsapp,
                        $ticket->email ?? '-',
                        str_replace(["\r\n", "\n", "\r"], ' ', $ticket->alamat),
                        strtoupper($ticket->jalur_pendaftaran),
                        $sd ? Carbon::parse($sd->service_date)->format('Y-m-d') : '-',
                        $sd ? Carbon::parse($sd->start_time)->format('H:i') . ' - ' . Carbon::parse($sd->end_time)->format('H:i') : '-',
                        strtoupper(str_replace('_', ' ', $ticket->status)),
                        $ticket->catatan_loket ?? '-',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        $perPage = $request->per_page ?? 20;
        $tickets = $query->paginate($perPage)->withQueryString();
        $dates = ServiceDate::orderBy('service_date', 'desc')->get();

        return view('backend.dashboard.attendance', compact('tickets', 'dates'));
    }

    public function storeServiceDate(Request $request)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
            'session_name' => 'required|string|max:100',
            'session_code' => 'required|string|max:3',
            'start_time' => 'required',
            'end_time' => 'required',
            'quota' => 'required|integer|min:1',
        ]);

        $cekDuplikat = ServiceDate::where('service_date', $validated['service_date'])
            ->where('session_name', $validated['session_name'])
            ->first();

        if ($cekDuplikat) {
            $tanggalFormat = Carbon::parse($validated['service_date'])->translatedFormat('d F Y');

            return redirect()->back()->with('error', 'Gagal! Sesi "' . $validated['session_name'] . '" pada ' . $tanggalFormat . ' sudah ada. Gunakan nama sesi lain (misal: Sesi 2).');
        }

        $validated['session_code'] = strtoupper(trim($validated['session_code']));
        ServiceDate::create($validated);

        return back()->with('success', 'Sesi ditambahkan!');
    }

    public function updateServiceDate(Request $request, int $id)
    {
        $validated = $request->validate([
            'service_date' => 'required|date',
            'session_name' => 'required|string|max:100',
            'session_code' => 'required|string|max:3',
            'start_time' => 'required',
            'end_time' => 'required',
            'quota' => 'required|integer|min:1',
        ]);

        $cekDuplikat = ServiceDate::where('service_date', $validated['service_date'])
            ->where('session_name', $validated['session_name'])
            ->where('id', '!=', $id)
            ->first();

        if ($cekDuplikat) {
            $tanggalFormat = Carbon::parse($validated['service_date'])->translatedFormat('d F Y');

            return redirect()->back()->with('error', 'Gagal Edit! Sesi "' . $validated['session_name'] . '" pada ' . $tanggalFormat . ' sudah ada.');
        }

        $validated['session_code'] = strtoupper(trim($validated['session_code']));
        ServiceDate::findOrFail($id)->update($validated);

        return back()->with('success', 'Sesi diupdate!');
    }

    public function toggleServiceDate(int $id)
    {
        $sd = ServiceDate::findOrFail($id);
        $sd->is_active = !$sd->is_active;
        $sd->save();

        return back()->with('success', 'Status Sesi diubah!');
    }

    public function destroyServiceDate(int $id)
    {
        ServiceDate::findOrFail($id)->delete();

        return back()->with('success', 'Sesi dihapus!');
    }

    public function getStudentsBySession(int $id)
    {
        $students = Ticket::where('service_date_id', $id)->orderBy('id', 'asc')->get();

        return response()->json(['success' => true, 'data' => $students]);
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:super_admin,verifikator,scanner',
            'loket_number' => $request->role === 'verifikator' ? 'required|integer|unique:users,loket_number' : 'nullable',
        ], [
            'loket_number.unique' => 'Nomor Loket tersebut sudah dipakai oleh petugas lain!',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'loket_number' => $request->role === 'verifikator' ? $request->loket_number : null,
        ]);

        return back()->with('success', 'Petugas ditambahkan!');
    }

    public function updateUser(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:super_admin,verifikator,scanner',
            'loket_number' => $request->role === 'verifikator' ? 'required|integer|unique:users,loket_number,' . $id : 'nullable',
        ], [
            'loket_number.unique' => 'Nomor Loket tersebut sudah dipakai oleh petugas lain!',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->loket_number = $request->role === 'verifikator' ? $request->loket_number : null;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return back()->with('success', 'Data Petugas diupdate!');
    }

    public function toggleUser(int $id)
    {
        $u = User::findOrFail($id);
        if ($u->role === 'super_admin') {
            return back()->with('error', 'Akun Super Admin tidak dapat dinonaktifkan!');
        }

        $u->is_active = !$u->is_active;
        $u->save();

        return back()->with('success', 'Status Petugas diubah!');
    }

    public function destroyUser(int $id)
    {
        $u = User::findOrFail($id);
        if ($u->role === 'super_admin') {
            return back()->with('error', 'Akun Super Admin tidak dapat dihapus!');
        }

        $u->delete();

        return back()->with('success', 'Petugas dihapus!');
    }

    public function panggilTiket(Request $request)
    {
        $request->validate(['ticket_id' => 'required|integer']);

        $ticket = Ticket::find($request->ticket_id);
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Tiket tidak ditemukan.'], 404);
        }

        if ($ticket->status === 'dipanggil' && $ticket->loket_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket ini baru saja dipanggil oleh loket lain.',
            ], 409);
        }

        if (in_array($ticket->status, ['menghadap', 'selesai'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Tiket ini sudah diproses.',
            ], 409);
        }

        $user = User::find(Auth::id());
        if ($user && $user->status_loket === 'tutup') {
            $user->status_loket = 'aktif';
            $user->keterangan_loket = null;
            $user->save();
        }

        if ($ticket->status !== 'dipanggil') {
            $ticket->status = 'dipanggil';
            $ticket->call_count = 1;
        } elseif ($ticket->call_count < 3) {
            $ticket->call_count += 1;
        }

        $ticket->loket_id = Auth::id();
        $ticket->save();

        $setting = Setting::first();
        $template = $setting->announcer_text ?? 'Nomor Antrian... {nomor}... Silakan menuju ke... Loket... {loket}';

        return response()->json([
            'success' => true,
            'ticket_number' => explode('-', $ticket->ticket_number)[0],
            'loket_number' => Auth::user()->loket_number,
            'announcer_template' => $template,
        ]);
    }

    public function siswaMenghadap(Request $request)
    {
        $request->validate(['ticket_id' => 'required|integer|exists:tickets,id']);

        $ticket = Ticket::findOrFail($request->ticket_id);
        $ticket->status = 'menghadap';
        $ticket->save();

        return back()->with('success', 'Siswa sedang diverifikasi.');
    }

    public function lewatiTiket(Request $request)
    {
        $request->validate(['ticket_id' => 'required|integer|exists:tickets,id']);

        $ticket = Ticket::findOrFail($request->ticket_id);
        $ticket->status = 'dilewati';
        $ticket->save();

        return back()->with('error', 'Tiket dilewati.');
    }

    public function selesaikanTiket(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|integer|exists:tickets,id',
            'catatan_loket' => 'nullable|string|max:1000',
        ]);

        $ticket = Ticket::findOrFail($request->ticket_id);
        $ticket->status = 'selesai';
        $ticket->catatan_loket = $request->catatan_loket;
        $ticket->save();

        return back()->with('success', 'Verifikasi selesai!');
    }

    public function restoreTiket(Request $request)
    {
        $request->validate(['ticket_id' => 'required|integer']);

        $ticket = Ticket::find($request->ticket_id);
        if ($ticket) {
            $ticket->status = 'hadir_di_lokasi';
            $ticket->call_count = 0;
            $ticket->loket_id = null;
            $ticket->catatan_loket = null;
            $ticket->save();

            return back()->with('success', 'Siswa dikembalikan ke antrian.');
        }

        return back()->with('error', 'Gagal memulihkan tiket.');
    }

    public function liveMonitor(string $ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->first();
        if (!$ticket) {
            return response()->json(['success' => false]);
        }

        $antrianDiDepan = Ticket::where('service_date_id', $ticket->service_date_id)
            ->where('status', 'hadir_di_lokasi')
            ->where('updated_at', '<', $ticket->updated_at ?? now())
            ->count();

        return response()->json([
            'success' => true,
            'status' => $ticket->status,
            'antrian_di_depan' => $antrianDiDepan,
            'estimasi_waktu' => $this->estimateWaitMinutes($antrianDiDepan),
            'catatan_loket' => $ticket->catatan_loket,
        ]);
    }

    public function cariNisn(Request $request)
    {
        $request->validate(['nisn' => 'required|string']);

        $ticket = Ticket::with('serviceDate')->where('nisn', $request->nisn)->first();
        if (!$ticket || !$ticket->serviceDate) {
            return response()->json(['success' => false, 'message' => 'NISN tidak terdaftar di sistem.']);
        }

        $isSelesaiWithNote = ($ticket->status === 'selesai' && !empty($ticket->catatan_loket));
        $allowedStatuses = ['menunggu', 'dilewati'];

        if (!in_array($ticket->status, $allowedStatuses, true) && !$isSelesaiWithNote) {
            $statusMap = [
                'hadir_di_lokasi' => 'HADIR DI LOKASI',
                'dipanggil' => 'SEDANG DIPANGGIL',
                'menghadap' => 'SEDANG VERIFIKASI',
                'selesai' => 'SELESAI (FINAL)',
            ];
            $statusName = $statusMap[$ticket->status] ?? strtoupper($ticket->status);

            return response()->json([
                'success' => false,
                'message' => "<div class='text-left'><b class='text-red-600 text-lg'>MUTASI DITOLAK!</b><br><br>Status siswa ini adalah <b class='text-blue-600'>{$statusName}</b> tanpa PR/Catatan.<br><br>Mutasi <b>HANYA</b> diizinkan untuk siswa: <br>- <i>Belum Hadir</i><br>- <i>Terlewat</i><br>- <i>Selesai dengan Catatan (PR Berkas)</i>.</div>",
            ]);
        }

        $todayAbsolute = Carbon::now('Asia/Jakarta')->toDateString();
        $ticketDate = Carbon::parse($ticket->serviceDate->service_date)->format('Y-m-d');

        if ($ticketDate === $todayAbsolute && !$isSelesaiWithNote) {
            return response()->json([
                'success' => false,
                'message' => "<div class='text-left'>Jadwal siswa ini adalah <b class='text-blue-600'>HARI INI</b>. Tidak perlu dimutasi!<br><br>Silakan arahkan siswa ke Pos OSIS untuk Scan QR Code agar masuk ke antrian.</div>",
            ]);
        }

        $statusJadwal = $isSelesaiWithNote
            ? 'SELESAI BERSYARAT (Catatan: ' . $ticket->catatan_loket . ')'
            : (($ticketDate < $todayAbsolute) ? 'KADALUARSA (Terlewat)' : 'KEPAGIAN (Maju / Awal)');

        $formattedDate = Carbon::parse($ticket->serviceDate->service_date)->format('d F Y');
        $todaySession = $this->findTodayActiveSession();
        $isFull = $todaySession && $todaySession->booked_count >= $todaySession->quota;

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'session_date' => $formattedDate,
            'status_jadwal' => $statusJadwal,
            'is_today_full' => $isFull,
        ]);
    }

    public function mutasiTiket(Request $request)
    {
        $request->validate(['ticket_id' => 'required|integer']);

        $ticket = Ticket::find($request->ticket_id);
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Data tiket tidak valid.']);
        }

        $isSelesaiWithNote = ($ticket->status === 'selesai' && !empty($ticket->catatan_loket));
        $allowedStatuses = ['menunggu', 'dilewati'];

        if (!in_array($ticket->status, $allowedStatuses, true) && !$isSelesaiWithNote) {
            return response()->json(['success' => false, 'message' => 'Gagal! Status tiket ini tidak memenuhi syarat untuk dimutasi.']);
        }

        $todaySession = $this->findTodayActiveSession();
        if (!$todaySession) {
            return response()->json(['success' => false, 'message' => 'Gagal Mutasi: Belum ada Sesi Layanan yang dibuka untuk hari ini.']);
        }

        $oldSessionId = $ticket->service_date_id;
        if ($oldSessionId == $todaySession->id) {
            if ($isSelesaiWithNote) {
                $ticket->status = 'hadir_di_lokasi';
                $ticket->call_count = 0;
                $ticket->loket_id = null;
                $ticket->save();

                $shortNumber = explode('-', $ticket->ticket_number)[0];

                return response()->json([
                    'success' => true,
                    'message' => "Berhasil! Siswa dimasukkan kembali ke antrian hari ini dengan Nomor Antrian: $shortNumber. (Catatan PR tetap tersimpan)",
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Siswa ini sudah berada di antrian hari ini.']);
        }

        $generated = DB::transaction(function () use ($ticket, $oldSessionId, $todaySession) {
            $oldSession = ServiceDate::whereKey($oldSessionId)->lockForUpdate()->first();
            if ($oldSession && $oldSession->booked_count > 0) {
                $oldSession->decrement('booked_count');
            }

            $lockedToday = ServiceDate::whereKey($todaySession->id)->lockForUpdate()->first();
            $lockedToday->increment('booked_count');

            $generated = $this->generateSequenceNumber($this->sessionPrefix($lockedToday));

            $ticket->service_date_id = $lockedToday->id;
            $ticket->ticket_number = $generated->full;
            $ticket->status = 'hadir_di_lokasi';
            $ticket->call_count = 0;
            $ticket->loket_id = null;
            $ticket->save();

            return $generated;
        });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil! Kuota lama dikembalikan, dan siswa masuk ke antrian hari ini dengan nomor: ' . $generated->short,
        ]);
    }

    public function toggleLoket(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        if ($user->status_loket === 'tutup') {
            $user->status_loket = 'aktif';
            $user->keterangan_loket = null;
            $msg = 'Loket Anda kembali AKTIF. Siap melayani antrian!';
        } else {
            $user->status_loket = 'tutup';
            $user->keterangan_loket = strtoupper(trim($request->keterangan ?: 'ISTIRAHAT'));
            $msg = 'Loket ditutup dengan alasan: ' . $user->keterangan_loket;
        }

        $user->save();

        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function updateSpmbAccess(Request $request)
    {
        $request->validate([
            'system_status' => 'required|in:normal,exhausted,maintenance',
            'spmb_open_at' => 'nullable|date',
        ]);

        $setting = Setting::first() ?? new Setting();
        $setting->spmb_open_at = $request->filled('spmb_open_at')
            ? Carbon::parse($request->spmb_open_at, 'Asia/Jakarta')->format('Y-m-d H:i:s')
            : null;

        $status = $request->system_status;
        $setting->is_maintenance = $status === 'maintenance';
        $setting->is_ticket_exhausted = $status === 'exhausted';
        $setting->save();

        return back()->with('success', 'Pengaturan Akses Pendaftaran Antrian berhasil diperbarui!');
    }

    public function storeGoShow(Request $request)
    {
        $validated = $request->validate([
            'nisn' => 'required|numeric|unique:tickets,nisn',
            'nama_cpd' => 'required|string|max:255',
            'asal_sekolah' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'email' => 'required|email',
            'alamat' => 'required|string',
            'jalur_pendaftaran' => 'required|in:KIP,DTKS,KIP & DTKS,REGULER',
        ], ['nisn.unique' => 'NISN ini sudah terdaftar di sistem online/offline. Siswa tidak boleh mendaftar ganda.']);

        $todaySession = $this->findTodayActiveSession();
        if (!$todaySession) {
            return response()->json([
                'success' => false,
                'message' => 'GAGAL: Belum ada Sesi Layanan yang dibuka untuk HARI INI. Silakan Super Admin membuka sesi terlebih dahulu di menu Manajemen Sesi.',
            ]);
        }

        [$ticket, $shortNumber] = DB::transaction(function () use ($validated, $todaySession) {
            $generated = $this->generateSequenceNumber('GS');

            $created = Ticket::create([
                'ticket_number' => $generated->full,
                'nisn' => $validated['nisn'],
                'nama_cpd' => $validated['nama_cpd'],
                'asal_sekolah' => $validated['asal_sekolah'],
                'whatsapp' => $validated['whatsapp'],
                'email' => $validated['email'],
                'alamat' => $validated['alamat'],
                'jalur_pendaftaran' => $validated['jalur_pendaftaran'],
                'service_date_id' => $todaySession->id,
                'status' => 'hadir_di_lokasi',
                'qr_code_path' => null,
            ]);

            $todaySession->increment('booked_count');

            return [$created, $generated->short];
        });

        return response()->json([
            'success' => true,
            'message' => 'Tiket Go-Show Berhasil Dibuat & Siswa Langsung Masuk Antrian!',
            'ticket_number' => $shortNumber,
            'nama_cpd' => strtoupper($ticket->nama_cpd),
        ]);
    }

    /**
     * Cari sesi aktif hari ini yang sedang berlangsung atau sesi terakhir hari ini.
     */
    private function findTodayActiveSession(): ?ServiceDate
    {
        $now = Carbon::now('Asia/Jakarta');
        $currentTime = $now->format('H:i:s');
        $todayAbsolute = $now->toDateString();

        $session = ServiceDate::where('is_active', true)
            ->whereDate('service_date', $todayAbsolute)
            ->whereTime('end_time', '>=', $currentTime)
            ->orderBy('start_time', 'asc')
            ->first();

        if (!$session) {
            $session = ServiceDate::where('is_active', true)
                ->whereDate('service_date', $todayAbsolute)
                ->orderBy('start_time', 'desc')
                ->first();
        }

        return $session;
    }
}
