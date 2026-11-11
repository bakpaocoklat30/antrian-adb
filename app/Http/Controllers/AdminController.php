<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\ServiceDate;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use App\Services\DatabaseBackupService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        if (Auth::user()->role === 'super_admin') {
            $stats = [
                'total' => Ticket::count(),
                'hadir' => Ticket::whereIn('status', ['hadir_di_lokasi', 'dipanggil', 'menghadap', 'selesai'])->count(),
                'selesai' => Ticket::where('status', 'selesai')->count(),
                'events_active' => Event::where('is_active', true)->count(),
            ];
            $setting = Setting::first();
            $hasCheckinEvents = Event::where('is_active', true)->where('requires_checkin', true)->exists();

            return view('backend.dashboard.superadmin', compact('stats', 'setting', 'hasCheckinEvents'));
        }

        $aPerPage = $request->query('a_per_page', 5);

        $antrianHadir = Ticket::with('event')
            ->where('status', 'hadir_di_lokasi')
            ->orderBy('updated_at', 'asc')
            ->paginate($aPerPage, ['*'], 'a_page')
            ->appends($request->except('a_page'));

        $riwayatHariIni = Ticket::with('event')
            ->where('loket_id', Auth::id())
            ->whereIn('status', ['dipanggil', 'menghadap', 'selesai', 'dilewati'])
            ->whereDate('updated_at', Carbon::today('Asia/Jakarta'))
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('backend.dashboard.index', compact('antrianHadir', 'riwayatHariIni'));
    }

    public function scanner()
    {
        $activeEvents = Event::where('is_active', true)->orderBy('id', 'asc')->get();
        $hasCheckinEvents = $activeEvents->contains(fn (Event $event) => (bool) $event->requires_checkin);

        return view('backend.scanner.index', compact('activeEvents', 'hasCheckinEvents'));
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

        $ticket = Ticket::with(['serviceDate', 'event'])
            ->where('ticket_number', $input)
            ->first();

        if (!$ticket) {
            $ticket = Ticket::with(['serviceDate', 'event'])
                ->where('nisn', $input)
                ->whereHas('event', fn ($q) => $q->where('is_active', true))
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$ticket || !$ticket->serviceDate) {
            return response()->json(['success' => false, 'message' => 'Tiket Tidak Dikenali / Tidak Terdaftar!']);
        }

        if ($ticket->event && !$ticket->event->requires_checkin) {
            return response()->json([
                'success' => false,
                'message' => 'Event "' . $ticket->event->name . '" tidak menggunakan menu Check-In. Peserta langsung masuk antrian loket.',
            ]);
        }

        if (!$ticket->serviceDate->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'DITOLAK! Sesi "' . $ticket->serviceDate->session_name . '" untuk tiket ini SUDAH DITUTUP oleh Admin. Peserta tidak dapat melakukan Check-in.',
            ]);
        }

        $today = Carbon::today('Asia/Jakarta')->format('Y-m-d');
        $ticketDate = Carbon::parse($ticket->serviceDate->service_date)->format('Y-m-d');
        $formattedTicketDate = Carbon::parse($ticketDate)->translatedFormat('d F Y');

        if ($ticketDate < $today) {
            return response()->json([
                'success' => false,
                'message' => "Tiket KADALUARSA!\nJadwal peserta ini adalah $formattedTicketDate. Silakan arahkan ke Meja Panitia untuk mutasi jadwal.",
            ]);
        }

        if ($ticketDate > $today) {
            return response()->json([
                'success' => false,
                'message' => "Tiket BELUM AKTIF!\nJadwal peserta ini untuk tanggal $formattedTicketDate. Tidak bisa Check-In hari ini.",
            ]);
        }

        if ($ticket->status === 'hadir_di_lokasi') {
            return response()->json(['success' => false, 'message' => 'Peserta ini SUDAH Check-In sebelumnya!']);
        }

        if ($ticket->status !== 'menunggu') {
            return response()->json(['success' => false, 'message' => 'Status tiket sudah tidak valid (mungkin sudah dipanggil atau selesai diproses).']);
        }

        $ticket->status = 'hadir_di_lokasi';
        $ticket->save();

        $antrianDiDepan = $this->countAheadInQueue($ticket);

        return response()->json([
            'success' => true,
            'message' => 'Check-In Berhasil! ' . $ticket->nama_cpd,
            'event_name' => $ticket->event?->name ?? 'Antrian',
            'ticket_short' => explode('-', $ticket->ticket_number)[0],
            'antrian_di_depan' => $antrianDiDepan,
            'estimasi_waktu' => $this->estimateWaitMinutes($antrianDiDepan),
        ]);
    }

    // =========================================================================
    // MANAJEMEN EVENT (MULTI-EVENT & FORM BUILDER DINAMIS)
    // =========================================================================

    public function manageEvents(Request $request)
    {
        $query = Event::withCount(['serviceDates', 'tickets']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        $events = $query->orderByDesc('is_active')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('backend.dashboard.events', compact('events'));
    }

    public function storeEvent(Request $request)
    {
        $attributes = $this->validatedEventPayload($request);
        $attributes['slug'] = $this->generateUniqueEventSlug($attributes['name']);

        Event::create($attributes);

        return back()->with('success', 'Event baru berhasil dibuat!');
    }

    public function updateEvent(Request $request, int $id)
    {
        $event = Event::findOrFail($id);
        $event->update($this->validatedEventPayload($request));

        return back()->with('success', 'Konfigurasi event berhasil diperbarui!');
    }

    private function validatedEventPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:2000',
            'identifier_label' => 'required|string|max:60',
            'identifier_placeholder' => 'nullable|string|max:255',
            'name_label' => 'required|string|max:80',
            'confirmation_text' => 'nullable|string|max:1000',
            'checklist_text' => 'nullable|string|max:3000',
            'fields' => 'nullable|array',
            'requires_checkin' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        return [
            'name' => $validated['name'],
            'subtitle' => $validated['subtitle'] ?? null,
            'description' => $validated['description'] ?? null,
            'identifier_label' => $validated['identifier_label'],
            'identifier_placeholder' => $validated['identifier_placeholder'] ?? null,
            'name_label' => $validated['name_label'],
            'form_fields' => Event::normalizeFields($request->input('fields', [])),
            'checklist_items' => $this->parseChecklist($request->input('checklist_text')),
            'confirmation_text' => $validated['confirmation_text'] ?? null,
            'requires_checkin' => $request->boolean('requires_checkin'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    public function toggleEvent(int $id)
    {
        $event = Event::findOrFail($id);
        $event->is_active = !$event->is_active;
        $event->save();

        return back()->with('success', 'Status event "' . $event->name . '" diubah!');
    }

    public function destroyEvent(int $id)
    {
        Event::findOrFail($id)->delete();

        return back()->with('success', 'Event beserta sesi & tiketnya berhasil dihapus!');
    }

    private function generateUniqueEventSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'event';
        $slug = $base;
        $suffix = 2;

        while (Event::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    private function parseChecklist(?string $raw): array
    {
        if (!$raw) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $lines), fn ($line) => $line !== ''));
    }

    // =========================================================================
    // MANAJEMEN SESI & KUOTA
    // =========================================================================

    public function manageSessions(Request $request)
    {
        $query = ServiceDate::with('event');

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
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

        $events = Event::orderByDesc('is_active')->orderBy('name')->get();

        return view('backend.dashboard.sessions', compact('serviceDates', 'events'));
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
        $setting = Setting::firstOrCreate(['id' => 1], [
            'voice_engine' => 'server_id',
            'voice_speed' => 1.00,
            'voice_pitch' => 1.00,
            'video_volume' => 60,
        ]);

        $field = $request->field;
        $numericFields = ['video_volume', 'voice_speed', 'voice_pitch'];

        if (in_array($field, $numericFields, true)) {
            $setting->$field = $request->value;
            $setting->save();
        } elseif ($field === 'voice_engine' && in_array($request->value, ['server_id', 'browser_id'], true)) {
            $setting->voice_engine = $request->value;
            $setting->save();
        }

        return response()->json(['success' => true]);
    }

    public function attendanceTracker(Request $request)
    {
        $query = Ticket::with(['serviceDate', 'event']);

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_cpd', 'like', '%' . $request->search . '%')
                    ->orWhere('nisn', 'like', '%' . $request->search . '%')
                    ->orWhere('ticket_number', 'like', '%' . $request->search . '%');
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
            return $this->exportAttendanceCsv($query, $request->filled('event_id') ? Event::find($request->event_id) : null);
        }

        $perPage = $request->per_page ?? 20;
        $tickets = $query->paginate($perPage)->withQueryString();
        $dates = ServiceDate::with('event')->orderBy('service_date', 'desc')->get();
        $events = Event::orderByDesc('is_active')->orderBy('name')->get();

        return view('backend.dashboard.attendance', compact('tickets', 'dates', 'events'));
    }

    private function exportAttendanceCsv($query, ?Event $selectedEvent)
    {
        $tickets = $query->orderBy('nama_cpd', 'asc')->get();
        $filename = 'Rekap_Antrian_' . date('Y-m-d_H-i') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $dynamicFields = $selectedEvent ? ($selectedEvent->form_fields ?? []) : [];

        $columns = [
            'ID Tiket Penuh',
            'Nomor Antrian',
            'Event',
            $selectedEvent?->identifier_label ?? 'Identitas',
            $selectedEvent?->name_label ?? 'Nama Pendaftar',
        ];

        if ($selectedEvent) {
            foreach ($dynamicFields as $field) {
                $columns[] = $field['label'] ?? $field['key'];
            }
        } else {
            $columns[] = 'Data Isian Form';
        }

        $columns = array_merge($columns, [
            'Tanggal Sesi',
            'Waktu Sesi',
            'Status Kehadiran',
            'Catatan Verifikator',
        ]);

        $callback = function () use ($tickets, $columns, $selectedEvent, $dynamicFields) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($tickets as $ticket) {
                $sd = $ticket->serviceDate;
                $row = [
                    $ticket->ticket_number,
                    explode('-', $ticket->ticket_number)[0],
                    $ticket->event?->name ?? '-',
                    "'" . $ticket->nisn,
                    strtoupper($ticket->nama_cpd),
                ];

                if ($selectedEvent) {
                    foreach ($dynamicFields as $field) {
                        $val = $ticket->fieldValue((string) ($field['key'] ?? '')) ?? '-';
                        $row[] = str_replace(["\r\n", "\n", "\r"], ' ', $val);
                    }
                } else {
                    $pairs = array_map(
                        fn ($item) => ($item['label'] ?? '') . ': ' . str_replace(["\r\n", "\n", "\r"], ' ', (string) ($item['value'] ?? '-')),
                        $ticket->displayDetails()
                    );
                    $row[] = implode(' | ', $pairs) ?: '-';
                }

                $row[] = $sd ? Carbon::parse($sd->service_date)->format('Y-m-d') : '-';
                $row[] = $sd ? Carbon::parse($sd->start_time)->format('H:i') . ' - ' . Carbon::parse($sd->end_time)->format('H:i') : '-';
                $row[] = strtoupper(str_replace('_', ' ', $ticket->status));
                $row[] = $ticket->catatan_loket ?? '-';

                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function storeServiceDate(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'service_date' => 'required|date',
            'session_name' => 'required|string|max:100',
            'session_code' => 'required|string|max:3',
            'start_time' => 'required',
            'end_time' => 'required',
            'quota' => 'required|integer|min:1',
        ]);

        $cekDuplikat = ServiceDate::where('event_id', $validated['event_id'])
            ->where('service_date', $validated['service_date'])
            ->where('session_name', $validated['session_name'])
            ->first();

        if ($cekDuplikat) {
            $tanggalFormat = Carbon::parse($validated['service_date'])->translatedFormat('d F Y');

            return redirect()->back()->with('error', 'Gagal! Sesi "' . $validated['session_name'] . '" pada ' . $tanggalFormat . ' untuk event ini sudah ada.');
        }

        $validated['session_code'] = strtoupper(trim($validated['session_code']));
        ServiceDate::create($validated);

        return back()->with('success', 'Sesi ditambahkan!');
    }

    public function updateServiceDate(Request $request, int $id)
    {
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'service_date' => 'required|date',
            'session_name' => 'required|string|max:100',
            'session_code' => 'required|string|max:3',
            'start_time' => 'required',
            'end_time' => 'required',
            'quota' => 'required|integer|min:1',
        ]);

        $cekDuplikat = ServiceDate::where('event_id', $validated['event_id'])
            ->where('service_date', $validated['service_date'])
            ->where('session_name', $validated['session_name'])
            ->where('id', '!=', $id)
            ->first();

        if ($cekDuplikat) {
            $tanggalFormat = Carbon::parse($validated['service_date'])->translatedFormat('d F Y');

            return redirect()->back()->with('error', 'Gagal Edit! Sesi "' . $validated['session_name'] . '" pada ' . $tanggalFormat . ' untuk event ini sudah ada.');
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
        $students = Ticket::with('event')->where('service_date_id', $id)->orderBy('id', 'asc')->get();

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
        } else {
            $ticket->call_count += 1;
        }

        $ticket->loket_id = Auth::id();
        $ticket->save();

        $setting = Setting::first();
        $template = $setting->announcer_text ?? 'Nomor antrian, {nomor}, silakan menuju ke loket {loket}';

        return response()->json([
            'success' => true,
            'ticket_number' => explode('-', $ticket->ticket_number)[0],
            'loket_number' => Auth::user()->loket_number,
            'call_count' => $ticket->call_count,
            'announcer_template' => $template,
        ]);
    }

    public function siswaMenghadap(Request $request)
    {
        $request->validate(['ticket_id' => 'required|integer|exists:tickets,id']);

        $ticket = Ticket::findOrFail($request->ticket_id);
        $ticket->status = 'menghadap';
        $ticket->save();

        return back()->with('success', 'Peserta sedang dilayani di loket.');
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

        return back()->with('success', 'Pelayanan selesai!');
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

            return back()->with('success', 'Peserta dikembalikan ke antrian.');
        }

        return back()->with('error', 'Gagal memulihkan tiket.');
    }

    public function liveMonitor(string $ticket_number)
    {
        $ticket = Ticket::where('ticket_number', $ticket_number)->first();
        if (!$ticket) {
            return response()->json(['success' => false]);
        }

        $antrianDiDepan = $this->countAheadInQueue($ticket);

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
        $input = trim($request->nisn);

        $ticket = Ticket::with(['serviceDate', 'event'])
            ->where('ticket_number', $input)
            ->first();

        if (!$ticket) {
            $ticket = Ticket::with(['serviceDate', 'event'])
                ->where('nisn', $input)
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$ticket || !$ticket->serviceDate) {
            return response()->json(['success' => false, 'message' => 'Data identitas / nomor tiket tidak terdaftar di sistem.']);
        }

        $isSelesaiWithNote = ($ticket->status === 'selesai' && !empty($ticket->catatan_loket));
        $allowedStatuses = ['menunggu', 'dilewati'];

        if (!in_array($ticket->status, $allowedStatuses, true) && !$isSelesaiWithNote) {
            $statusMap = [
                'hadir_di_lokasi' => 'HADIR DI LOKASI',
                'dipanggil' => 'SEDANG DIPANGGIL',
                'menghadap' => 'SEDANG DILAYANI',
                'selesai' => 'SELESAI (FINAL)',
            ];
            $statusName = $statusMap[$ticket->status] ?? strtoupper($ticket->status);

            return response()->json([
                'success' => false,
                'message' => "<div class='text-left'><b class='text-red-600 text-lg'>MUTASI DITOLAK!</b><br><br>Status peserta ini adalah <b class='text-blue-600'>{$statusName}</b> tanpa PR/Catatan.<br><br>Mutasi <b>HANYA</b> diizinkan untuk peserta: <br>- <i>Belum Hadir</i><br>- <i>Terlewat</i><br>- <i>Selesai dengan Catatan</i>.</div>",
            ]);
        }

        $todayAbsolute = Carbon::now('Asia/Jakarta')->toDateString();
        $ticketDate = Carbon::parse($ticket->serviceDate->service_date)->format('Y-m-d');

        if ($ticketDate === $todayAbsolute && !$isSelesaiWithNote) {
            $todayHint = $ticket->event?->requires_checkin
                ? 'Silakan arahkan ke Pos Scanner untuk Check-In agar masuk ke antrian.'
                : 'Event ini langsung masuk antrian loket tanpa Check-In (gunakan Kembalikan ke Antrian di Loket jika berstatus Dilewati).';

            return response()->json([
                'success' => false,
                'message' => "<div class='text-left'>Jadwal peserta ini adalah <b class='text-blue-600'>HARI INI</b>. Tidak perlu dimutasi!<br><br>{$todayHint}</div>",
            ]);
        }

        $statusJadwal = $isSelesaiWithNote
            ? 'SELESAI BERSYARAT (Catatan: ' . $ticket->catatan_loket . ')'
            : (($ticketDate < $todayAbsolute) ? 'KADALUARSA (Terlewat)' : 'KEPAGIAN (Maju / Awal)');

        $formattedDate = Carbon::parse($ticket->serviceDate->service_date)->format('d F Y');
        $todaySession = $this->findTodayActiveSession($ticket->event_id);
        $isFull = $todaySession && $todaySession->booked_count >= $todaySession->quota;

        return response()->json([
            'success' => true,
            'data' => $ticket,
            'event_name' => $ticket->event?->name ?? 'Antrian',
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

        $todaySession = $this->findTodayActiveSession($ticket->event_id);
        if (!$todaySession) {
            return response()->json(['success' => false, 'message' => 'Gagal Mutasi: Belum ada Sesi Layanan hari ini untuk event tiket tersebut.']);
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
                    'message' => "Berhasil! Peserta dimasukkan kembali ke antrian hari ini dengan Nomor Antrian: $shortNumber. (Catatan tetap tersimpan)",
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Peserta ini sudah berada di antrian hari ini.']);
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
            'message' => 'Berhasil! Kuota lama dikembalikan, dan peserta masuk ke antrian hari ini dengan nomor: ' . $generated->short,
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
        $event = Event::where('is_active', true)->findOrFail($request->input('event_id'));

        $payload = $this->validateEventSubmission(
            $event,
            $request,
            ($event->identifier_label ?: 'Identitas') . ' ini sudah terdaftar pada event ' . $event->name . '. Peserta tidak boleh mendaftar ganda.'
        );

        $todaySession = $this->findTodayActiveSession($event->id);
        if (!$todaySession) {
            return response()->json([
                'success' => false,
                'message' => 'GAGAL: Belum ada Sesi Layanan HARI INI untuk event "' . $event->name . '". Silakan Super Admin membuka sesi terlebih dahulu di Manajemen Sesi.',
            ]);
        }

        [$ticket, $shortNumber] = DB::transaction(function () use ($event, $payload, $todaySession) {
            $generated = $this->generateSequenceNumber('GS');

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
                'service_date_id' => $todaySession->id,
                'status' => 'hadir_di_lokasi',
                'qr_code_path' => null,
            ]);

            $todaySession->increment('booked_count');

            return [$created, $generated->short];
        });

        return response()->json([
            'success' => true,
            'message' => 'Tiket Go-Show Berhasil Dibuat & Peserta Langsung Masuk Antrian!',
            'event_name' => $event->name,
            'ticket_number' => $shortNumber,
            'nama_cpd' => strtoupper($ticket->nama_cpd),
        ]);
    }

    public function backupDatabase(DatabaseBackupService $backupService)
    {
        $filename = 'disapa_backup_' . Carbon::now('Asia/Jakarta')->format('Y-m-d_His') . '.sql';

        return response()->streamDownload(function () use ($backupService) {
            $out = fopen('php://output', 'wb');
            if ($out === false) {
                return;
            }
            $backupService->streamSqlDump($out);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'application/sql; charset=UTF-8',
        ]);
    }

    /**
     * Cari sesi aktif hari ini untuk event tertentu.
     */
    private function findTodayActiveSession(?int $eventId = null): ?ServiceDate
    {
        $now = Carbon::now('Asia/Jakarta');
        $currentTime = $now->format('H:i:s');
        $todayAbsolute = $now->toDateString();

        $baseQuery = ServiceDate::where('is_active', true)
            ->when($eventId, fn ($q) => $q->where('event_id', $eventId))
            ->whereDate('service_date', $todayAbsolute);

        $session = (clone $baseQuery)
            ->whereTime('end_time', '>=', $currentTime)
            ->orderBy('start_time', 'asc')
            ->first();

        if (!$session) {
            $session = (clone $baseQuery)
                ->orderBy('start_time', 'desc')
                ->first();
        }

        return $session;
    }
}
