<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
// 🔥 PASTIKAN AUTH DI-IMPORT 🔥
use Illuminate\Support\Facades\Auth; 

class AdminBackupController extends Controller
{
    // 1. Menampilkan Halaman Menu Backup/Restore
    public function index()
    {
        // 🛑 GEMBOK KEAMANAN
        if (Auth::user()->role !== 'super_admin') abort(403, 'Akses Terlarang! Khusus Super Admin.');
        
        return view('backend.backup.index');
    }

    // 2. Backup
    public function backup()
    {
        // 🛑 GEMBOK KEAMANAN
        if (Auth::user()->role !== 'super_admin') abort(403, 'Akses Terlarang! Khusus Super Admin.');
        
        try {
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            $dbHost = env('DB_HOST');
            $dbPort = env('DB_PORT', '3306');

            // Gunakan folder /tmp sebagai transit agar tidak terhalang permission Laravel
            $fileName = 'backup_disapa_' . date('Y-m-d_H-i-s') . '.sql';
            $tempPath = '/tmp/' . $fileName;
            $finalPath = storage_path('app/public/' . $fileName);

            // Buat folder storage jika belum ada
            if (!File::exists(storage_path('app/public'))) {
                File::makeDirectory(storage_path('app/public'), 0777, true);
            }

            $passStr = $dbPass ? "-p\"{$dbPass}\"" : "";
            
            // Perintah mysqldump dengan bypass SSL
            $command = "mysqldump -u \"{$dbUser}\" {$passStr} -h \"{$dbHost}\" -P \"{$dbPort}\" --ssl-mode=DISABLED \"{$dbName}\" > \"{$tempPath}\" 2>&1";
            
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            // Jika gagal, coba fallback ke --skip-ssl (untuk MariaDB)
            if (!File::exists($tempPath) || filesize($tempPath) < 100) {
                $command = "mysqldump -u \"{$dbUser}\" {$passStr} -h \"{$dbHost}\" -P \"{$dbPort}\" --skip-ssl \"{$dbName}\" > \"{$tempPath}\" 2>&1";
                exec($command, $output, $returnVar);
            }

            // Jika file berhasil dibuat di /tmp, pindahkan ke storage Laravel untuk didownload
            if (File::exists($tempPath) && filesize($tempPath) > 100) {
                File::move($tempPath, $finalPath);
                return response()->download($finalPath)->deleteFileAfterSend(true);
            } else {
                $errorLog = implode(" | ", $output);
                return back()->with('error', 'Gagal: ' . ($errorLog ?: "Mysqldump tidak ditemukan atau akses ditolak."));
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Sistem Error: ' . $e->getMessage());
        }
    }

    // 3. Proses Restore
    public function restore(Request $request)
    {
        // 🛑 GEMBOK KEAMANAN
        if (Auth::user()->role !== 'super_admin') abort(403, 'Akses Terlarang! Khusus Super Admin.');

        $request->validate([
            'file_sql' => 'required|file|max:51200',
        ]);

        try {
            $dbName = env('DB_DATABASE');
            $dbUser = env('DB_USERNAME');
            $dbPass = env('DB_PASSWORD');
            $dbHost = env('DB_HOST');
            $dbPort = env('DB_PORT', '3306');

            $file = $request->file('file_sql');
            $tempPath = '/tmp/restore_disapa_' . time() . '.sql';
            move_uploaded_file($file->getRealPath(), $tempPath);

            $passStr = $dbPass ? "-p\"{$dbPass}\"" : "";
            
            // 1. Coba eksekusi pertama menggunakan --skip-ssl (Bawaan MariaDB)
            $command = "mysql -u \"{$dbUser}\" {$passStr} -h \"{$dbHost}\" -P \"{$dbPort}\" --skip-ssl \"{$dbName}\" < \"{$tempPath}\" 2>&1";
            
            $output = [];
            $returnVar = 0;
            exec($command, $output, $returnVar);

            $errorLog = strtolower(implode(" ", $output));

            // 2. Jika gagal karena opsi tidak dikenali
            if ($returnVar !== 0 && (strpos($errorLog, 'unknown option') !== false || strpos($errorLog, 'unknown variable') !== false)) {
                $output = [];
                $command = "mysql -u \"{$dbUser}\" {$passStr} -h \"{$dbHost}\" -P \"{$dbPort}\" --ssl-mode=DISABLED \"{$dbName}\" < \"{$tempPath}\" 2>&1";
                exec($command, $output, $returnVar);
            }

            // Hapus file sementara
            if (File::exists($tempPath)) {
                File::delete($tempPath);
            }

            // Cek hasil akhir
            if ($returnVar === 0) {
                // Logout user untuk mencegah session clash
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect('/login')->with('success', 'RESTORE BERHASIL! Silakan login kembali dengan data baru Anda.');
            } else {
                $finalError = implode(" | ", $output);
                return back()->with('error', 'Gagal mengeksekusi MySQL: ' . $finalError);
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Sistem Error: ' . $e->getMessage());
        }
    }

    // 4. Proses Reset
    public function reset(Request $request)
    {
        // 🛑 GEMBOK KEAMANAN
        if (Auth::user()->role !== 'super_admin') abort(403, 'Akses Terlarang! Khusus Super Admin.');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // 1. KOSONGKAN TABEL TIKET/ANTRIAN
            $tablesToClean = [
                'tickets',           
            ];

            $clearedTables = [];

            foreach ($tablesToClean as $table) {
                try {
                    DB::statement("TRUNCATE TABLE `{$table}`");
                    $clearedTables[] = $table; 
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Gagal reset tabel {$table}: " . $e->getMessage());
                }
            }
            
            // =================================================================
            // 2. RESET BOOKED_COUNT PADA TABEL SERVICE_DATES
            // =================================================================
            try {
                DB::table('service_dates')->update(['booked_count' => 0]);
                $clearedTables[] = 'kuota antrian (booked_count)';
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal mereset booked_count: " . $e->getMessage());
            }
            // =================================================================

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            if (count($clearedTables) > 0) {
                $namaTabel = implode(', ', $clearedTables);
                return back()->with('success', "Database berhasil dibersihkan! Data yang dieksekusi: [{$namaTabel}].");
            } else {
                return back()->with('error', 'Tidak ada data yang dihapus!');
            }

        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'Gagal membersihkan database. Error: ' . $e->getMessage());
        }
    }
}