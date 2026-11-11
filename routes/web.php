<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

// 🌐 FRONTEND ANTRIAN PUBLIK (MULTI-EVENT DINAMIS)
Route::get('/', [TicketController::class, 'create'])->name('ticket.create');
Route::get('/e/{slug}', [TicketController::class, 'showEvent'])->name('ticket.event');
Route::post('/ticket', [TicketController::class, 'store'])->name('ticket.store');
Route::post('/ticket/reprint', [TicketController::class, 'reprint'])->name('ticket.reprint');
Route::get('/ticket/success/{ticket_number}', [TicketController::class, 'success'])->name('ticket.success');
Route::get('/ticket/download/{ticket_number}', [TicketController::class, 'download'])->name('ticket.download');

// 📺 LAYAR MONITOR AULA & LIVE TRACKER
Route::get('/layar', [TicketController::class, 'layar'])->name('ticket.layar');
Route::get('/api/layar', [TicketController::class, 'apiLayar'])->name('api.layar');
Route::get('/api/tts', [TicketController::class, 'apiTts'])->middleware('throttle:120,1')->name('api.tts');
Route::get('/api/ticket/live/{ticket_number}', [AdminController::class, 'liveMonitor'])->name('api.ticket.live');

// 🔐 AUTHENTICATION
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ⚙️ BACKEND PETUGAS & SUPER ADMIN
Route::middleware(['auth'])->prefix('admin')->group(function () {

    // --- 1. POS SCANNER CHECK-IN ---
    Route::middleware(['role:scanner,super_admin'])->group(function () {
        Route::get('/scanner', [AdminController::class, 'scanner'])->name('admin.scanner');
        Route::post('/scanner/checkin', [AdminController::class, 'scannerCheckin'])->name('admin.scanner.checkin');
        Route::post('/scanner/go-show', [AdminController::class, 'storeGoShow'])->name('admin.scanner.goshow');
    });

    // --- 2. LOKET VERIFIKATOR & DASHBOARD ---
    Route::middleware(['role:verifikator,super_admin'])->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::post('/dashboard/panggil', [AdminController::class, 'panggilTiket'])->name('admin.panggil');
        Route::post('/dashboard/menghadap', [AdminController::class, 'siswaMenghadap'])->name('admin.menghadap');
        Route::post('/dashboard/lewati', [AdminController::class, 'lewatiTiket'])->name('admin.lewati');
        Route::post('/dashboard/selesaikan', [AdminController::class, 'selesaikanTiket'])->name('admin.selesaikan');
        Route::post('/dashboard/restore', [AdminController::class, 'restoreTiket'])->name('admin.restore');
        Route::post('/dashboard/cari-nisn', [AdminController::class, 'cariNisn'])->name('admin.cari_nisn');
        Route::post('/dashboard/mutasi', [AdminController::class, 'mutasiTiket'])->name('admin.mutasi');
        Route::post('/dashboard/toggle-loket', [AdminController::class, 'toggleLoket'])->name('admin.toggle_loket');
    });

    // --- 3. MANAJEMEN SISTEM (SUPER ADMIN) ---
    Route::middleware(['role:super_admin'])->group(function () {
        Route::post('/dashboard/spmb-access', [AdminController::class, 'updateSpmbAccess'])->name('admin.spmb_access');

        // Manajemen Event & Form Dinamis
        Route::get('/manage-events', [AdminController::class, 'manageEvents'])->name('admin.manage_events');
        Route::post('/events/store', [AdminController::class, 'storeEvent'])->name('admin.events.store');
        Route::put('/events/{id}', [AdminController::class, 'updateEvent'])->name('admin.events.update');
        Route::post('/events/{id}/toggle', [AdminController::class, 'toggleEvent'])->name('admin.events.toggle');
        Route::delete('/events/{id}', [AdminController::class, 'destroyEvent'])->name('admin.events.destroy');

        // Manajemen Sesi & Kuota
        Route::get('/manage-sessions', [AdminController::class, 'manageSessions'])->name('admin.manage_sessions');
        Route::post('/service-date/store', [AdminController::class, 'storeServiceDate'])->name('admin.service_date.store');
        Route::put('/service-date/{id}', [AdminController::class, 'updateServiceDate'])->name('admin.service_date.update');
        Route::post('/service-date/{id}/toggle', [AdminController::class, 'toggleServiceDate'])->name('admin.service_date.toggle');
        Route::delete('/service-date/{id}', [AdminController::class, 'destroyServiceDate'])->name('admin.service_date.destroy');
        Route::get('/service-date/{id}/students', [AdminController::class, 'getStudentsBySession'])->name('admin.service_date.students');

        // Manajemen Akun Petugas
        Route::get('/manage-users', [AdminController::class, 'manageUsers'])->name('admin.manage_users');
        Route::post('/users/store', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::post('/users/{id}/toggle', [AdminController::class, 'toggleUser'])->name('admin.users.toggle');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');

        // Pengaturan Layar Aula
        Route::get('/manage-display', [AdminController::class, 'manageDisplay'])->name('admin.manage_display');
        Route::post('/manage-display', [AdminController::class, 'updateDisplay'])->name('admin.update_display');
        Route::post('/manage-display/live-setting', [AdminController::class, 'updateLiveSetting'])->name('admin.live_setting');

        // Tracker Kehadiran & Backup Database
        Route::get('/attendance', [AdminController::class, 'attendanceTracker'])->name('admin.attendance');
        Route::get('/backup-database', [AdminController::class, 'backupDatabase'])->name('admin.backup_db');
    });
});
