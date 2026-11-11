<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use Exception;

class DocumentGeneratorController extends Controller
{
    private function checkAccessLock()
    {
        try {
            $setting = Setting::first();
            if ($setting) {
                if ($setting->is_maintenance == 1) return true; 
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    // 🔥 HELPER BARU: Mengubah input request menjadi format array yang dibutuhkan Blade PDF 🔥
    private function enrichData(Request $request)
    {
        Carbon::setLocale('id');
        $data = $request->all();
        
        $data['tanggal_cetak'] = Carbon::now()->translatedFormat('d F Y');
        $data['lokasi_cetak'] = $request->lokasi_cetak ?? 'Kab. Tegal';

        // Fix untuk $ortu['nama'], $ortu['alamat'], dll
        $data['ortu'] = [
            'nama' => $request->ortu_nama ?? '-',
            'alamat' => $request->ortu_alamat ?? '-',
            'pekerjaan' => $request->ortu_pekerjaan ?? '-'
        ];
        $data['ortu_nama'] = $data['ortu']['nama']; // Fallback untuk PDF yang masih panggil $ortu_nama

        // Fix untuk $siswa['nama'], $siswa['nisn'], dll
        $tglLahir = $request->tanggal_lahir ?? $request->siswa_tanggal_lahir;
        $data['siswa'] = [
            'nama' => strtoupper($request->siswa_nama ?? '-'),
            'nisn' => $request->siswa_nisn ?? '-',
            'nik' => $request->siswa_nik ?? ($request->nik ?? '-'),
            'no_daftar' => $request->siswa_no_daftar ?? '-',
            'tempat_lahir' => $request->tempat_lahir ?? ($request->siswa_tempat_lahir ?? '-'),
            'tanggal_lahir' => $tglLahir ? Carbon::parse($tglLahir)->translatedFormat('d F Y') : '-',
            'alamat' => $request->alamat ?? ($request->siswa_alamat ?? '-'),
            'asal_sekolah' => $request->asal_sekolah ?? ($request->siswa_asal_sekolah ?? '-'),
            'tahun_lulus' => $request->tahun_lulus ?? '-'
        ];

        // Fix untuk $alamat['jalan'], $alamat['rt'], dll
        $data['alamat'] = [
            'jalan' => $request->alamat_jalan ?? '-',
            'rt' => $request->alamat_rt ?? '-',
            'rw' => $request->alamat_rw ?? '-',
            'desa' => $request->alamat_desa ?? '-',
            'kecamatan' => $request->alamat_kecamatan ?? '-',
            'kota' => $request->alamat_kota ?? '-'
        ];
        
        $data['program_keahlian'] = $request->program_keahlian ?? '-';

        return $data;
    }

    public function index()
    {
        if ($this->checkAccessLock()) {
            return redirect()->route('ticket.create')->with('error', 'Pusat Dokumen SPMB sedang ditutup sementara.');
        }
        return view('frontend.documents.index');
    }

    // --- KEBENARAN ---
    public function formKebenaran() { return view('frontend.documents.kebenaran-form'); }
    public function generateKebenaran(Request $request)
    {
        $data = $this->enrichData($request);
        $pdf = app('dompdf.wrapper')->loadView('frontend.documents.kebenaran-pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Surat_Kebenaran_Dokumen_'.$request->siswa_nisn.'.pdf');
    }

    // --- SEHAT ---
    public function formSehat() { return view('frontend.documents.sehat-form'); }
    public function generateSehat(Request $request)
    {
        $data = $this->enrichData($request);
        $pdf = app('dompdf.wrapper')->loadView('frontend.documents.sehat-pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Surat_Sehat_'.$request->siswa_nisn.'.pdf');
    }

    // --- DAFTAR ULANG ---
    public function formKebenaranDU() { return view('frontend.documents.kebenaran-du-form'); }
    public function generateKebenaranDU(Request $request)
    {
        $data = $this->enrichData($request);
        $pdf = app('dompdf.wrapper')->loadView('frontend.documents.kebenaran-du-pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Surat_Daftar_Ulang_'.$request->siswa_no_daftar.'.pdf');
    }

    // --- ATS ---
    public function formATS() { return view('frontend.documents.ats-form'); }
    public function generateATS(Request $request)
    {
        $data = $this->enrichData($request);
        $pdf = app('dompdf.wrapper')->loadView('frontend.documents.ats-pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Surat_ATS_'.$request->siswa_nik.'.pdf');
    }

    // --- BUTA WARNA ---
    public function formButawarna()
    {
        if ($this->checkAccessLock()) return redirect()->route('ticket.create');
        return view('frontend.documents.butawarna-form');
    }

    public function checkNisnButawarna(Request $request)
    {
        $nisn = $request->nisn;
        $exists = DB::table('butawarna_tests')->where('nisn', $nisn)->first();
        
        if ($exists) {
            if ($exists->status === 'normal') return response()->json(['success' => true, 'action' => 'already_passed', 'message' => 'NISN ini sudah Lulus Tes.']);
            return response()->json(['success' => false, 'message' => 'NISN ini terindikasi. Silakan hubungi loket.']);
        }
        
        $attempts = DB::table('butawarna_attempts')->where('nisn', $nisn)->first();
        if ($attempts && $attempts->attempt_count >= 2) return response()->json(['success' => false, 'message' => 'Maksimal tes tercapai.']);
        
        return response()->json(['success' => true, 'action' => 'start_test']);
    }

    public function generateButawarna(Request $request)
    {
        $nisn = $request->nisn;
        $score = (int) $request->score;

        if ($request->has('is_fail_report')) {
            $attempt = DB::table('butawarna_attempts')->where('nisn', $nisn)->first();
            if ($attempt) {
                DB::table('butawarna_attempts')->where('nisn', $nisn)->update(['attempt_count' => 2]);
                $exists = DB::table('butawarna_tests')->where('nisn', $nisn)->first();
                if (!$exists) {
                    DB::table('butawarna_tests')->insert([
                        'nisn' => $nisn, 'nama' => 'GAGAL TES ONLINE', 'nik' => '0000000000000000',
                        'tempat_lahir' => '-', 'tanggal_lahir' => now()->format('Y-m-d'), 'alamat' => '-',
                        'ortu_nama' => '-', 'score' => $score, 'status' => 'terindikasi',
                        'keterangan' => 'Belum Validasi Medis', 'created_at' => now()
                    ]);
                }
                return response()->json(['success' => false, 'action' => 'final_fail', 'message' => 'Gagal tes ke-2.']);
            } else {
                DB::table('butawarna_attempts')->insert(['nisn' => $nisn, 'attempt_count' => 1]);
                return response()->json(['success' => false, 'action' => 'retry', 'message' => 'Coba lagi.']);
            }
        }

        DB::table('butawarna_tests')->insert([
            'nisn' => $nisn, 'nama' => strtoupper($request->siswa_nama), 'nik' => $request->siswa_nik, 
            'tempat_lahir' => $request->tempat_lahir, 'tanggal_lahir' => $request->tanggal_lahir, 
            'alamat' => $request->siswa_alamat, 'ortu_nama' => strtoupper($request->ortu_nama), 
            'score' => $score, 'status' => 'normal', 'keterangan' => 'Lolos Tes Online', 'created_at' => now()
        ]);

        $data = $this->enrichData($request);
        $pdf = app('dompdf.wrapper')->loadView('frontend.documents.butawarna-pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Surat_Pernyataan_Tidak_Buta_Warna_' . $nisn . '.pdf');
    }

    public function reprintButawarna($nisn)
    {
        $data_db = DB::table('butawarna_tests')->where('nisn', $nisn)->where('status', 'normal')->first();
        if (!$data_db) return "Data tidak ditemukan.";

        $data = [
            'siswa' => ['nama' => $data_db->nama, 'tempat_lahir' => $data_db->tempat_lahir, 'tanggal_lahir' => Carbon::parse($data_db->tanggal_lahir)->translatedFormat('d F Y'), 'nik' => $data_db->nik, 'alamat' => $data_db->alamat], 
            'ortu_nama' => $data_db->ortu_nama, 'lokasi_cetak' => 'Kab. Tegal', 'tanggal_cetak' => Carbon::parse($data_db->created_at)->translatedFormat('d F Y')
        ];
        
        $pdf = app('dompdf.wrapper')->loadView('frontend.documents.butawarna-pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Surat_Pernyataan_Tidak_Buta_Warna_' . $nisn . '.pdf');
    }
}