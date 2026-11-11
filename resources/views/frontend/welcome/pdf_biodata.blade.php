<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berkas Daftar Ulang - {{ $siswa_db->nama }}</title>
    
    @php
        $jalur_verif = strtoupper($siswa_db->jalur_pendaftaran);
        if ($jalur_verif == 'DOMISILI' || $jalur_verif == 'JARAK TERDEKAT' || $jalur_verif == 'TERDEKAT') {
            $judul_jalur = 'JARAK TERDEKAT';
        } elseif ($jalur_verif == 'AFIRMASI') {
            $judul_jalur = 'AFIRMASI';
        } elseif ($jalur_verif == 'PRESTASI') {
            $judul_jalur = 'PRESTASI';
        } else {
            $judul_jalur = $jalur_verif;
        }
    @endphp

    <style>
        @page { size: A4 portrait; margin: 0.8cm 1cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 9.5pt; line-height: 1.25; color: #000; }
        .page-break { page-break-after: always; clear: both; }
        .font-bold { font-weight: bold; }
        .font-black { font-weight: 900; }
        .uppercase { text-transform: uppercase; }
        .text-center { text-align: center; }
        
        table { width: 100%; border-collapse: collapse; border-spacing: 0; }
        .data-table td { padding: 1.5px 0; vertical-align: top; }
        .nested-table td { padding: 1px 0; vertical-align: top; }
        .table-borderless td { padding: 4px 0; vertical-align: top; }

        .main-title { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-top: 10px; margin-bottom: 10px; }
        .section-title { font-weight: bold; font-size: 10pt; text-transform: uppercase; background-color: #f8f8f8; padding: 2px 0; border-bottom: 1px solid #000; margin-top: 6px; margin-bottom: 3px; }

        .w-label { width: 18%; } .w-colon { width: 2%; text-align: center; } .w-value { width: 30%; text-transform: uppercase; font-weight: bold; }
        .c-label { width: 36%; } .c-colon { width: 3%; text-align: center; } .c-value { width: 61%; text-transform: uppercase; }
        .p-label1 { width: 22%; } .p-colon1 { width: 2%; text-align: center; } .p-value1 { width: 36%; text-transform: uppercase; font-weight: bold; }
        .p-label2 { width: 18%; } .p-colon2 { width: 2%; text-align: center; } .p-value2 { width: 20%; text-transform: uppercase; }

        .foto-box { width: 3cm; height: 4cm; border: 1px solid black; text-align: center; display: inline-block; vertical-align: middle; }
        .foto-text { display: inline-block; margin-top: 1.5cm; font-size: 8.5pt; line-height: 1.2; }
        
        .denah-box { border: 2px solid black; width: 100%; height: 800px; padding: 10px; box-sizing: border-box; position: relative; }
        .gps-radar-box { position: absolute; bottom: 15px; right: 15px; border: 2px dashed #444; padding: 8px; width: 270px; text-align: center; background-color: #fcfcfc; border-radius: 8px; }
        .gps-title { font-size: 9pt; font-weight: 900; letter-spacing: 1px; margin-bottom: 5px; border-bottom: 1px solid #ccc; padding-bottom: 3px; color: #111; }
        .gps-coord { font-family: monospace; font-size: 8.5pt; font-weight: bold; color: #333; margin-top: 6px; border-top: 1px dashed #ccc; padding-top: 4px; }
        .gps-info { font-size: 7.5pt; color: #666; font-style: italic; margin-top: 2px; }

        .pernyataan-list { padding-left: 20px; margin-top: 5px; margin-bottom: 5px; }
        .pernyataan-list li { margin-bottom: 3px; text-align: justify; }

        .label-container { border: 2px dashed #111; padding: 10px; text-align: center; margin-top: 10px; border-radius: 12px; }
        .label-title { font-size: 18pt; letter-spacing: 2px; margin-bottom: 0; font-weight: 900; }
        .label-subtitle { font-size: 11pt; color: #444; margin-bottom: 8px; font-weight: bold; }
        .label-jurusan { font-size: 28pt; margin: 8px 0; background: #eee; padding: 5px; border: 2px solid #111; display: inline-block; width: 70%; font-weight: 900; }
        .label-color-box { font-size: 11pt; border: 2px solid #111; padding: 8px; margin: 8px auto; width: 80%; font-weight: bold; text-transform: uppercase; }
        .qr-box { margin-top: 5px; }
        .gunting-box { text-align: center; margin: 15px 0 8px 0; border-top: 2px dotted #555; position: relative; }
        .gunting-text { background-color: #fff; padding: 0 15px; color: #555; font-size: 9.5pt; font-weight: bold; position: relative; top: -10px; }
        
        /* 🔥 THE CONDITIONAL SPACING ENGINE (HALAMAN 5) 🔥 */
        @if($judul_jalur == 'AFIRMASI')
            .verif-table th, .verif-table td { border: 1px solid #000; padding: 2.5px 3px; line-height: 1.1; font-size: 7.5pt; }
            .identitas-table { line-height: 1.15 !important; margin-bottom: 4px !important; font-size: 8.5pt !important; }
            .ttd-box { margin-top: 25px !important; font-size: 9.5pt !important; line-height: 1.3; }
            .ttd-cell { height: 55px !important; } 
            .ttd-kepsek { height: 65px !important; } 
            .ttd-gap { padding-top: 15px !important; }
            .verif-title { font-size: 9.5pt !important; margin-bottom: 4px !important; margin-top: 2px !important; line-height: 1 !important; }
            .kop-divider { margin-bottom: 25px !important; border-top: 2px solid black; border-bottom: 1px solid black; height: 1px; margin-top: 2px; }
        @else
            .verif-table th, .verif-table td { border: 1px solid #000; padding: 5px 6px; line-height: 1.35; font-size: 9pt; }
            .identitas-table { line-height: 1.4 !important; margin-bottom: 10px !important; font-size: 9.5pt !important; }
            .ttd-box { margin-top: 35px !important; font-size: 9.5pt !important; line-height: 1.3; }
            .ttd-cell { height: 60px !important; }
            .ttd-kepsek { height: 70px !important; }
            .ttd-gap { padding-top: 15px !important; }
            .verif-title { font-size: 10.5pt !important; margin-bottom: 8px !important; margin-top: 2px !important; line-height: 1.2 !important;}
            .kop-divider { margin-bottom: 25px !important; border-top: 2px solid black; border-bottom: 1px solid black; height: 1px; margin-top: 2px; }
        @endif
        
        .verif-table th { background-color: #f0f0f0; text-align: center; font-weight: bold; }
    </style>
</head>
<body>

    @php
        $jurusan = strtoupper($siswa_db->jurusan);
        $setting = \App\Models\Setting::first();
        $jadwalArray = $setting && $setting->jadwal_daftar_ulang ? json_decode($setting->jadwal_daftar_ulang, true) : [];
        $jadwalSiswa = $jadwalArray[$jurusan] ?? 'Menunggu Pengumuman Panitia';
        $warnaArray = $setting && $setting->warna_map ? json_decode($setting->warna_map, true) : [];
        $warnaMap = $warnaArray[$jurusan] ?? 'BELUM DISETTING'; 
        $qrCodeBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(95)->margin(0)->generate($siswa_db->nisn));
        $logoPath = public_path('images/logo-adb.jpg'); 
        $logoBase64 = file_exists($logoPath) ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath)) : '';
        $jatengPath = public_path('images/jateng.png');
        $jatengBase64 = file_exists($jatengPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($jatengPath)) : '';
        $lat = $siswa_db->latitude ?? null;
        $lng = $siswa_db->longitude ?? null;
        $qrRumahBase64 = null; $qrRuteBase64 = null;
        if ($lat && $lng) {
            $urlRumah = "https://www.google.com/maps?q={$lat},{$lng}";
            $qrRumahBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(95)->margin(0)->generate($urlRumah));
            $urlRute = "https://www.google.com/maps/dir/?api=1&origin={$lat},{$lng}&destination=SMK+Negeri+1+Adiwerna";
            $qrRuteBase64 = base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(95)->margin(0)->generate($urlRute));
        }
        $sp_nama = ''; $sp_pekerjaan = ''; $sp_alamat = ''; $sp_agama = ''; $sp_hubungan = '';
        if (!empty($ayah_nama) && strtolower(trim($ayah_nama)) !== '-' && strtolower(trim($ayah_nama)) !== 'tidak ada') {
            $sp_nama = $ayah_nama; $sp_pekerjaan = $ayah_pekerjaan ?? ''; $sp_alamat = $ayah_alamat ?? ''; $sp_agama = $ayah_agama ?? ''; $sp_hubungan = $ayah_hubungan ?? '';
        } elseif (!empty($ibu_nama) && strtolower(trim($ibu_nama)) !== '-' && strtolower(trim($ibu_nama)) !== 'tidak ada') {
            $sp_nama = $ibu_nama; $sp_pekerjaan = $ibu_pekerjaan ?? ''; $sp_alamat = $ibu_alamat ?? ''; $sp_agama = $ibu_agama ?? ''; $sp_hubungan = $ibu_hubungan ?? '';
        } else {
            $sp_nama = $wali_nama ?? ''; $sp_pekerjaan = $wali_pekerjaan ?? ''; $sp_alamat = $wali_alamat ?? ''; $sp_agama = $wali_agama ?? ''; $sp_hubungan = $wali_hubungan ?? '';
        }
        if (!function_exists('formatPenghasilan')) {
            function formatPenghasilan($val) {
                if (empty($val)) return '';
                if (is_numeric($val)) return 'Rp ' . number_format($val, 0, ',', '.') . ',-';
                return $val;
            }
        }
    @endphp

    <!-- HALAMAN 1 -->
    <div>
        <table style="width: 100%; font-family: Arial, Helvetica, sans-serif; text-align: center; margin-bottom: 2px;">
            <tr>
                <td style="width: 15%; vertical-align: middle; text-align: left; padding:0;"><img src="{{ $jatengBase64 }}" style="height: 85px; width: auto; margin-left: 5px;"></td>
                <td style="width: 70%; vertical-align: middle; line-height: 1.05; padding:0;">
                    <div style="font-size: 13pt;">PEMERINTAH PROVINSI JAWA TENGAH</div>
                    <div style="font-size: 15pt; font-weight: bold; margin-top: 3px;">DINAS PENDIDIKAN</div>
                    <div style="font-size: 16.5pt; font-weight: bold; margin-top: 3px;">SEKOLAH MENENGAH KEJURUAN</div>
                    <div style="font-size: 16.5pt; font-weight: bold;">NEGERI 1 ADIWERNA</div>
                    <div style="font-size: 8.5pt; margin-top: 5px; font-weight: normal;">Jalan Raya 2 PO BOX 2, Adiwerna, Kabupaten Tegal, Jawa Tengah, Kode Pos 52194</div>
                    <div style="font-size: 8.5pt; font-weight: normal; margin-top: 1px;">Telepon 0283-443768, Faksimile 0283-445494</div>
                    <div style="font-size: 8.5pt; font-weight: normal; margin-top: 1px;">Laman https://smkn1adw.sch.id, Pos-el mail@smkn1adw.sch.id</div>
                </td>
                <td style="width: 15%; vertical-align: middle; text-align: right; padding:0;"><img src="{{ $logoBase64 }}" style="height: 90px; width: auto; margin-right: 5px;"></td>
            </tr>
        </table>
        <div style="border-top: 3px solid black; border-bottom: 1px solid black; height: 1.5px; margin-top: 2px; margin-bottom: 25px;"></div>

        <div class="label-container">
            <div class="label-title">LABEL MAP DAFTAR ULANG</div>
            <div class="label-subtitle">TAHUN PELAJARAN 2026/2027</div>
            <table class="table-borderless" style="width: 85%; margin: 0 auto; text-align: left; font-size: 10.5pt; font-weight: bold;">
                <tr><td width="30%">NISN</td><td width="5%">:</td><td>{{ $siswa_db->nisn }}</td></tr>
                <tr><td>NAMA</td><td>:</td><td class="uppercase">{{ $siswa_db->nama }}</td></tr>
                <tr><td>ASAL SEKOLAH</td><td>:</td><td class="uppercase">{{ $siswa_db->asal_sekolah ?? 'BELUM DIINPUT' }}</td></tr>
                <tr><td>JALUR MASUK</td><td>:</td><td class="uppercase">{{ $siswa_db->jalur_pendaftaran }}</td></tr>
            </table>
            <div class="label-jurusan">{{ $jurusan }}</div>
            <div class="label-color-box">- GUNAKAN MAP KERTAS BERWARNA: <span style="font-size: 13pt; text-decoration: underline;">{{ $warnaMap }}</span> -</div>
            <div class="qr-box">
                <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" alt="QR Code NISN">
                <div style="font-size: 8pt; margin-top: 2px; font-weight:bold;">SCAN QR CODE OLEH PANITIA</div>
            </div>
        </div>

        <div class="gunting-box"><span class="gunting-text">[ GUNTING PADA GARIS PUTUS-PUTUS INI DAN TEMPELKAN DI MAP BERKAS DAFTAR ULANG ]</span></div>
        <div style="text-align: justify; font-size: 8.5pt; border: 1px solid #111; padding: 10px; border-radius: 8px;">
            <div style="font-size: 10pt; font-weight: bold; color: red; margin-bottom: 3px; text-decoration: underline;">PENGINGAT PENTING DAFTAR ULANG:</div>
            <table style="width: 100%; font-size: 9pt; font-weight: bold; margin-bottom: 3px;">
                <tr><td width="30%">Jadwal Kedatangan Anda</td><td width="2%">:</td><td style="color: blue;">{{ $jadwalSiswa }}</td></tr>
            </table>
            Pastikan Anda memasukkan <b>Dokumen Fisik (Asli/Fotokopi sesuai ketentuan)</b> berikut ke dalam Map <b>{{ $warnaMap }}</b> sebelum datang ke sekolah:<br>
            <ol style="margin-top: 3px; margin-bottom: 0; padding-left: 20px; line-height: 1.35;">
                <li>Asli Bukti Pendaftaran & Verifikasi Akun.</li>
                <li>Asli Surat Pernyataan Kebenaran Dokumen (Ditandatangani & Bermaterai).</li>
                <li>Asli Buku Rapor SMP/sederajat & Surat Keterangan Nilai Rapor Semester I-V (SKNR).</li>
                <li>Copy Ijazah / Asli Surat Keterangan Lulus (SKL).</li>
                <li>Copy Sertifikat Hasil TKA.</li>
                <li>Copy Akta Kelahiran / Surat Kelahiran.</li>
                <li>Copy Kartu Keluarga (KK).</li>
                <li>Asli Surat Pernyataan Sehat atau Surat Keterangan Sehat dari Dokter *(pilih salah satu)).</li>
                <li style="font-weight: bold; color: #000;">Dokumen Daftar Ulang Lainnya Asli (Biodata, Denah Rumah, Surat Pernyataan Murid, Kontrak Belajar & Lembar Verifikasi Daftar Ulang di Halaman 2, 3, 4, 5 PDF ini).</li>
                @if($judul_jalur == 'AFIRMASI')
                    <li style="color: #d97706; font-weight: bold;"><i>*ATS non database wajib melampirkan Surat Keterangan Kades/Lurah disahkan Camat (Asli).</i></li>
                    <li style="color: #d97706; font-weight: bold;"><i>*Asli Surat Keterangan Dokter/Hasil Asesmen (Khusus Jalur Afirmasi Disabilitas).</i></li>
                    <li style="color: #d97706;"><i>*Copy Piagam Prestasi/Penghargaan Akademik/Non-Akademik dan Asli Surat Keterangan Piagam (Khusus bagi yang memiliki).</i></li>
                @elseif($judul_jalur == 'PRESTASI')
                    <li style="color: #d97706; font-weight: bold;"><i>*Copy Piagam Prestasi/Penghargaan Akademik/Non-Akademik dan Asli Surat Keterangan Piagam (Khusus bagi yang memiliki).</i></li>
                @else
                    <li style="color: #d97706;"><i>*Copy Piagam Prestasi/Penghargaan Akademik/Non-Akademik dan Asli Surat Keterangan Piagam (Khusus bagi yang memiliki).</i></li>
                @endif
            </ol>
        </div>
    </div>

    <!-- HALAMAN 2 -->
    <div style="page-break-before: always; clear: both;">
        <table style="width: 100%; font-family: Arial, Helvetica, sans-serif; text-align: center; margin-bottom: 2px;">
            <tr>
                <td style="width: 15%; vertical-align: middle; text-align: left; padding:0;"><img src="{{ $jatengBase64 }}" style="height: 85px; width: auto; margin-left: 5px;"></td>
                <td style="width: 70%; vertical-align: middle; line-height: 1.05; padding:0;">
                    <div style="font-size: 13pt;">PEMERINTAH PROVINSI JAWA TENGAH</div>
                    <div style="font-size: 15pt; font-weight: bold; margin-top: 3px;">DINAS PENDIDIKAN</div>
                    <div style="font-size: 16.5pt; font-weight: bold; margin-top: 3px;">SEKOLAH MENENGAH KEJURUAN</div>
                    <div style="font-size: 16.5pt; font-weight: bold;">NEGERI 1 ADIWERNA</div>
                    <div style="font-size: 8.5pt; margin-top: 5px; font-weight: normal;">Jalan Raya 2 PO BOX 2, Adiwerna, Kabupaten Tegal, Jawa Tengah, Kode Pos 52194</div>
                    <div style="font-size: 8.5pt; font-weight: normal; margin-top: 1px;">Telepon 0283-443768, Faksimile 0283-445494</div>
                    <div style="font-size: 8.5pt; font-weight: normal; margin-top: 1px;">Laman https://smkn1adw.sch.id, Pos-el mail@smkn1adw.sch.id</div>
                </td>
                <td style="width: 15%; vertical-align: middle; text-align: right; padding:0;"><img src="{{ $logoBase64 }}" style="height: 90px; width: auto; margin-right: 5px;"></td>
            </tr>
        </table>
        <div style="border-top: 3px solid black; border-bottom: 1px solid black; height: 1.5px; margin-top: 2px; margin-bottom: 0;"></div>
        <div class="main-title">BIODATA DIRI PESERTA DIDIK</div>
        <table class="data-table" style="margin-bottom: 5px;">
            <tr><td class="w-label">Nama</td><td class="w-colon">:</td><td class="w-value">{{ $siswa_db->nama }}</td><td class="w-label">NIK</td><td class="w-colon">:</td><td class="w-value" style="font-weight:normal; text-transform:none;">{{ $nik }}</td></tr>
            <tr><td class="w-label">Program Keahlian</td><td class="w-colon">:</td><td class="w-value">{{ $siswa_db->jurusan }}</td><td class="w-label">NISN</td><td class="w-colon">:</td><td class="w-value" style="font-weight:normal; text-transform:none;">{{ $siswa_db->nisn }}</td></tr>
        </table>
        <table style="width: 100%;">
            <tr>
                <td style="width: 48%; vertical-align: top; padding-right: 10px;">
                    <div class="section-title">I. IDENTITAS PESERTA DIDIK</div>
                    <table class="data-table">
                        <tr><td class="c-label">No. Pendaftaran</td><td class="c-colon">:</td><td class="c-value font-bold">{{ $no_pendaftaran }}</td></tr>
                        <tr><td class="c-label">Nama Panggilan</td><td class="c-colon">:</td><td class="c-value">{{ $nama_panggilan }}</td></tr>
                        <tr><td class="c-label">TTL</td><td class="c-colon">:</td><td class="c-value">{{ $ttl }}</td></tr>
                        <tr><td class="c-label">Jenis Kelamin</td><td class="c-colon">:</td><td class="c-value">{{ $jenis_kelamin }}</td></tr>
                        <tr><td class="c-label">Agama</td><td class="c-colon">:</td><td class="c-value">{{ $agama }}</td></tr>
                        <tr><td class="c-label">Alamat</td><td class="c-colon">:</td><td class="c-value">{{ $alamat }}</td></tr>
                        <tr><td class="c-label">No HP/WA</td><td class="c-colon">:</td><td class="c-value" style="text-transform:none;">{{ $no_hp }}</td></tr>
                        <tr><td class="c-label">Riwayat Kesehatan</td><td class="c-colon">:</td><td class="c-value">{{ $riwayat_kesehatan }}</td></tr>
                        <tr><td class="c-label">Prestasi (jika ada)</td><td class="c-colon">:</td><td class="c-value">{{ $prestasi }}</td></tr>
                    </table>
                </td>
                <td style="width: 4%;"></td>
                <td style="width: 48%; vertical-align: top;">
                    <div class="section-title">II. KEADAAN KELUARGA / TEMAN</div>
                    <table class="data-table">
                        <tr><td class="c-label" style="width:40%;">PD tinggal dengan</td><td class="c-colon">:</td><td class="c-value" style="width:57%;">{{ $tinggal_dengan }}</td></tr>
                        <tr><td class="c-label" style="width:40%;">Jumlah saudara</td><td class="c-colon">:</td><td class="c-value" style="width:57%; text-transform:none;">{{ str_pad($jumlah_saudara, 2, '0', STR_PAD_LEFT) }}</td></tr>
                    </table>
                    @if(isset($saudara_nama) && is_array($saudara_nama) && count($saudara_nama) > 0)
                    <table class="nested-table" style="width: 100%; margin-top: 4px; margin-bottom: 4px;">
                        @foreach($saudara_nama as $index => $nama_sdr)
                        <tr><td style="width: 50%;" class="uppercase">• {{ $nama_sdr }}</td><td style="width: 18%;">Umur</td><td style="width: 10%;">{{ isset($saudara_umur[$index]) && $saudara_umur[$index] != '' ? $saudara_umur[$index] . ' th' : '-' }}</td><td style="width: 22%; text-align: right;" class="uppercase">({{ $saudara_status[$index] ?? '-' }})</td></tr>
                        @endforeach
                    </table>
                    @endif
                    <table class="data-table">
                        <tr><td class="c-label" style="width:40%;">Hobi</td><td class="c-colon">:</td><td class="c-value" style="width:57%;">{{ $hobi }}</td></tr>
                        <tr><td class="c-label" style="width:40%;">Cita-cita</td><td class="c-colon">:</td><td class="c-value" style="width:57%;">{{ $cita_cita }}</td></tr>
                        <tr><td colspan="3" style="padding-top: 8px;">Teman dekat yang dikenal di SMKN 1 Adiwerna</td></tr>
                        <tr><td colspan="3" style="padding-left: 10px;"><table class="nested-table" style="width: 100%;"><tr><td style="width: 60%;" class="uppercase">• {{ $teman_nama ?? '-' }}</td><td style="width: 15%;">HP/WA:</td><td style="width: 25%; text-transform:none;">{{ $teman_hp ?? '-' }}</td></tr></table></td></tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="section-title">III. IDENTITAS ORANG TUA / WALI</div>
        <table class="data-table">
            <tr><td class="p-label1">1. Ayah</td><td class="p-colon1">:</td><td class="p-value1">{{ $ayah_nama }}</td><td class="p-label2">Umur</td><td class="p-colon2">:</td><td class="p-value2">{{ $ayah_umur != '' ? $ayah_umur . ' th' : '' }}</td></tr>
            <tr><td class="p-label1" style="padding-left:15px; font-weight:normal;">Hubungan Keluarga</td><td class="p-colon1">:</td><td class="p-value1" style="font-weight:normal;">{{ $ayah_hubungan }}</td><td class="p-label2">Agama</td><td class="p-colon2">:</td><td class="p-value2">{{ $ayah_agama }}</td></tr>
            <tr><td class="p-label1" style="padding-left:15px; font-weight:normal;">Penghasilan</td><td class="p-colon1">:</td><td class="p-value1" style="font-weight:normal; text-transform:none;">{{ formatPenghasilan($ayah_penghasilan) }}</td><td class="p-label2">Pendidikan terakhir</td><td class="p-colon2">:</td><td class="p-value2">{{ $ayah_pendidikan }}</td></tr>
            <tr><td class="p-label1" style="padding-left:15px; font-weight:normal;">Alamat</td><td class="p-colon1">:</td><td class="p-value1" rowspan="2" style="font-weight:normal;">{{ $ayah_alamat }}</td><td class="p-label2">Pekerjaan</td><td class="p-colon2">:</td><td class="p-value2">{{ $ayah_pekerjaan }}</td></tr>
            <tr><td class="p-label1"></td><td class="p-colon1"></td><td class="p-label2">No. HP/WA</td><td class="p-colon2">:</td><td class="p-value2" style="text-transform:none;">{{ $ayah_hp }}</td></tr>
        </table>
        <table class="data-table" style="margin-top: 5px;">
            <tr><td class="p-label1">2. Ibu</td><td class="p-colon1">:</td><td class="p-value1">{{ $ibu_nama }}</td><td class="p-label2">Umur</td><td class="p-colon2">:</td><td class="p-value2">{{ $ibu_umur != '' ? $ibu_umur . ' th' : '' }}</td></tr>
            <tr><td class="p-label1" style="padding-left:15px; font-weight:normal;">Hubungan Keluarga</td><td class="p-colon1">:</td><td class="p-value1" style="font-weight:normal;">{{ $ibu_hubungan }}</td><td class="p-label2">Agama</td><td class="p-colon2">:</td><td class="p-value2">{{ $ibu_agama }}</td></tr>
            <tr><td class="p-label1" style="padding-left:15px; font-weight:normal;">Penghasilan</td><td class="p-colon1">:</td><td class="p-value1" style="font-weight:normal; text-transform:none;">{{ formatPenghasilan($ibu_penghasilan) }}</td><td class="p-label2">Pendidikan terakhir</td><td class="p-colon2">:</td><td class="p-value2">{{ $ibu_pendidikan }}</td></tr>
            <tr><td class="p-label1" style="padding-left:15px; font-weight:normal;">Alamat</td><td class="p-colon1">:</td><td class="p-value1" rowspan="2" style="font-weight:normal;">{{ $ibu_alamat }}</td><td class="p-label2">Pekerjaan</td><td class="p-colon2">:</td><td class="p-value2">{{ $ibu_pekerjaan }}</td></tr>
            <tr><td class="p-label1"></td><td class="p-colon1"></td><td class="p-label2">No. HP/WA</td><td class="p-colon2">:</td><td class="p-value2" style="text-transform:none;">{{ $ibu_hp }}</td></tr>
        </table>
        <table class="data-table" style="margin-top: 5px;">
            <tr><td class="p-label1">3. Wali (jika Ada)</td><td class="p-colon1">:</td><td class="p-value1">{{ $wali_nama ?? '' }}</td><td class="p-label2">Umur</td><td class="p-colon2">:</td><td class="p-value2">{{ !empty($wali_umur) ? $wali_umur . ' th' : '' }}</td></tr>
            <tr><td class="p-label1" style="padding-left:15px; font-weight:normal;">Hubungan Keluarga</td><td class="p-colon1">:</td><td class="p-value1" style="font-weight:normal;">{{ $wali_hubungan ?? '' }}</td><td class="p-label2">Agama</td><td class="p-colon2">:</td><td class="p-value2">{{ $wali_agama ?? '' }}</td></tr>
            <tr><td class="p-label1" style="padding-left:15px; font-weight:normal;">Penghasilan</td><td class="p-colon1">:</td><td class="p-value1" style="font-weight:normal; text-transform:none;">{{ !empty($wali_penghasilan) ? formatPenghasilan($wali_penghasilan) : '' }}</td><td class="p-label2">Pendidikan terakhir</td><td class="p-colon2">:</td><td class="p-value2">{{ $wali_pendidikan ?? '' }}</td></tr>
            <tr><td class="p-label1" style="padding-left:15px; font-weight:normal;">Alamat</td><td class="p-colon1">:</td><td class="p-value1" rowspan="2" style="font-weight:normal;">{{ $wali_alamat ?? '' }}</td><td class="p-label2">Pekerjaan</td><td class="p-colon2">:</td><td class="p-value2">{{ $wali_pekerjaan ?? '' }}</td></tr>
            <tr><td class="p-label1"></td><td class="p-colon1"></td><td class="p-label2">No. HP/WA</td><td class="p-colon2">:</td><td class="p-value2" style="text-transform:none;">{{ $wali_hp ?? '' }}</td></tr>
        </table>
        <table style="width: 100%; margin-top: 15px;">
            <tr>
                <td style="width: 55%; vertical-align: top;">
                    <div class="section-title" style="margin-top:0;">IV. RIWAYAT PENDIDIKAN</div>
                    <table class="data-table">
                        <tr><td class="c-label" style="width:40%;">TK/PAUD</td><td class="c-colon">:</td><td class="c-value" style="width:57%;">{{ $sekolah_tk ?? '-' }}</td></tr>
                        <tr><td class="c-label" style="width:40%;">SD/MI</td><td class="c-colon">:</td><td class="c-value" style="width:57%;">{{ $sekolah_sd }}</td></tr>
                        <tr><td class="c-label" style="width:40%;">SMP/MTS</td><td class="c-colon">:</td><td class="c-value font-bold" style="width:57%;">{{ $sekolah_smp }}</td></tr>
                        <tr><td colspan="3" style="padding-top: 8px;">Pernah tinggal kelas/ tidak lulus</td></tr>
                        <tr><td class="c-label" style="padding-left: 15px; width:40%;">SD, di Kelas</td><td class="c-colon">:</td><td class="c-value" style="width:57%;">{{ $tinggal_kelas_sd }}</td></tr>
                        <tr><td class="c-label" style="padding-left: 15px; width:40%;">SMP/MTS, di kelas</td><td class="c-colon">:</td><td class="c-value" style="width:57%;">{{ $tinggal_kelas_smp }}</td></tr>
                        <tr><td colspan="3" style="padding-top: 8px;">Pernah Mutasi / Pindah Sekolah</td></tr>
                        <tr><td class="c-label" style="padding-left: 15px; width:40%;">Tahun</td><td class="c-colon">:</td><td class="c-value" style="width:57%;">{{ $mutasi_tahun }}</td></tr>
                        <tr><td class="c-label" style="padding-left: 15px; width:40%;">Dari Sekolah</td><td class="c-colon">:</td><td class="c-value" style="width:57%;">{{ $mutasi_dari }}</td></tr>
                    </table>
                </td>
                <td style="width: 2%;"></td>
                <td style="width: 43%; vertical-align: bottom; padding-bottom: 5px;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr><td colspan="2" style="text-align: left; padding-bottom: 8px; padding-left: 5px;">Adiwerna, {{ $tanggal_cetak }}</td></tr>
                        <tr>
                            <td style="width: 40%; text-align: left; vertical-align: bottom;">
                                <div class="foto-box" style="padding: 0; overflow: hidden; position: relative;">
                                    @if(!empty($pas_foto))<img src="{{ $pas_foto }}" style="width: 3cm; height: 4cm; display: block;" alt="Pas Foto">@else<div class="foto-text">Foto 3x4<br>berwarna</div>@endif
                                </div>
                            </td>
                            <td style="width: 60%; text-align: center; vertical-align: bottom;">Peserta didik<br><br><br><br><br><br>( <span class="font-bold uppercase">{{ $siswa_db->nama }}</span> )</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- HALAMAN 3 -->
    <div style="page-break-before: always; clear: both;">
        <table style="width: 100%; font-weight: bold; font-size: 10.5pt; margin-bottom: 25px; line-height: 1.5;">
            <tr>
                <td style="width: 16%; padding-bottom: 8px;">Nama Siswa</td>
                <td style="width: 2%; padding-bottom: 8px;">:</td>
                <td style="width: 32%; border-bottom: 1px dotted #000; padding-bottom: 8px;" class="uppercase">{{ $siswa_db->nama }}</td>
                <td style="width: 50%; text-align: left; padding-left: 20px; vertical-align: top;" rowspan="3">Alamat : <span style="font-weight:normal;" class="uppercase">{{ $alamat }}</span></td>
            </tr>
            <tr><td style="padding-bottom: 8px;">Nama Orangtua</td><td style="padding-bottom: 8px;">:</td><td style="border-bottom: 1px dotted #000; padding-bottom: 8px;" class="uppercase">{{ $sp_nama !== '' ? $sp_nama : '-' }}</td></tr>
            <tr><td style="padding-bottom: 8px;">Kelas / Jurusan</td><td style="padding-bottom: 8px;">:</td><td style="border-bottom: 1px dotted #000; padding-bottom: 8px;" class="uppercase">X / {{ $siswa_db->jurusan }}</td></tr>
        </table>
        <div class="denah-box">
            <div style="font-weight: bold; font-size: 14pt; margin-bottom: 20px; text-align: center; text-decoration: underline;">GAMBAR DENAH RUMAH SAMPAI SMKN 1 ADIWERNA</div>
            <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
            <div style="text-align: center; color: #888; font-style: italic;">(Gambarkan secara manual rute dari rumah menuju SMK Negeri 1 Adiwerna pada area kosong ini)</div>
            <div class="gps-radar-box">
                <div class="gps-title">SMART LOCATION RADAR</div>
                @if($qrRumahBase64 && $qrRuteBase64)
                    <table style="width: 100%; border: none; margin-top: 3px;">
                        <tr>
                            <td style="text-align: center; width: 50%; border: none; padding: 2px;"><img src="data:image/svg+xml;base64,{{ $qrRumahBase64 }}" alt="QR Rumah"><div style="font-size: 8pt; font-weight: bold; margin-top: 3px; color: #d97706;">SCAN RUMAH</div><div style="font-size: 6.5pt; color: #555;">(Street View)</div></td>
                            <td style="text-align: center; width: 50%; border: none; padding: 2px;"><img src="data:image/svg+xml;base64,{{ $qrRuteBase64 }}" alt="QR Rute"><div style="font-size: 8pt; font-weight: bold; margin-top: 3px; color: #0284c7;">SCAN RUTE</div><div style="font-size: 6.5pt; color: #555;">(Jarak Tempuh)</div></td>
                        </tr>
                    </table>
                    <div class="gps-coord">LAT: {{ substr($lat, 0, 8) }} | LNG: {{ substr($lng, 0, 8) }}</div>
                @else
                    <div style="border: 1px solid #ccc; width: 100px; height: 100px; margin: 5px auto; padding-top: 35px; box-sizing: border-box; font-size: 8pt; color:#999; background:#eee;">No GPS Data</div>
                    <div class="gps-coord" style="color: #d97706;">KOORDINAT MANUAL</div>
                    <div class="gps-info">Siswa tidak mengaktifkan Lokasi</div>
                @endif
            </div>
        </div>
    </div>

    <!-- HALAMAN 4 -->
    <div style="page-break-before: always; clear: both;">
        <div style="font-size: 9.5pt; line-height: 1.3;">
            <div class="main-title" style="margin-bottom: 5px; margin-top: 15px; font-size: 12pt;">SURAT PERNYATAAN CALON PESERTA DIDIK</div>
            <div style="text-align: center; font-weight: bold; text-transform: uppercase; margin-bottom: 20px; font-size: 11.5pt;">SMK NEGERI 1 ADIWERNA</div>
            <div style="text-justify" style="margin-bottom: 8px;">Saya yang bertanda tangan di bawah ini :</div>
            <table style="margin-left: 10px; font-size: 9.5pt; line-height: 1.3;">
                <tr><td class="w-label" style="padding-bottom: 3px; width:26%;">Nama Lengkap</td><td class="w-colon" style="width:2%;">:</td><td class="w-value uppercase font-bold" style="width:72%;">{{ $siswa_db->nama }}</td></tr>
                <tr><td style="padding-bottom: 3px;">Tempat / tanggal lahir</td><td>:</td><td class="uppercase">{{ $ttl }}</td></tr>
                <tr><td style="padding-bottom: 3px;">Jenis kelamin</td><td>:</td><td class="uppercase">{{ $jenis_kelamin }}</td></tr>
                <tr><td style="padding-bottom: 3px;">Agama</td><td>:</td><td class="uppercase">{{ $agama }}</td></tr>
                <tr><td style="padding-bottom: 3px;">Nomor pendaftaran</td><td>:</td><td class="uppercase font-bold">{{ $no_pendaftaran }}</td></tr>
                <tr><td style="padding-bottom: 3px;">Kompetensi keahlian</td><td>:</td><td class="uppercase font-bold">{{ $siswa_db->jurusan }}</td></tr>
                <tr><td style="padding-bottom: 3px;">Nama orang tua / wali</td><td>:</td><td class="uppercase">{{ $sp_nama }}</td></tr>
                <tr><td style="padding-bottom: 3px;">Pekerjaan orang tua / wali</td><td>:</td><td class="uppercase">{{ $sp_pekerjaan }}</td></tr>
                <tr><td style="padding-bottom: 3px;">Alamat orang tua / wali</td><td>:</td><td class="uppercase">{{ $sp_alamat }}</td></tr>
                <tr><td style="padding-bottom: 3px;">Agama orang tua / wali</td><td>:</td><td class="uppercase">{{ $sp_agama }}</td></tr>
                <tr><td style="padding-bottom: 3px;">Hubungan keluarga dengan siswa</td><td>:</td><td class="uppercase">{{ $sp_hubungan }}</td></tr>
            </table>
            <div class="font-bold" style="margin-top: 15px; margin-bottom: 5px;">MENYATAKAN bahwa selama menjadi siswa SMK Negeri 1 Adiwerna Tegal, saya sanggup :</div>
            <ol class="pernyataan-list" style="font-size: 9.5pt; line-height: 1.3;">
                <li>Melaksanakan ibadah sesuai ketentuan agama.</li>
                <li>Belajar dengan tekun dan penuh semangat.</li>
                <li>Menjaga nama baik diri sendiri, keluarga dan sekolah.</li>
                <li>Mentaati dan mematuhi pelaksanaan wiyatamandala.</li>
                <li>Mengikuti pelajaran Agama yang diakui oleh Negara Kesatuan Republik Indonesia.</li>
                <li>Tidak akan terlibat, membawa/ menyimpan, mengedarkan serta memakai barang terlarang termasuk Narkoba dan Pornografi.</li>
                <li>Mengikuti kegiatan intra dan ekstra kurikuler yang ditetapkan.</li>
                <li>Mematuhi tata tertib siswa yang berlaku.</li>
                <li>Mengikuti daftar ulang khusus, jika tidak naik kelas. Apabila tidak mendaftar ulang sampai pada batas waktu yang ditentukan dianggap mengundurkan diri dari SMK Negeri 1 Adiwerna.</li>
                <li>Tidak Alpha maksimal 9 kali (termasuk berbohong ijin/ sakit, dihitung Alpha).</li>
                <li>Tidak meminta dengan paksaan, mengambil barang milik orang lain, milik sekolah (mencuri).</li>
                <li>Tidak berkelahi/ tawuran di dalam maupun di luar sekolah.</li>
                <li>Tidak hamil. Tidak Menikah. Tidak punya anak selama masih menjadi siswa SMK Negeri 1 Adiwerna Tegal.</li>
                <li>Menarik diri atau keluar dari SMK Negeri 1 Adiwerna, apabila 2 (dua) kali berturut-turut tidak naik kelas/ tingkat.</li>
                <li>Sebelum atau sesudah menerima pengumuman kelulusan tidak akan melakukan corat-coret, semprotan cat pada anggota tubuh, pakaian seragam sekolah, fasilitas umum/pribadi dan melakukan konvoi di jalan.</li>
            </ol>
            <div class="font-bold mt-2" style="margin-top: 10px; margin-bottom: 5px;">Apabila tidak mentaati ketentuan di atas, maka saya sanggup :</div>
            <ol class="pernyataan-list" style="list-style-type: lower-alpha; font-size: 9.5pt; line-height: 1.3;">
                <li>Menerima sanksi atau tidak mengikuti pelajaran dalam batas waktu tertentu.</li>
                <li>Khusus poin 10 tidak dapat melanjutkan ke jenjang (tingkat) berikutnya.</li>
                <li>Khusus poin 11, 12, 13 dikembalikan ke Orang Tua/ Wali (dikeluarkan).</li>
                <li>Khusus poin 15, ijasah dan dokumen lain ditahan sampai tahun ajaran kelas XII selesai (bulan Juli).</li>
                <li>Mengambil ijasah dan dokumen kelulusan lainnya maksimal 2 tahun tertanggal kelulusan. Jika tidak diambil dalam masa tersebut, segala resiko hilang/ rusak bukan merupakan tanggungjawab sekolah.</li>
            </ol>
            <div class="mt-2 mb-4" style="margin-top: 15px; margin-bottom: 25px;">Demikian pernyataan ini saya buat dengan sebenar-benarnya dan penuh tanggung jawab serta diketahui orang tua/wali.</div>
            <table style="width: 100%; margin-top: 35px; font-size: 9.5pt;">
                <tr>
                    <td style="width: 50%; text-align: center; vertical-align: bottom;">Mengetahui,<br>Orang tua / wali<br><br><br><br><br><br>( <span style="text-transform: uppercase;">{{ $sp_nama }}</span> )</td>
                    <td style="width: 50%; text-align: center; vertical-align: top;">Adiwerna, {{ $tanggal_cetak }}<br>Yang membuat pernyataan<br><br><div style="border: 1px solid black; width: 75px; height: 40px; margin: 0 auto; line-height: 1.1; font-size: 8pt; padding-top: 10px; box-sizing: border-box;">MATERAI<br>10.000</div><br>( <span style="text-transform: uppercase; font-weight: bold;">{{ $siswa_db->nama }}</span> )</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- HALAMAN 5 -->
    <div style="page-break-before: always; clear: both;">
        <table style="width: 100%; font-family: Arial, Helvetica, sans-serif; text-align: center; margin-bottom: 2px;">
            <tr>
                <td style="width: 15%; vertical-align: middle; text-align: left; padding:0;"><img src="{{ $jatengBase64 }}" style="height: 85px; width: auto; margin-left: 5px;"></td>
                <td style="width: 70%; vertical-align: middle; line-height: 1.05; padding:0;">
                    <div style="font-size: 13pt;">PEMERINTAH PROVINSI JAWA TENGAH</div>
                    <div style="font-size: 15pt; font-weight: bold; margin-top: 3px;">DINAS PENDIDIKAN</div>
                    <div style="font-size: 16.5pt; font-weight: bold; margin-top: 3px;">SEKOLAH MENENGAH KEJURUAN</div>
                    <div style="font-size: 16.5pt; font-weight: bold;">NEGERI 1 ADIWERNA</div>
                    <div style="font-size: 8.5pt; margin-top: 5px; font-weight: normal;">Jalan Raya 2 PO BOX 2, Adiwerna, Kabupaten Tegal, Jawa Tengah, Kode Pos 52194</div>
                    <div style="font-size: 8.5pt; font-weight: normal; margin-top: 1px;">Telepon 0283-443768, Faksimile 0283-445494</div>
                    <div style="font-size: 8.5pt; font-weight: normal; margin-top: 1px;">Laman https://smkn1adw.sch.id, Pos-el mail@smkn1adw.sch.id</div>
                </td>
                <td style="width: 15%; vertical-align: middle; text-align: right; padding:0;"><img src="{{ $logoBase64 }}" style="height: 90px; width: auto; margin-right: 5px;"></td>
            </tr>
        </table>
        <div class="kop-divider"></div>

        <div class="verif-title" style="text-align: center; font-weight: bold;">
            LEMBAR VERIFIKASI DOKUMEN PERSYARATAN SPMB SMA NEGERI, SMK NEGERI, SMA SWASTA<br>
            DAN SMK SWASTA PELAKSANA PROGRAM KEMITRAAN PROVINSI JAWA TENGAH<br>
            TAHUN AJARAN 2026/2027
        </div>
        
        <div style="text-align: center; font-weight: bold; margin-bottom: 5px; font-size: 10.5pt;">
            @if($judul_jalur == 'PRESTASI') "SELEKSI PRESTASI SMK" @elseif($judul_jalur == 'AFIRMASI') "SELEKSI AFIRMASI SMK" @else "JALUR DOMISILI TERDEKAT SMK" @endif
        </div>

        <div style="font-weight: bold; margin-bottom: 3px; font-size: 9.5pt;">A. IDENTITAS CALON MURID BARU</div>
        <table class="identitas-table" style="width: 100%;">
            <tr><td style="width: 25%;">1. Nomor pendaftaran</td><td style="width: 2%;">:</td><td style="width: 73%;" colspan="3">{{ $no_pendaftaran }}</td></tr>
            <tr><td>2. Nama</td><td>:</td><td style="text-transform: uppercase; font-weight: bold;" colspan="3">{{ $siswa_db->nama }}</td></tr>
            <tr><td>3. Tempat/tgl.lahir</td><td>:</td><td style="width: 35%;">{{ $ttl }}</td><td style="width: 18%;">Nomor Telepon/HP</td><td style="width: 20%;">: {{ $no_hp }}</td></tr>
            <tr><td style="vertical-align: top;">4. Alamat</td><td style="vertical-align: top;">:</td><td style="vertical-align: top; text-transform: uppercase;" colspan="3">{{ $alamat }}</td></tr>
            <tr><td>5. Asal sekolah</td><td>:</td><td style="text-transform: uppercase;" colspan="3">{{ $siswa_db->asal_sekolah }}</td></tr>
        </table>

        <div style="font-weight: bold; margin-bottom: 3px; font-size: 9.5pt;">B. DOKUMEN TERVERIFIKASI</div>
        <table class="verif-table" style="width: 100%; border-collapse: collapse; text-align: center;">
            <thead>
                <tr><th rowspan="2" style="width: 5%;">NO</th><th rowspan="2" style="width: 53%;">DOKUMEN PERSYARATAN</th><th colspan="2" style="width: 18%;">HASIL VERIFIKASI</th><th rowspan="2" style="width: 24%;">KETERANGAN</th></tr>
                <tr><th style="width: 9%;">SESUAI</th><th style="width: 9%;">TIDAK<br>SESUAI</th></tr>
            </thead>
            <tbody style="text-align: left; vertical-align: middle;">
                @php
                    $list_dokumen = [];
                    if ($judul_jalur == 'PRESTASI') {
                        $list_dokumen = [['dok' => 'Asli print out bukti pendaftaran.', 'ket' => ''], ['dok' => 'Asli Surat Pernyataan Kebenaran Dokumen.', 'ket' => ''], ['dok' => 'Asli Surat Keterangan Sehat.', 'ket' => ''], ['dok' => 'Fotokopi Buku Rapor SMP/sederajat.', 'ket' => ''], ['dok' => 'Fotokopi Surat Keterangan Nilai Rapor Semester I s.d. V.', 'ket' => ''], ['dok' => 'Fotokopi Ijazah SMP/sederajat atau SKL.', 'ket' => ''], ['dok' => 'Fotokopi Sertifikat Hasil Tes Kemampuan Akademik (SHTKA).', 'ket' => ''], ['dok' => 'Fotokopi Akta kelahiran/Surat Kelahiran.', 'ket' => ''], ['dok' => 'Fotokopi Kartu Keluarga (KK).', 'ket' => ''], ['dok' => 'Fotokopi Piagam Prestasi/Penghargaan.', 'ket' => '']];
                    } elseif ($judul_jalur == 'AFIRMASI') {
                        $list_dokumen = [['dok' => 'Asli print out bukti pendaftaran.', 'ket' => ''], ['dok' => 'Asli Surat Pernyataan Kebenaran Dokumen.', 'ket' => ''], ['dok' => 'Asli Surat Keterangan Sehat.', 'ket' => ''], ['dok' => 'Asli rekomendasi Kepala Cabang Dinas Pendidikan berdasarkan hasil asesmen dari Tim Asesmen Disabilitas.', 'ket' => 'Khusus Disabilitas'], ['dok' => 'Fotokopi Print out bukti pendaftaran.', 'ket' => ''], ['dok' => 'Fotokopi Surat Pernyataan Kebenaran Dokumen.', 'ket' => ''], ['dok' => 'Fotokopi Buku Rapor SMP/sederajat.', 'ket' => ''], ['dok' => 'Fotokopi Surat Keterangan Nilai Rapor Semester I s.d. V.', 'ket' => ''], ['dok' => 'Fotokopi Ijazah SMP/sederajat atau SKL.', 'ket' => ''], ['dok' => 'Fotokopi Sertifikat Hasil Tes Kemampuan Akademik (SHTKA).', 'ket' => ''], ['dok' => 'Fotokopi Akta kelahiran.', 'ket' => ''], ['dok' => 'Fotokopi Kartu Keluarga (KK).', 'ket' => ''], ['dok' => 'Fotokopi Piagam Prestasi/Penghargaan.', 'ket' => '']];
                    } else {
                        $list_dokumen = [['dok' => 'Asli print out bukti pendaftaran.', 'ket' => ''], ['dok' => 'Asli Surat Pernyataan Kebenaran Dokumen.', 'ket' => ''], ['dok' => 'Asli Surat Keterangan Sehat.', 'ket' => ''], ['dok' => 'Fotokopi Buku Rapor SMP/sederajat.', 'ket' => ''], ['dok' => 'Fotokopi Surat Keterangan Nilai Rapor Semester I s.d. V.', 'ket' => ''], ['dok' => 'Fotokopi Ijazah SMP/sederajat atau SKL.', 'ket' => ''], ['dok' => 'Fotokopi Sertifikat Hasil Tes Kemampuan Akademik (SHTKA).', 'ket' => ''], ['dok' => 'Fotokopi Akta kelahiran.', 'ket' => ''], ['dok' => 'Fotokopi Kartu Keluarga (KK).', 'ket' => ''], ['dok' => 'Fotokopi Piagam Prestasi/Penghargaan.', 'ket' => '']];
                    }
                @endphp
                @foreach($list_dokumen as $idx => $item)
                <tr><td style="text-align: center;">{{ $idx + 1 }}.</td><td style="text-align: justify; padding-right: 5px;">{{ $item['dok'] }}</td><td></td><td></td><td style="text-align: center; font-style: italic;">{{ $item['ket'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
        <div style="font-size: 8.5pt; text-align: justify; margin-top: 5px;">Demikian dokumen daftar ulang telah dilakukan penelitian dan klarifikasi bersama oleh Tim Verifikator bersama Orang Tua/Wali/Calon Murid yang bersangkutan.</div>
        <table class="ttd-box" style="width: 100%; text-align: center;">
            <tr><td style="width: 33%;"></td><td style="width: 33%;"></td><td style="width: 34%; font-weight: bold;">....................., ..... Juni 2026</td></tr>
            <tr><td style="padding-top: 2px; font-weight: bold;">Orang tua/wali/<br>calon murid baru</td><td style="padding-top: 2px; vertical-align: bottom; font-weight: bold;">Verifikator I</td><td style="padding-top: 2px; vertical-align: bottom; font-weight: bold;">Verifikator II</td></tr>
            <tr><td class="ttd-cell" style="vertical-align: bottom;">.........................................</td><td class="ttd-cell" style="vertical-align: bottom;">.........................................<br>NIP. ........................................</td><td class="ttd-cell" style="vertical-align: bottom;">.........................................<br>NIP. ........................................</td></tr>
            <tr><td colspan="3" class="ttd-gap" style="font-weight: bold;">Mengetahui<br>Kepala SMK Negeri 1 Adiwerna</td></tr>
            <tr><td colspan="3" class="ttd-kepsek" style="vertical-align: bottom; font-weight: bold;"><span style="text-decoration: underline;">Joko Pramono, S.Pd., M.Ds.</span><br><span style="font-weight: normal;">NIP. 19690317 199802 1 004</span></td></tr>
        </table>
    </div>
</body>
</html>