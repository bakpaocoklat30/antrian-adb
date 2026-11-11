<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpmbResult;
use App\Models\Setting; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class SpmbResultController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') abort(403, 'Akses Terlarang!');

        if (!Schema::hasColumn('spmb_results', 'status_daftar_ulang')) {
            Schema::table('spmb_results', function (Blueprint $table) {
                $table->enum('status_daftar_ulang', ['belum', 'sudah'])->default('belum')->after('jurusan');
            });
        }

        $query = SpmbResult::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('asal_sekolah', 'like', "%{$search}%")
                  ->orWhere('jalur_pendaftaran', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jalur')) {
            $query->where('jalur_pendaftaran', 'like', "%{$request->jalur}%");
        }

        if ($request->filled('jurusan')) {
            $query->where('jurusan', $request->jurusan);
        }

        if ($request->filled('status')) {
            $query->where('status_daftar_ulang', $request->status);
        }

        $perPage = $request->get('per_page', 20);
        $results = $query->orderBy('nama', 'asc')->paginate($perPage)->withQueryString();

        $setting = Setting::first();
        $jadwalRaw = $setting && !empty($setting->jadwal_daftar_ulang) ? $setting->jadwal_daftar_ulang : null;
        
        $jadwalArray = [];
        if ($jadwalRaw) {
            $decoded = json_decode($jadwalRaw, true);
            if (json_last_error() === JSON_ERROR_NONE) $jadwalArray = $decoded;
        }

        if (empty($jadwalArray)) {
            $jadwalArray = [
                'TPFL' => 'Senin, 22 Juni 2026', 'TK' => 'Senin, 22 Juni 2026', 'TM' => 'Senin, 22 Juni 2026',
                'DPIB' => 'Selasa, 23 Juni 2026', 'TE' => 'Selasa, 23 Juni 2026',
                'TO' => 'Rabu, 24 Juni 2026', 'TJKT' => 'Rabu, 24 Juni 2026',
            ];
        }

        return view('backend.pengumuman.index', compact('results', 'jadwalArray'));
    }

    public function updateJadwal(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') abort(403);
        
        $request->validate([
            'jadwal' => 'required|array',
            'warna_map' => 'required|array'
        ]);
        
        $setting = Setting::first();
        if(!$setting) {
            $setting = new Setting();
        }
        
        $setting->jadwal_daftar_ulang = json_encode($request->jadwal);
        $setting->warna_map = json_encode($request->warna_map);
        $setting->save();

        return back()->with('success', 'Jadwal & Warna Map Per Jurusan berhasil diperbarui!');
    }

    public function scanTerimaBerkas(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'scanner', 'verifikator'])) abort(403, 'Akses Terlarang!');

        $request->validate([
            'nisn' => 'required'
        ]);

        $siswa = SpmbResult::where('nisn', $request->nisn)->first();

        if ($siswa) {
            if ($siswa->status_daftar_ulang === 'sudah') {
                return response()->json([
                    'success' => true,
                    'already_scanned' => true,
                    'message' => 'Berkas atas nama ' . strtoupper($siswa->nama) . ' SUDAH DITERIMA sebelumnya.'
                ]);
            }

            $siswa->status_daftar_ulang = 'sudah';
            $siswa->save();

            return response()->json([
                'success' => true,
                'already_scanned' => false,
                'data' => $siswa,
                'message' => 'BERHASIL! Berkas fisik ' . strtoupper($siswa->nama) . ' (Jurusan: ' . $siswa->jurusan . ') resmi DITERIMA.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'GAGAL: Data ' . $request->nisn . ' tidak ditemukan dalam data kelulusan.'
        ]);
    }

    public function downloadTemplate()
    {
        if (auth()->user()->role !== 'super_admin') abort(403);

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Template_Import_Kelulusan_SPMB.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['NISN_ATAU_NO_PENDAFTARAN', 'NAMA', 'JALUR_PENDAFTARAN', 'JURUSAN'];
        
        $callback = function() use($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ';'); 
            fputcsv($file, ['1081234567', 'BUDI SANTOSO', 'DOMISILI', 'TJKT'], ';');
            fputcsv($file, ['PEN-2026-001', 'SITI AMINAH', 'PRESTASI', 'DPIB'], ';');
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') abort(403);

        $request->validate([
            'nisn' => ['required', 'string', 'regex:/^([a-zA-Z0-9]{10}|[a-zA-Z0-9]{16})$/', 'unique:spmb_results,nisn'],
            'nama' => 'required|string|max:255',
            'jalur_pendaftaran' => 'required|string|max:255', 
            'jurusan' => 'required|string|in:TPFL,TK,TM,DPIB,TE,TO,TJKT',
        ], [
            'nisn.regex' => 'Data harus tepat 10 Karakter (NISN) atau 16 Karakter (No Pendaftaran)!',
            'nisn.unique' => 'Gagal! NISN / Nomor Pendaftaran tersebut sudah ada di database.'
        ]);

        // 🔥 LOGIKA STANDARISASI JALUR 🔥
        $jalur = strtoupper(trim($request->jalur_pendaftaran));
        if (strpos($jalur, 'DOMISILI') !== false || strpos($jalur, 'TERDEKAT') !== false) {
            $jalur = 'DOMISILI';
        } elseif (strpos($jalur, 'AFIRMASI') !== false) {
            $jalur = 'AFIRMASI';
        } elseif (strpos($jalur, 'PRESTASI') !== false) {
            $jalur = 'PRESTASI';
        }

        SpmbResult::create([
            'nisn' => $request->nisn,
            'nama' => strtoupper($request->nama),
            'jalur_pendaftaran' => $jalur, 
            'jurusan' => strtoupper($request->jurusan),
        ]);

        return back()->with('success', 'Data kelulusan siswa berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'super_admin') abort(403);

        $result = SpmbResult::findOrFail($id);

        $request->validate([
            'nisn' => ['required', 'string', 'regex:/^([a-zA-Z0-9]{10}|[a-zA-Z0-9]{16})$/', 'unique:spmb_results,nisn,' . $id],
            'nama' => 'required|string|max:255',
            'jalur_pendaftaran' => 'required|string|max:255', 
            'jurusan' => 'required|string|in:TPFL,TK,TM,DPIB,TE,TO,TJKT',
        ], [
            'nisn.regex' => 'Data harus tepat 10 Karakter (NISN) atau 16 Karakter (No Pendaftaran)!',
            'nisn.unique' => 'Gagal! NISN / Nomor Pendaftaran tersebut sudah ada di database.'
        ]);

        // 🔥 LOGIKA STANDARISASI JALUR 🔥
        $jalur = strtoupper(trim($request->jalur_pendaftaran));
        if (strpos($jalur, 'DOMISILI') !== false || strpos($jalur, 'TERDEKAT') !== false) {
            $jalur = 'DOMISILI';
        } elseif (strpos($jalur, 'AFIRMASI') !== false) {
            $jalur = 'AFIRMASI';
        } elseif (strpos($jalur, 'PRESTASI') !== false) {
            $jalur = 'PRESTASI';
        }

        $result->update([
            'nisn' => $request->nisn,
            'nama' => strtoupper($request->nama),
            'jalur_pendaftaran' => $jalur, 
            'jurusan' => strtoupper($request->jurusan),
        ]);

        return back()->with('success', 'Data kelulusan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'super_admin') abort(403);
        SpmbResult::findOrFail($id)->delete();
        return back()->with('success', 'Data kelulusan berhasil dihapus!');
    }

    public function truncate()
    {
        if (auth()->user()->role !== 'super_admin') abort(403);
        SpmbResult::truncate();
        return back()->with('success', 'Semua data kelulusan berhasil dibersihkan!');
    }

    public function importCsv(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') abort(403);

        $request->validate([
            'file_csv' => 'required|mimes:csv,txt|max:10240',
        ], [
            'file_csv.mimes' => 'File harus berekstensi .csv',
            'file_csv.max' => 'Ukuran file maksimal 10MB'
        ]);

        ini_set('auto_detect_line_endings', TRUE);

        $file = $request->file('file_csv');
        $handle = fopen($file->getPathname(), "r");

        $firstLine = fgets($handle);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        rewind($handle);

        $header = fgetcsv($handle, 1000, $delimiter);
        $logs = []; 

        if (!$header) {
            return back()->with('error', 'File CSV kosong atau tidak bisa dibaca.');
        }

        $header[0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $header[0]);
        $logs[] = "INFO: Delimiter otomatis terdeteksi = '" . $delimiter . "'";
        $logs[] = "INFO: Header = " . json_encode($header);
        
        if (count($header) < 4) {
            $logs[] = "CRITICAL ERROR: Kolom tidak sesuai format. Harap gunakan template asli.";
            return back()->with('error', 'Format CSV salah! Cek Live Debugger.')->with('import_logs', $logs);
        }

        $inserted = 0;
        $skipped = 0;
        $rowCount = 1; 

        DB::beginTransaction();
        try {
            while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                $rowCount++;
                
                if (empty($data) || count($data) < 2) {
                    $logs[] = "Baris $rowCount: SKIP (Baris kosong/rusak di Excel)";
                    continue; 
                }

                $nisn = preg_replace('/[^a-zA-Z0-9]/', '', trim($data[0])); 
                $nama = strtoupper(trim($data[1]));
                $jalurRaw = strtoupper(trim($data[2] ?? '-'));
                $jurusan = strtoupper(trim($data[3] ?? '-'));

                // 🔥 LOGIKA STANDARISASI JALUR (FIX IMPORT BUG) 🔥
                $jalur = $jalurRaw;
                if (strpos($jalurRaw, 'DOMISILI') !== false || strpos($jalurRaw, 'TERDEKAT') !== false) {
                    $jalur = 'DOMISILI';
                } elseif (strpos($jalurRaw, 'AFIRMASI') !== false) {
                    $jalur = 'AFIRMASI';
                } elseif (strpos($jalurRaw, 'PRESTASI') !== false) {
                    $jalur = 'PRESTASI';
                }

                if (strlen($nisn) !== 10 && strlen($nisn) !== 16) {
                    $logs[] = "Baris $rowCount: GAGAL (Bukan 10/16 karakter) -> Data: $nisn";
                    $skipped++;
                    continue;
                }

                $exists = SpmbResult::where('nisn', $nisn)->exists();
                
                if ($exists) {
                    $logs[] = "Baris $rowCount: SKIP (Data $nisn sudah terdaftar).";
                    $skipped++;
                } else {
                    SpmbResult::create([
                        'nisn' => $nisn,
                        'nama' => $nama,
                        'jalur_pendaftaran' => $jalur,
                        'jurusan' => $jurusan,
                    ]);
                    $inserted++;
                    $logs[] = "Baris $rowCount: SUKSES ($nama masuk).";
                }
            }
            DB::commit();
            fclose($handle);

            $msgType = $inserted > 0 ? 'success' : 'error';
            return back()->with($msgType, "Import Selesai! $inserted data masuk, $skipped data dilewati.")->with('import_logs', $logs);
            
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            $logs[] = "CRITICAL ERROR PADA BARIS $rowCount: " . $e->getMessage();
            return back()->with('error', 'Terjadi kesalahan sistem saat import. Cek Live Debugger.')->with('import_logs', $logs);
        }
    }

    public function exportCsv(Request $request)
    {
        if (auth()->user()->role !== 'super_admin') abort(403);

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Export_Data_Kelulusan_SPMB_" . date('Y-m-d_H-i') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['NISN_ATAU_NO_PENDAFTARAN', 'NAMA', 'JALUR_PENDAFTARAN', 'JURUSAN', 'STATUS_DAFTAR_ULANG'];
        
        $query = SpmbResult::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nisn', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('asal_sekolah', 'like', "%{$search}%")
                  ->orWhere('jalur_pendaftaran', 'like', "%{$search}%");
            });
        }
        if ($request->filled('jalur')) {
            $query->where('jalur_pendaftaran', 'like', "%{$request->jalur}%");
        }
        if ($request->filled('jurusan')) {
            $query->where('jurusan', $request->jurusan);
        }
        if ($request->filled('status')) {
            $query->where('status_daftar_ulang', $request->status);
        }

        $results = $query->orderBy('nama', 'asc')->get();

        $callback = function() use($results, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ';'); 
            foreach ($results as $row) {
                fputcsv($file, [
                    $row->nisn,
                    $row->nama,
                    $row->jalur_pendaftaran,
                    $row->jurusan,
                    strtoupper($row->status_daftar_ulang)
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}