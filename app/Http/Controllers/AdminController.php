<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\ServiceDate;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard(Request $request) 
    {
        if (Auth::user()->role === 'super_admin') {
            $stats = [
                'total' => Ticket::count(), 
                'hadir' => Ticket::whereIn('status', ['hadir_di_lokasi', 'dipanggil', 'menghadap', 'selesai'])->count(), 
                'selesai' => Ticket::where('status', 'selesai')->count()
            ];
            $setting = Setting::first();
            return view('backend.dashboard.superadmin', compact('stats', 'setting'));
        }

        $aPerPage = $request->query('a_per_page', 5);
        
        $antrianHadir = Ticket::where('status', 'hadir_di_lokasi')
            ->orderBy('updated_at', 'asc')
            ->paginate($aPerPage, ['*'], 'a_page')
            ->appends($request->except('a_page'));

        $nisnArray = $antrianHadir->pluck('nisn')->toArray();
        if(!empty($nisnArray)) {
            $bwData = \Illuminate\Support\Facades\DB::table('butawarna_tests')
                        ->whereIn('nisn', $nisnArray)
                        ->pluck('status', 'nisn');

            $antrianHadir->getCollection()->transform(function ($ticket) use ($bwData) {
                $ticket->butawarna_status = $bwData[$ticket->nisn] ?? 'belum_tes';
                return $ticket;
            });
        }

        $riwayatHariIni = Ticket::where('loket_id', Auth::id())
            ->whereIn('status', ['dipanggil', 'menghadap', 'selesai', 'dilewati'])
            ->whereDate('updated_at', Carbon::today())
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('backend.dashboard.index', compact('antrianHadir', 'riwayatHariIni'));
    }

    public function manageSessions(Request $request) 
    {
        if (Auth::user()->role !== 'super_admin') abort(403, 'Akses Terlarang!');
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
        if (Auth::user()->role !== 'super_admin') abort(403, 'Akses Terlarang!');
        $query = \App\Models\User::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
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

        $usedLokets = \App\Models\User::whereNotNull('loket_number')->pluck('loket_number')->toArray();

        return view('backend.dashboard.users', compact('users', 'usedLokets')); 
    }

    public function manageDisplay() 
    {
        if (Auth::user()->role !== 'super_admin') abort(403, 'Akses Terlarang!');
        $setting = \App\Models\Setting::first();
        if (!$setting) { 
            $setting = new \App\Models\Setting(); 
        }

        $existingVideos = [];
        if (\Illuminate\Support\Facades\Storage::disk('public')->exists('videos')) {
            $existingVideos = \Illuminate\Support\Facades\Storage::disk('public')->files('videos');
        }

        return view('backend.dashboard.display', compact('setting', 'existingVideos'));
    }

    public function updateDisplay(Request $request) 
    {
        if (Auth::user()->role !== 'super_admin') abort(403, 'Akses Terlarang!');
        $setting = \App\Models\Setting::first();
        if (!$setting) $setting = new \App\Models\Setting();

        $setting->running_text = $request->running_text;
        $setting->announcer_text = $request->announcer_text;
        $setting->video_type = $request->video_type;

        if ($request->video_type == 'youtube') { 
            $setting->video_url = $request->video_url_youtube; 
        } else {
            if ($request->hasFile('video_file')) {
                $file = $request->file('video_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('videos', $filename, 'public');
                $setting->video_url = $path;
            } elseif ($request->filled('existing_video')) { 
                $setting->video_url = $request->existing_video; 
            }
        }
        $setting->save();

        return redirect()->back()->with('success', 'Pengaturan Teks & Video berhasil disimpan!');
    }

    public function updateLiveSetting(Request $request) 
    {
        if (Auth::user()->role !== 'super_admin') abort(403, 'Akses Terlarang!');
        $setting = \App\Models\Setting::first();

        if ($setting) {
            $field = $request->field;
            $allowed = ['video_volume', 'voice_speed', 'voice_pitch', 'voice_type'];
            
            if(in_array($field, $allowed)) { 
                $setting->$field = $request->value; 
                $setting->save(); 
            }
        }
        return response()->json(['success' => true]);
    }

    public function attendanceTracker(Request $request) 
    {
        if (Auth::user()->role !== 'super_admin') abort(403, 'Akses Terlarang!');
        $query = \App\Models\Ticket::with('serviceDate');

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
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
            $query->orderByRaw("FIELD(status, 'menunggu', 'dilewati', 'hadir_di_lokasi', 'dipanggil', 'menghadap', 'selesai')"); 
        }

        if ($request->has('export') && $request->export == 'true') {
            $tickets = $query->orderBy('nama_cpd', 'asc')->get();
            $filename = "Rekap_Kehadiran_Peserta_" . date('Y-m-d_H-i') . ".csv";
            
            $headers = array(
                "Content-type" => "text/csv", 
                "Content-Disposition" => "attachment; filename=$filename", 
                "Pragma" => "no-cache", 
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", 
                "Expires" => "0"
            );
            
            $columns = ['ID Tiket Penuh', 'Nomor Antrian', 'Nama Lengkap', 'NISN', 'Asal Sekolah', 'WhatsApp', 'Email', 'Alamat Lengkap', 'Data Kesejahteraan', 'Tanggal Sesi', 'Waktu Sesi', 'Status Kehadiran', 'Catatan Verifikator'];
            
            $callback = function() use($tickets, $columns) {
                $file = fopen('php://output', 'w');
                fputs($file, "\xEF\xBB\xBF"); 
                fputcsv($file, $columns);
                
                foreach ($tickets as $ticket) {
                    $row['ID Tiket Penuh'] = $ticket->ticket_number;
                    $row['Nomor Antrian']  = explode('-', $ticket->ticket_number)[0];
                    $row['Nama Lengkap']   = strtoupper($ticket->nama_cpd);
                    $row['NISN']           = "'" . $ticket->nisn;
                    $row['Asal Sekolah']   = $ticket->asal_sekolah;
                    $row['WhatsApp']       = "'" . $ticket->whatsapp;
                    $row['Email']          = $ticket->email ?? '-';
                    $row['Alamat Lengkap'] = str_replace(["\r\n", "\n", "\r"], ' ', $ticket->alamat);
                    $row['Data Kesejahteraan'] = strtoupper($ticket->jalur_pendaftaran);
                    $row['Tanggal Sesi']   = \Carbon\Carbon::parse($ticket->serviceDate->service_date)->format('Y-m-d');
                    $row['Waktu Sesi']     = \Carbon\Carbon::parse($ticket->serviceDate->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($ticket->serviceDate->end_time)->format('H:i');
                    $row['Status Kehadiran']= strtoupper(str_replace('_', ' ', $ticket->status));
                    $row['Catatan Verifikator'] = $ticket->catatan_loket ?? '-';
                    
                    fputcsv($file, array(
                        $row['ID Tiket Penuh'], 
                        $row['Nomor Antrian'], 
                        $row['Nama Lengkap'], 
                        $row['NISN'], 
                        $row['Asal Sekolah'], 
                        $row['WhatsApp'], 
                        $row['Email'], 
                        $row['Alamat Lengkap'], 
                        $row['Data Kesejahteraan'], 
                        $row['Tanggal Sesi'], 
                        $row['Waktu Sesi'], 
                        $row['Status Kehadiran'],
                        $row['Catatan Verifikator']
                    ));
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }

        $perPage = $request->per_page ?? 20;
        $tickets = $query->paginate($perPage)->withQueryString();
        $dates = \App\Models\ServiceDate::orderBy('service_date', 'desc')->get();

        return view('backend.dashboard.attendance', compact('tickets', 'dates'));
    }

    public function storeServiceDate(Request $request) 
    { 
        if (Auth::user()->role !== 'super_admin') abort(403);

        $cekDuplikat = ServiceDate::where('service_date', $request->service_date)
                                  ->where('session_name', $request->session_name)
                                  ->first();

        if ($cekDuplikat) { 
            $tanggalFormat = \Carbon\Carbon::parse($request->service_date)->translatedFormat('d F Y'); 
            return redirect()->back()->with('error', 'Gagal! Sesi "' . $request->session_name . '" pada ' . $tanggalFormat . ' sudah ada. Gunakan nama sesi lain (misal: Sesi 2).'); 
        }

        $request->merge(['session_code' => strtoupper($request->session_code)]);

        ServiceDate::create($request->all()); 
        return back()->with('success', 'Sesi ditambahkan!');
    }

    public function updateServiceDate(Request $request, $id) 
    { 
        if (Auth::user()->role !== 'super_admin') abort(403);

        $cekDuplikat = ServiceDate::where('service_date', $request->service_date)
                                  ->where('session_name', $request->session_name)
                                  ->where('id', '!=', $id)
                                  ->first();

        if ($cekDuplikat) { 
            $tanggalFormat = \Carbon\Carbon::parse($request->service_date)->translatedFormat('d F Y'); 
            return redirect()->back()->with('error', 'Gagal Edit! Sesi "' . $request->session_name . '" pada ' . $tanggalFormat . ' sudah ada.'); 
        }

        $request->merge(['session_code' => strtoupper($request->session_code)]);

        ServiceDate::findOrFail($id)->update($request->all()); 
        return back()->with('success', 'Sesi diupdate!');
    }

    public function toggleServiceDate($id) 
    { 
        if (Auth::user()->role !== 'super_admin') abort(403); 
        $sd = ServiceDate::findOrFail($id); 
        $sd->is_active = !$sd->is_active; 
        $sd->save(); 
        return back()->with('success', 'Status Sesi diubah!'); 
    }

    public function destroyServiceDate($id) 
    { 
        if (Auth::user()->role !== 'super_admin') abort(403); 
        ServiceDate::findOrFail($id)->delete(); 
        return back()->with('success', 'Sesi dihapus!'); 
    }

    public function getStudentsBySession($id) 
    { 
        if (Auth::user()->role !== 'super_admin') abort(403); 
        $students = Ticket::where('service_date_id', $id)->orderBy('id', 'asc')->get(); 
        return response()->json(['success' => true, 'data' => $students]); 
    }

    public function storeUser(Request $request) 
    { 
        if (Auth::user()->role !== 'super_admin') abort(403);

        $request->validate([
            'name' => 'required', 
            'email' => 'required|email|unique:users', 
            'password' => 'required|min:6', 
            'role' => 'required', 
            'loket_number' => $request->role === 'verifikator' ? 'required|integer|unique:users,loket_number' : 'nullable'
        ], [
            'loket_number.unique' => 'Nomor Loket tersebut sudah dipakai oleh petugas lain!'
        ]);

        User::create([
            'name' => $request->name, 
            'email' => $request->email, 
            'password' => Hash::make($request->password), 
            'role' => $request->role, 
            'loket_number' => $request->role === 'verifikator' ? $request->loket_number : null 
        ]);

        return back()->with('success', 'Petugas ditambahkan!'); 
    }

    public function updateUser(Request $request, $id) 
    { 
        if (Auth::user()->role !== 'super_admin') abort(403);
        $user = User::findOrFail($id); 

        $request->validate([
            'name' => 'required', 
            'email' => 'required|email|unique:users,email,'.$id, 
            'role' => 'required', 
            'loket_number' => $request->role === 'verifikator' ? 'required|integer|unique:users,loket_number,'.$id : 'nullable'
        ], [
            'loket_number.unique' => 'Nomor Loket tersebut sudah dipakai oleh petugas lain!'
        ]); 

        $user->name = $request->name; 
        $user->email = $request->email; 
        $user->role = $request->role; 
        $user->loket_number = $request->role === 'verifikator' ? $request->loket_number : null; 

        if($request->password) { 
            $user->password = Hash::make($request->password); 
        } 
        $user->save(); 

        return back()->with('success', 'Data Petugas diupdate!'); 
    }

    public function toggleUser($id) 
    { 
        if (Auth::user()->role !== 'super_admin') abort(403); 
        $u = User::findOrFail($id); 
        $u->is_active = !$u->is_active; 
        $u->save(); 
        return back()->with('success', 'Status Petugas diubah!'); 
    }

    public function destroyUser($id) 
    { 
        if (Auth::user()->role !== 'super_admin') abort(403); 
        User::findOrFail($id)->delete(); 
        return back()->with('success', 'Petugas dihapus!'); 
    }

    public function panggilTiket(Request $request) 
    {
        $ticket = Ticket::find($request->ticket_id);
        if (!$ticket) {
            return response()->json(['success' => false, 'message' => 'Tiket tidak ditemukan.']);
        }

        $user = \App\Models\User::find(Auth::id());
        if ($user->status_loket === 'tutup') {
            $user->status_loket = 'aktif';
            $user->keterangan_loket = null;
            $user->save();
        }

        if ($ticket->status != 'dipanggil') { 
            $ticket->status = 'dipanggil';
            $ticket->call_count = 1; 
        } else { 
            if ($ticket->call_count < 3) {
                $ticket->call_count += 1;
            }
        }
        
        $ticket->loket_id = Auth::id(); 
        $ticket->save();

        $setting = Setting::first(); 
        $template = $setting->announcer_text ?? 'Nomor Antrian... {nomor}... Silakan menuju ke... Loket... {loket}';
        
        return response()->json([
            'success' => true, 
            'ticket_number' => explode('-', $ticket->ticket_number)[0], 
            'loket_number' => Auth::user()->loket_number, 
            'announcer_template' => $template
        ]);
    }

    public function siswaMenghadap(Request $request) 
    { 
        $ticket = Ticket::find($request->ticket_id); 
        $ticket->status = 'menghadap'; 
        $ticket->save(); 
        return back()->with('success', 'Siswa sedang diverifikasi.'); 
    }

    public function lewatiTiket(Request $request) 
    { 
        $ticket = Ticket::find($request->ticket_id); 
        $ticket->status = 'dilewati'; 
        $ticket->save(); 
        return back()->with('error', 'Tiket dilewati.'); 
    }

    public function selesaikanTiket(Request $request) 
    { 
        $ticket = Ticket::find($request->ticket_id); 
        $ticket->status = 'selesai'; 
        $ticket->catatan_loket = $request->catatan_loket;
        $ticket->save(); 
        return back()->with('success', 'Verifikasi selesai!'); 
    }

    public function restoreTiket(Request $request) 
    { 
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
    
    public function liveMonitor($ticket_number) 
    { 
        $ticket = Ticket::where('ticket_number', $ticket_number)->first(); 
        if (!$ticket) return response()->json(['success' => false]); 
        
        $antrianDiDepan = Ticket::where('service_date_id', $ticket->service_date_id)
            ->where('status', 'hadir_di_lokasi')
            ->where('updated_at', '<', $ticket->updated_at ?? now())
            ->count(); 
            
        return response()->json([
            'success' => true, 
            'status' => $ticket->status, 
            'antrian_di_depan' => $antrianDiDepan, 
            'estimasi_waktu' => ceil(($antrianDiDepan / 7) * 5),
            'catatan_loket' => $ticket->catatan_loket
        ]); 
    }
    
    public function cariNisn(Request $request) 
    {
        $ticket = Ticket::with('serviceDate')->where('nisn', $request->nisn)->first();
        if($ticket) {
            $isSelesaiWithNote = ($ticket->status === 'selesai' && !empty($ticket->catatan_loket));
            $allowedStatuses = ['menunggu', 'dilewati'];

            if (!in_array($ticket->status, $allowedStatuses) && !$isSelesaiWithNote) {
                $statusMap = [
                    'hadir_di_lokasi' => 'HADIR DI LOKASI', 
                    'dipanggil' => 'SEDANG DIPANGGIL', 
                    'menghadap' => 'SEDANG VERIFIKASI', 
                    'selesai' => 'SELESAI (FINAL)'
                ];
                $statusName = $statusMap[$ticket->status] ?? strtoupper($ticket->status);
                
                return response()->json([
                    'success' => false, 
                    'message' => "<div class='text-left'><b class='text-red-600 text-lg'>MUTASI DITOLAK!</b><br><br>Status siswa ini adalah <b class='text-blue-600'>{$statusName}</b> tanpa PR/Catatan.<br><br>Mutasi <b>HANYA</b> diizinkan untuk siswa: <br>- <i>Belum Hadir</i><br>- <i>Terlewat</i><br>- <i>Selesai dengan Catatan (PR Berkas)</i>.</div>"
                ]);
            }

            $currentTime = Carbon::now('Asia/Jakarta')->format('H:i:s');
            $todayAbsolute = Carbon::now('Asia/Jakarta')->toDateString();
            $ticketDate = Carbon::parse($ticket->serviceDate->service_date)->format('Y-m-d');
            
            if ($ticketDate == $todayAbsolute && !$isSelesaiWithNote) { 
                return response()->json([
                    'success' => false, 
                    'message' => "<div class='text-left'>Jadwal siswa ini adalah <b class='text-blue-600'>HARI INI</b>. Tidak perlu dimutasi!<br><br>Silakan arahkan siswa ke Pos OSIS untuk Scan QR Code agar masuk ke antrian.</div>"
                ]); 
            }

            if ($isSelesaiWithNote) {
                $statusJadwal = "SELESAI BERSYARAT (Catatan: " . $ticket->catatan_loket . ")";
            } else {
                $statusJadwal = ($ticketDate < $todayAbsolute) ? 'KADALUARSA (Terlewat)' : 'KEPAGIAN (Maju / Awal)';
            }
            
            $formattedDate = Carbon::parse($ticket->serviceDate->service_date)->format('d F Y');
            
            $todaySession = ServiceDate::where('is_active', true)
                            ->whereDate('service_date', $todayAbsolute)
                            ->whereTime('end_time', '>=', $currentTime)
                            ->orderBy('start_time', 'asc')
                            ->first();

            if (!$todaySession) {
                $todaySession = ServiceDate::where('is_active', true)
                                ->whereDate('service_date', $todayAbsolute)
                                ->orderBy('start_time', 'desc')
                                ->first();
            }

            $isFull = false;
            if($todaySession && $todaySession->booked_count >= $todaySession->quota) { 
                $isFull = true; 
            }

            return response()->json([
                'success' => true, 
                'data' => $ticket, 
                'session_date' => $formattedDate, 
                'status_jadwal' => $statusJadwal, 
                'is_today_full' => $isFull 
            ]);
        }
        return response()->json(['success' => false, 'message' => 'NISN tidak terdaftar di sistem.']);
    }

    public function mutasiTiket(Request $request) 
    {
        $ticket = Ticket::find($request->ticket_id);
        if(!$ticket) return response()->json(['success' => false, 'message' => 'Data tiket tidak valid.']);

        $isSelesaiWithNote = ($ticket->status === 'selesai' && !empty($ticket->catatan_loket));
        $allowedStatuses = ['menunggu', 'dilewati'];
        
        if (!in_array($ticket->status, $allowedStatuses) && !$isSelesaiWithNote) { 
            return response()->json(['success' => false, 'message' => 'Gagal! Status tiket ini tidak memenuhi syarat untuk dimutasi.']); 
        }

        $oldSessionId = $ticket->service_date_id;
        
        $currentTime = Carbon::now('Asia/Jakarta')->format('H:i:s');
        $todayAbsolute = Carbon::now('Asia/Jakarta')->toDateString();

        $todaySession = ServiceDate::where('is_active', true)
                        ->whereDate('service_date', $todayAbsolute)
                        ->whereTime('end_time', '>=', $currentTime)
                        ->orderBy('start_time', 'asc')
                        ->first();

        if (!$todaySession) {
            $todaySession = ServiceDate::where('is_active', true)
                            ->whereDate('service_date', $todayAbsolute)
                            ->orderBy('start_time', 'desc')
                            ->first();
        }
        
        if(!$todaySession) { 
            return response()->json(['success' => false, 'message' => 'Gagal Mutasi: Belum ada Sesi Layanan yang dibuka untuk hari ini.']); 
        }

        if($oldSessionId == $todaySession->id) { 
            if ($isSelesaiWithNote) {
                $ticket->status = 'hadir_di_lokasi'; 
                $ticket->call_count = 0; 
                $ticket->loket_id = null; 
                $ticket->save();

                $shortNumber = explode('-', $ticket->ticket_number)[0];
                return response()->json([
                    'success' => true, 
                    'message' => "Berhasil! Siswa dimasukkan kembali ke antrian hari ini dengan Nomor Antrian: $shortNumber. (Catatan PR tetap tersimpan)"
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'Siswa ini sudah berada di antrian hari ini.']); 
            }
        }

        $oldSession = ServiceDate::find($oldSessionId);
        if($oldSession && $oldSession->booked_count > 0) { 
            $oldSession->decrement('booked_count'); 
        }

        $todaySession->increment('booked_count');

        $prefix = $todaySession->session_code ? strtoupper($todaySession->session_code) : strtoupper(substr($todaySession->session_name, 0, 1));
        $generated = $this->generateSequenceNumber($prefix);
        $newTicketNumber = $generated->full;
        
        $ticket->service_date_id = $todaySession->id; 
        $ticket->ticket_number = $newTicketNumber; 
        $ticket->status = 'hadir_di_lokasi'; 
        $ticket->call_count = 0; 
        $ticket->loket_id = null; 
        $ticket->save();

        $shortNumber = $generated->short;
        
        return response()->json([
            'success' => true, 
            'message' => "Berhasil! Kuota lama dikembalikan, dan siswa masuk ke antrian hari ini dengan nomor: $shortNumber"
        ]);
    }

    public function toggleLoket(Request $request) 
    {
        $user = \App\Models\User::find(Auth::id());
        
        if ($user->status_loket === 'tutup') {
            $user->status_loket = 'aktif';
            $user->keterangan_loket = null;
            $msg = 'Loket Anda kembali AKTIF. Siap melayani antrian!';
        } else {
            $user->status_loket = 'tutup';
            $user->keterangan_loket = strtoupper($request->keterangan ?? 'ISTIRAHAT');
            $msg = 'Loket ditutup dengan alasan: ' . $user->keterangan_loket;
        }
        
        $user->save();
        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function updateSpmbAccess(Request $request) 
    {
        if (Auth::user()->role !== 'super_admin') abort(403);
        
        $setting = Setting::first();
        if (!$setting) $setting = new Setting();
        
        // 🔥 IRON CLAD ARCHITECTURE: Auto Structural Guard Check 🔥
        if (!\Illuminate\Support\Facades\Schema::hasColumn('settings', 'is_pengumuman')) {
            \Illuminate\Support\Facades\Schema::table('settings', function ($table) {
                $table->tinyInteger('is_pengumuman')->default(0)->after('is_ticket_exhausted');
            });
        }
        
        $setting->spmb_open_at = $request->filled('spmb_open_at') ? $request->spmb_open_at : null; 
        
        $status = $request->system_status; 
        if ($status === 'maintenance') {
            $setting->is_maintenance = 1;
            $setting->is_ticket_exhausted = 0;
            $setting->is_pengumuman = 0;
        } elseif ($status === 'exhausted') {
            $setting->is_maintenance = 0;
            $setting->is_ticket_exhausted = 1;
            $setting->is_pengumuman = 0;
        } elseif ($status === 'pengumuman') {
            $setting->is_maintenance = 0;
            $setting->is_ticket_exhausted = 0;
            $setting->is_pengumuman = 1;
        } else {
            $setting->is_maintenance = 0;
            $setting->is_ticket_exhausted = 0;
            $setting->is_pengumuman = 0;
        }
        
        $setting->save();
        
        return back()->with('success', 'Pengaturan Akses Pendaftaran SPMB berhasil diperbarui!');
    }

    public function cekButaWarna(Request $request)
    {
        $input = $request->input_data;
        $nisn = $input;

        if (str_contains($input, '-')) {
            $ticket = \App\Models\Ticket::where('ticket_number', $input)->first();
            if ($ticket) {
                $nisn = $ticket->nisn;
            } else {
                return response()->json(['success' => false, 'message' => 'QR Code Tiket tidak valid atau tidak ditemukan di sistem.']);
            }
        }

        if (!$nisn) {
            return response()->json(['success' => false, 'message' => 'Input NISN / Scanner Tiket tidak boleh kosong.']);
        }

        $test = \Illuminate\Support\Facades\DB::table('butawarna_tests')->where('nisn', $nisn)->first();

        if ($test) {
            return response()->json([
                'success' => true,
                'data' => $test,
                'print_url' => route('dokumen.butawarna.reprint', $nisn) 
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Pendaftar dengan NISN ('.$nisn.') belum mengikuti Screening Tes Buta Warna.']);
    }

    public function storeGoShow(Request $request)
    {
        $request->validate([
            'nisn' => 'required|numeric|unique:tickets,nisn',
            'nama_cpd' => 'required|string|max:255',
            'asal_sekolah' => 'required|string|max:255',
            'whatsapp' => 'required|string|max:20',
            'email' => 'required|email',
            'alamat' => 'required|string',
            'jalur_pendaftaran' => 'required|in:KIP,DTKS,KIP & DTKS,REGULER',
        ], ['nisn.unique' => 'NISN ini sudah terdaftar di sistem online/offline. Siswa tidak boleh mendaftar ganda.']);

        $currentTime = \Carbon\Carbon::now('Asia/Jakarta')->format('H:i:s');
        $todayAbsolute = \Carbon\Carbon::now('Asia/Jakarta')->toDateString();

        $todaySession = ServiceDate::where('is_active', true)
                        ->whereDate('service_date', $todayAbsolute)
                        ->whereTime('end_time', '>=', $currentTime)
                        ->orderBy('start_time', 'asc')
                        ->first();

        if (!$todaySession) {
            $todaySession = ServiceDate::where('is_active', true)
                            ->whereDate('service_date', $todayAbsolute)
                            ->orderBy('start_time', 'desc')
                            ->first();
        }

        if (!$todaySession) {
            return response()->json([
                'success' => false, 
                'message' => 'GAGAL: Belum ada Sesi Layanan yang dibuka untuk HARI INI. Silakan Super Admin membuka sesi terlebih dahulu di menu Pengaturan Sesi.'
            ]);
        }

        $generated = $this->generateSequenceNumber('GS');
        $ticketNumber = $generated->full;
        $shortNumber = $generated->short;

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
            'service_date_id' => $todaySession->id, 
            'status' => 'hadir_di_lokasi', 
            'qr_code_path' => $qrPath,
        ]);
        
        $todaySession->increment('booked_count');
        
        return response()->json([
            'success' => true, 
            'message' => 'Tiket Go-Show Berhasil Dibuat & Siswa Langsung Masuk Antrian!',
            'ticket_number' => $shortNumber, 
            'nama_cpd' => strtoupper($ticket->nama_cpd)
        ]);
    }
}