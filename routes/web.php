<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SpmbResultController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 🌐 FRONTEND SISWA (PUBLIC)
Route::get('/', [TicketController::class, 'create'])->name('ticket.create');
Route::post('/ticket', [TicketController::class, 'store'])->name('ticket.store');
Route::post('/ticket/reprint', [TicketController::class, 'reprint'])->name('ticket.reprint');
Route::get('/ticket/success/{ticket_number}', [TicketController::class, 'success'])->name('ticket.success');
Route::get('/ticket/download/{ticket_number}', [TicketController::class, 'download'])->name('ticket.download');

// 🎓 PENGUMUMAN KELULUSAN & FORM DAFTAR ULANG (/welcome-adb)
Route::get('/welcome-adb', [WelcomeController::class, 'index'])->name('welcome.index');
Route::post('/welcome-adb/check', [WelcomeController::class, 'checkNisn'])->name('welcome.check');
Route::get('/welcome-adb/form/{nisn}', [WelcomeController::class, 'formDaftarUlang'])->name('welcome.form');
Route::post('/welcome-adb/submit', [WelcomeController::class, 'submitForm'])->name('welcome.submit');
Route::get('/welcome-adb/download/{nisn}', [WelcomeController::class, 'generatePdf'])->name('welcome.download');

// 📺 LAYAR MONITOR AULA
Route::get('/layar', [TicketController::class, 'layar'])->name('ticket.layar');
Route::get('/api/layar', [TicketController::class, 'apiLayar'])->name('api.layar');

// API LIVE MONITOR
Route::get('/api/ticket/live/{ticket_number}', [AdminController::class, 'liveMonitor'])->name('api.ticket.live');

// 🔐 AUTHENTICATION
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================================
// 🔥 JALUR DARURAT: RESET PASSWORD SUPER ADMIN 🔥
// ==========================================================
Route::get('/super-recovery', function () {
    return view('auth.super-recovery');
})->name('super.recovery');

Route::post('/super-recovery', function (Request $request) {
    $request->validate([
        'pin' => 'required',
        'new_password' => 'required|min:6|confirmed'
    ]);

    $envPin = env('SUPER_ADMIN_RECOVERY_PIN');
    
    if (!$envPin || $request->pin !== $envPin) {
        return back()->with('error', 'PIN Darurat salah atau belum disetting di file .env!');
    }

    $superAdmin = User::where('role', 'super_admin')->first();
    if (!$superAdmin) {
        return back()->with('error', 'Akun Super Admin tidak ditemukan di database!');
    }

    $superAdmin->password = Hash::make($request->new_password);
    $superAdmin->save();

    return redirect('/login')->with('success', 'Password Super Admin berhasil direset paksa! Silakan login.');
})->name('super.recovery.process');
// ==========================================================

