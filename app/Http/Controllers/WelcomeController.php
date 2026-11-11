<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpmbResult;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class WelcomeController extends Controller
{
    public function index()
    {
        // 🛡️ SECURITY ACCESS GUARD STRICT MODE 🛡️
        $setting = Setting::first();
        if ($setting) {
            $tz = 'Asia/Jakarta';
            $isMaintenance = ($setting->is_maintenance == 1);
            $isPengumumanMode = (isset($setting->is_pengumuman) && $setting->is_pengumuman == 1);
            $openAt = $setting->spmb_open_at ? Carbon::parse($setting->spmb_open_at, $tz) : null;
            
            // Jika maintenance, tendang balik ke halaman depan
            if ($isMaintenance) {
                return redirect()->route('ticket.create')->with('error', 'AKSES DITOLAK! Portal kelulusan sedang dalam perbaikan teknis.');
            }
            
            // 🛡️ BENTENG THE "SWAP-LOCK" ENGINE (BACKEND LEVEL) 🛡️
            if ($isPengumumanMode && $openAt) {
                if (now($tz)->lt($openAt)) {
                    return redirect()->route('ticket.create')->with('error', 'Sabar ya, pengumuman resmi belum dibuka. Hitung mundur masih berjalan!');
                }
            }
        }

        return view('frontend.welcome.index');
    }

    public function checkNisn(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string'
        ]);

        $siswa = SpmbResult::where('nisn', $request->nisn)->first();

        if ($siswa) {
            return response()->json([
                'success' => true,
                'redirect' => route('welcome.form', ['nisn' => $siswa->nisn])
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Data tidak terdaftar dalam hasil SPMB SMKN 1 Adiwerna.'
        ]);
    }

    public function formDaftarUlang($nisn)
    {
        $siswa = SpmbResult::where('nisn', $nisn)->firstOrFail();

        $setting = Setting::first();
        $jadwalRaw = $setting && !empty($setting->jadwal_daftar_ulang) ? $setting->jadwal_daftar_ulang : null;

        $jadwalArray = [];
        if ($jadwalRaw) {
            $decoded = json_decode($jadwalRaw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $jadwalArray = $decoded;
            }
        }

        if (empty($jadwalArray)) {
            $jadwalArray = [
                'TPFL' => 'Senin, 22 Juni 2026', 'TK' => 'Senin, 22 Juni 2026', 'TM' => 'Senin, 22 Juni 2026',
                'DPIB' => 'Selasa, 23 Juni 2026', 'TE' => 'Selasa, 23 Juni 2026',
                'TO' => 'Rabu, 24 Juni 2026', 'TJKT' => 'Rabu, 24 Juni 2026',
            ];
        }

        $jadwalSiswa = $jadwalArray[strtoupper($siswa->jurusan)] ?? 'Menunggu Jadwal dari Panitia';

        return view('frontend.welcome.form', compact('siswa', 'jadwalSiswa'));
    }

    public function submitForm(Request $request)
    {
        $request->validate([
            'nisn' => 'required|string',
            'no_pendaftaran' => 'required',
            'nik' => 'required',
            'sekolah_smp' => 'required'
        ]);

        $siswa = SpmbResult::where('nisn', $request->nisn)->first();

        if ($siswa) {
            // 🔥 REVERSE-CAPTURE HACK: Update data "Asal Sekolah" dari inputan siswa! 🔥
            $siswa->update([
                'asal_sekolah' => strtoupper($request->sekolah_smp),
                'latitude'     => $request->latitude,  // 👈 Tambahkan baris ini
                'longitude'    => $request->longitude  // 👈 Tambahkan baris ini
            ]);

            session(['pdf_data_' . $siswa->nisn => $request->all()]);

            return response()->json([
                'success' => true,
                'message' => 'Biodata berhasil disimpan sementara!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan, data siswa tidak ditemukan.'
        ]);
    }

    public function generatePdf($nisn)
    {
        $siswa_db = SpmbResult::where('nisn', $nisn)->firstOrFail();
        $formData = session('pdf_data_' . $nisn);

        if (!$formData) {
            return redirect()->route('welcome.form', $nisn)->with('error', 'Sesi formulir habis, silakan isi kembali.');
        }

        Carbon::setLocale('id');
        $tanggal_cetak = Carbon::now()->translatedFormat('d F Y');

        $data = $formData;
        $data['siswa_db'] = $siswa_db;
        $data['tanggal_cetak'] = $tanggal_cetak;

        $pdf = Pdf::loadView('frontend.welcome.pdf_biodata', $data)->setPaper('A4', 'portrait');
        $nama_file = 'BERKAS_DAFTAR_ULANG_' . $siswa_db->jurusan . '_' . $siswa_db->nisn . '_' . str_replace(' ', '_', strtoupper($siswa_db->nama)) . '.pdf';

        return $pdf->download($nama_file);
    }
}