// ⚙️ BACKEND ADMIN & SUPER ADMIN
Route::middleware(['auth'])->prefix('admin')->group(function () {
    
    // --- 1. RUTE POS OSIS (SCANNER) ---
    Route::get('/scanner', function () {
        if(Auth::user()->role !== 'scanner' && Auth::user()->role !== 'super_admin') abort(403, 'Akses Ditolak! Khusus Scanner OSIS.');
        return view('backend.scanner.index');
    })->name('admin.scanner');

    Route::post('/scanner/checkin', function (Illuminate\Http\Request $request) {
        $ticket = App\Models\Ticket::with('serviceDate')->where('ticket_number', $request->ticket_number)->first();
        if (!$ticket) return response()->json(['success' => false, 'message' => 'Tiket Tidak Dikenali / Palsu!']);
        
        if ($ticket->serviceDate && $ticket->serviceDate->is_active == 0) {
            return response()->json(['success' => false, 'message' => 'DITOLAK! Sesi "' . $ticket->serviceDate->session_name . '" untuk tiket ini SUDAH DITUTUP oleh Admin. Siswa tidak dapat melakukan Check-in.']);
        }

        $today = \Carbon\Carbon::today()->format('Y-m-d');
        $ticketDate = \Carbon\Carbon::parse($ticket->serviceDate->service_date)->format('Y-m-d');
        $formattedTicketDate = \Carbon\Carbon::parse($ticketDate)->format('d F Y');
        
        if ($ticketDate < $today) return response()->json(['success' => false, 'message' => "Tiket KADALUARSA!\nJadwal siswa ini adalah $formattedTicketDate. Silakan arahkan siswa untuk mendaftar antrian baru atau ke Meja Panitia."]);
        elseif ($ticketDate > $today) return response()->json(['success' => false, 'message' => "Tiket BELUM AKTIF!\nJadwal siswa ini untuk tanggal $formattedTicketDate. Tidak bisa Check-In hari ini."]);
        
        if ($ticket->status == 'hadir_di_lokasi') return response()->json(['success' => false, 'message' => 'Siswa ini SUDAH Check-In sebelumnya!']);
        if ($ticket->status != 'menunggu') return response()->json(['success' => false, 'message' => 'Status tiket sudah tidak valid (Mungkin sudah selesai diproses).']);
        
        $ticket->status = 'hadir_di_lokasi'; 
        $ticket->save();

        $antrianDiDepan = \App\Models\Ticket::where('service_date_id', $ticket->service_date_id)
            ->where('status', 'hadir_di_lokasi')
            ->where('updated_at', '<', $ticket->updated_at ?? now())
            ->count();
        $estimasiWaktu = ceil(($antrianDiDepan / 7) * 5); 

        return response()->json([
            'success' => true, 
            'message' => 'Check-In Berhasil! ' . $ticket->nama_cpd,
            'antrian_di_depan' => $antrianDiDepan,
            'estimasi_waktu' => $estimasiWaktu
        ]);
    })->name('admin.scanner.checkin');

    Route::post('/scanner/go-show', [AdminController::class, 'storeGoShow'])->name('admin.scanner.goshow');

    // --- 2. RUTE BERSAMA (LOKET VERIFIKATOR & SUPER ADMIN) ---
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/dashboard/panggil', [AdminController::class, 'panggilTiket'])->name('admin.panggil');
    Route::post('/dashboard/menghadap', [AdminController::class, 'siswaMenghadap'])->name('admin.menghadap');
    Route::post('/dashboard/lewati', [AdminController::class, 'lewatiTiket'])->name('admin.lewati');
    Route::post('/dashboard/selesaikan', [AdminController::class, 'selesaikanTiket'])->name('admin.selesaikan');
    Route::post('/dashboard/restore', [AdminController::class, 'restoreTiket'])->name('admin.restore');
    Route::post('/dashboard/cari-nisn', [AdminController::class, 'cariNisn'])->name('admin.cari_nisn');
    Route::post('/dashboard/mutasi', [AdminController::class, 'mutasiTiket'])->name('admin.mutasi');
    Route::post('/dashboard/spmb-access', [AdminController::class, 'updateSpmbAccess'])->name('admin.spmb_access');
    Route::post('/dashboard/cek-butawarna', [AdminController::class, 'cekButaWarna'])->name('admin.cek_butawarna');

    // =================================================================================
    // 🔥 3. RUANGAN VIP: RUTE KHUSUS SUPER ADMIN 🔥
    // =================================================================================
    
    // Management Sesi
    Route::get('/manage-sessions', [AdminController::class, 'manageSessions'])->name('admin.manage_sessions');
    Route::post('/service-date/store', [AdminController::class, 'storeServiceDate'])->name('admin.service_date.store');
    Route::put('/service-date/{id}', [AdminController::class, 'updateServiceDate'])->name('admin.service_date.update');
    Route::post('/service-date/{id}/toggle', [AdminController::class, 'toggleServiceDate'])->name('admin.service_date.toggle');
    Route::delete('/service-date/{id}', [AdminController::class, 'destroyServiceDate'])->name('admin.service_date.destroy');
    Route::get('/service-date/{id}/students', [AdminController::class, 'getStudentsBySession'])->name('admin.service_date.students');

    // Management Users
    Route::get('/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manage_users');
    Route::post('/users/store', [AdminController::class, 'storeUser'])->name('admin.users.store');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
    Route::post('/users/{id}/toggle', [AdminController::class, 'toggleUser'])->name('admin.users.toggle');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

    // Pengaturan Layar Aula
    Route::get('/manage-display', [AdminController::class, 'manageDisplay'])->name('admin.manage_display');
    Route::post('/manage-display', [AdminController::class, 'updateDisplay'])->name('admin.update_display');
    Route::post('/manage-display/live-setting', [AdminController::class, 'updateLiveSetting'])->name('admin.live_setting');

    // Tracker Kehadiran
    Route::get('/attendance', [AdminController::class, 'attendanceTracker'])->name('admin.attendance');

    // Backup & Restore Database
    Route::get('/backup-restore', [\App\Http\Controllers\AdminBackupController::class, 'index'])->name('admin.backup.index');
    Route::get('/backup/download', [\App\Http\Controllers\AdminBackupController::class, 'backup'])->name('admin.backup.download');
    Route::post('/restore/upload', [\App\Http\Controllers\AdminBackupController::class, 'restore'])->name('admin.restore.upload');
    Route::post('/backup-restore/reset', [\App\Http\Controllers\AdminBackupController::class, 'reset'])->name('admin.backup.reset');

    // 🔥 DATA KELULUSAN SPMB (IMPORTER) 🔥
    Route::get('/pengumuman', [SpmbResultController::class, 'index'])->name('admin.pengumuman.index');
    Route::post('/pengumuman/jadwal', [SpmbResultController::class, 'updateJadwal'])->name('admin.pengumuman.jadwal');
    Route::get('/pengumuman/template', [SpmbResultController::class, 'downloadTemplate'])->name('admin.pengumuman.template');
    Route::post('/pengumuman/store', [SpmbResultController::class, 'store'])->name('admin.pengumuman.store');
    Route::put('/pengumuman/{id}', [SpmbResultController::class, 'update'])->name('admin.pengumuman.update');
    Route::delete('/pengumuman/{id}', [SpmbResultController::class, 'destroy'])->name('admin.pengumuman.destroy');
    Route::post('/pengumuman/truncate', [SpmbResultController::class, 'truncate'])->name('admin.pengumuman.truncate');
    Route::post('/pengumuman/import', [SpmbResultController::class, 'importCsv'])->name('admin.pengumuman.import');
    Route::get('/pengumuman/export', [SpmbResultController::class, 'exportCsv'])->name('admin.pengumuman.export'); // 🔥 RUTE BARU EXPORT CSV 🔥
    
    // 🔥 RUTE BARU: SCANNER PENERIMAAN BERKAS DAFTAR ULANG 🔥
    Route::post('/pengumuman/scan-berkas', [SpmbResultController::class, 'scanTerimaBerkas'])->name('admin.pengumuman.scan_berkas');
});

// --- ROUTE GENERATOR DOKUMEN SPMB ---
Route::prefix('generator-dokumen')->group(function () {
    Route::get('/', [\App\Http\Controllers\DocumentGeneratorController::class, 'index'])->name('dokumen.index'); 
    Route::get('/kebenaran-dokumen', [\App\Http\Controllers\DocumentGeneratorController::class, 'formKebenaran'])->name('dokumen.kebenaran.form');
    Route::post('/kebenaran-dokumen/download', [\App\Http\Controllers\DocumentGeneratorController::class, 'generateKebenaran'])->name('dokumen.kebenaran.download');
    Route::get('/pernyataan-sehat', [\App\Http\Controllers\DocumentGeneratorController::class, 'formSehat'])->name('dokumen.sehat.form');
    Route::post('/pernyataan-sehat/download', [\App\Http\Controllers\DocumentGeneratorController::class, 'generateSehat'])->name('dokumen.sehat.download');
    Route::get('/kebenaran-dokumen-du', [\App\Http\Controllers\DocumentGeneratorController::class, 'formKebenaranDU'])->name('dokumen.kebenaran-du.form');
    Route::post('/kebenaran-dokumen-du/download', [\App\Http\Controllers\DocumentGeneratorController::class, 'generateKebenaranDU'])->name('dokumen.kebenaran-du.download');
    Route::get('/pernyataan-ats', [\App\Http\Controllers\DocumentGeneratorController::class, 'formATS'])->name('dokumen.ats.form');
    Route::post('/pernyataan-ats/download', [\App\Http\Controllers\DocumentGeneratorController::class, 'generateATS'])->name('dokumen.ats.download');

    Route::get('/pernyataan-butawarna', [\App\Http\Controllers\DocumentGeneratorController::class, 'formButawarna'])->name('dokumen.butawarna.form');
    Route::post('/pernyataan-butawarna/check-nisn', [\App\Http\Controllers\DocumentGeneratorController::class, 'checkNisnButawarna'])->name('dokumen.butawarna.check_nisn');
    Route::post('/pernyataan-butawarna/download', [\App\Http\Controllers\DocumentGeneratorController::class, 'generateButawarna'])->name('dokumen.butawarna.download');
    Route::get('/pernyataan-butawarna/reprint/{nisn}', [\App\Http\Controllers\DocumentGeneratorController::class, 'reprintButawarna'])->name('dokumen.butawarna.reprint');

    Route::post('/dashboard/toggle-loket', [AdminController::class, 'toggleLoket'])->name('admin.toggle_loket');
});