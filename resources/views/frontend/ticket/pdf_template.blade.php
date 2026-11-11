<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tiket Pelayanan SPMB - {{ $ticket->nama_cpd }}</title>
    <style>
        /* CSS Khusus DomPDF - Strict 1 Halaman */
        @page {
            margin: 25px 40px; 
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11.5px; 
            color: #111827;
            line-height: 1.4;
        }
        
        /* ─── KOP SURAT ─── */
        .kop-surat {
            width: 100%;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .kop-surat td { vertical-align: middle; }
        .instansi-name {
            font-size: 22px; font-weight: 900; color: #1e3a8a;
            text-transform: uppercase; margin: 0; letter-spacing: 1px;
        }
        .instansi-desc { font-size: 12px; color: #4b5563; margin: 2px 0; }
        .instansi-address { font-size: 10px; color: #6b7280; margin: 0; }

        /* ─── HIGHLIGHT ANTRIAN ─── */
        .highlight-box {
            width: 100%;
            border: 2px dashed #3b82f6; background-color: #eff6ff;
            margin-bottom: 12px; border-radius: 8px; border-collapse: collapse;
        }
        .highlight-box td { padding: 10px; text-align: center; vertical-align: middle; }
        .highlight-title {
            font-size: 10px; color: #6b7280; text-transform: uppercase;
            letter-spacing: 1px; margin-bottom: 4px;
        }
        .highlight-number { font-size: 46px; font-weight: 900; color: #1e3a8a; line-height: 1; }
        .highlight-date { font-size: 16px; font-weight: bold; color: #111827; }
        .highlight-time { font-size: 13px; font-weight: bold; color: #e11d48; margin-top: 4px; background: #ffe4e6; display: inline-block; padding: 2px 8px; border-radius: 4px; }

        /* ─── ALERT BOX (PENGUMUMAN PENTING) ─── */
        /* Dirubah menjadi table agar presisi lebarnya dengan kotak di atasnya */
        .alert-box {
            width: 100%;
            background-color: #fef2f2;
            border: 2px solid #e11d48;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .alert-box td {
            padding: 8px;
            text-align: center;
            color: #be123c;
        }
        .alert-text {
            font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;
        }

        /* ─── DATA SISWA ─── */
        .section-title {
            font-size: 13px; font-weight: bold; color: #1e3a8a;
            border-bottom: 1px solid #e5e7eb; padding-bottom: 4px;
            margin-bottom: 10px; text-transform: uppercase;
        }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table td { padding: 5px 0; vertical-align: top; }
        .data-label { width: 35%; font-weight: bold; color: #4b5563; }
        .data-colon { width: 5%; text-align: center; }
        .data-value { width: 60%; font-weight: bold; }

        /* ─── CHECKLIST BERKAS ─── */
        .checklist-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .checklist-table td { padding: 5px 0; vertical-align: middle; }
        .box-container { width: 5%; text-align: left; }
        .box { display: inline-block; width: 12px; height: 12px; border: 1px solid #111827; }
        .box-text { width: 95%; font-size: 11.5px; color: #374151; }

        /* ─── FOOTER TTD ─── */
        .signature-section {
            width: 100%; margin-top: 15px; border: 1px solid #d1d5db;
        }
        .signature-table { width: 100%; border-collapse: collapse; }
        .signature-table td { padding: 10px; vertical-align: top; }
        .signature-box { text-align: center; }
        .ttd-space { height: 50px; } 
        .notes-area { border-bottom: 1px dotted #9ca3af; height: 18px; margin-top: 6px; }
    </style>
</head>
<body>

    @php
        // 🔥 VAKSIN LOKALISASI: Memaksa Carbon berbahasa Indonesia di dalam output PDF!
        \Carbon\Carbon::setLocale('id');

        // 1. Render Logo Utama (Kiri)
        $logoPath = public_path('images/logo-adb.jpg'); 
        $logoBase64 = '';
        if(file_exists($logoPath)) {
            $logoBase64 = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($logoPath));
        }

        // 2. Render Logo Integritas (Kanan)
        $logoIntegrityPath = public_path('images/integrasi.png'); 
        $logoIntegrityBase64 = '';
        if(file_exists($logoIntegrityPath)) {
            $logoIntegrityBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoIntegrityPath));
        }

        // 3. Render QR Code (On The Fly)
        $qrSvgRaw = (string) \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                        ->margin(0)
                        ->size(65)
                        ->generate(route('ticket.download', $ticket->ticket_number));
        
        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvgRaw);
    @endphp

    <table class="kop-surat">
        <tr>
            <td style="width: 14%; text-align: center;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="width: 70px; height: auto;">
                @else
                    <div style="width: 70px; height: 70px; background: #1e3a8a; border-radius: 50%;"></div>
                @endif
            </td>
            
            <td style="width: 72%; padding-left: 10px; text-align: center;">
                <h1 class="instansi-name">{{ $ticket->event?->subtitle ?: 'SMK NEGERI 1 ADIWERNA' }}</h1>
                <p class="instansi-desc">{{ $ticket->event?->name ?: 'Digitalisasi Sistem Antrian Pelayanan ADB (di-SAPA)' }}</p>
                <p class="instansi-address">Sistem Antrian Pelayanan Terpadu (di-SAPA)</p>
            </td>
            
            <td style="width: 14%; text-align: center;">
                @if($logoIntegrityBase64)
                    <img src="{{ $logoIntegrityBase64 }}" style="width: 70px; height: auto;">
                @endif
            </td>
        </tr>
    </table>

    <table class="highlight-box">
        <tr>
            <td style="border-right: 1px solid #bfdbfe; width: 35%;">
                <div class="highlight-title">Nomor Antrian</div>
                <div class="highlight-number">{{ explode('-', $ticket->ticket_number)[0] }}</div>
            </td>
            <td style="border-right: 1px solid #bfdbfe; width: 45%;">
                <div class="highlight-title">Jadwal Kehadiran (Sesi {{ $ticket->serviceDate->session_name }})</div>
                <div class="highlight-date">
                    {{ \Carbon\Carbon::parse($ticket->serviceDate->service_date)->translatedFormat('l, d F Y') }}
                </div>
                <div class="highlight-time">
                    Pukul {{ \Carbon\Carbon::parse($ticket->serviceDate->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($ticket->serviceDate->end_time)->format('H:i') }} WIB
                </div>
            </td>
            <td style="width: 20%; text-align: center;">
                @if($qrCodeBase64)
                    <img src="{{ $qrCodeBase64 }}" style="width: 65px; height: 65px;">
                @endif
                <div style="font-size: 9px; color: #6b7280; margin-top: 5px;">Scan e-Tiket</div>
            </td>
        </tr>
    </table>

    @php $requiresCheckin = (bool) ($ticket->event?->requires_checkin ?? false); @endphp
    <table class="alert-box">
        <tr>
            <td>
                <span class="alert-text">
                    @if($requiresCheckin)
                        [ PENTING ] Harap Hadir 15 Menit Sebelum Jam Kedatangan Dimulai &amp; Siapkan QR e-Tiket Ini untuk Check-In!
                    @else
                        [ PENTING ] Harap Hadir 15 Menit Sebelum Jam Kedatangan Dimulai &amp; Pantau Panggilan Nomor Antrian Anda!
                    @endif
                </span>
            </td>
        </tr>
    </table>

    <div class="section-title">A. Data Pendaftar ({{ $ticket->event?->name ?? 'Antrian' }})</div>
    <table class="data-table">
        <tr>
            <td class="data-label">{{ $ticket->event?->name_label ?? 'Nama Lengkap' }}</td>
            <td class="data-colon">:</td>
            <td class="data-value" style="font-size: 13px; text-transform: uppercase;">{{ $ticket->nama_cpd }}</td>
        </tr>
        <tr>
            <td class="data-label">{{ $ticket->event?->identifier_label ?? 'Identitas' }}</td>
            <td class="data-colon">:</td>
            <td class="data-value">{{ $ticket->nisn }}</td>
        </tr>
        @foreach($ticket->displayDetails() as $detail)
        <tr>
            <td class="data-label">{{ $detail['label'] }}</td>
            <td class="data-colon">:</td>
            <td class="data-value">{{ $detail['value'] }}</td>
        </tr>
        @endforeach
    </table>

    @php $checklistItems = $ticket->event?->checklist_items ?? []; @endphp
    @if(!empty($checklistItems))
    <div class="section-title" style="margin-top: 15px;">B. Kelengkapan / Ketentuan Event</div>
    <table class="checklist-table">
        @foreach($checklistItems as $item)
        <tr><td class="box-container"><div class="box"></div></td><td class="box-text">{{ $item }}</td></tr>
        @endforeach
    </table>
    @endif

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td style="width: 45%; border-right: 1px solid #d1d5db;">
                    <div style="font-weight: bold; margin-bottom: 10px; font-size: 11.5px;">Diisi oleh Petugas Verifikasi:</div>
                    <div style="margin-bottom: 8px; font-size: 11px;">Tanggal Datang : ....................................................</div>
                    <div style="font-size: 11px;">Catatan Kekurangan/Revisi:</div>
                    <div class="notes-area"></div>
                    <div class="notes-area"></div>
                    <div class="notes-area"></div>
                    
                    <div style="margin-top: 15px; font-size: 10px; color: #6b7280;">
                        <strong>Kode Aktivasi Sistem:</strong><br>
                        {{ $ticket->ticket_number }}
                    </div>
                </td>
                <td style="width: 55%;">
                    <table style="width: 100%;">
                        <tr>
                            <td class="signature-box" style="width: 50%;">
                                <div style="font-size: 11px; font-weight: bold;">{{ $requiresCheckin ? 'Loket Scanner' : 'Peserta / Pemohon' }}</div>
                                <div class="ttd-space"></div>
                                <div style="font-size: 11px;">( .............................................. )</div>
                            </td>
                            <td class="signature-box" style="width: 50%;">
                                <div style="font-size: 11px; font-weight: bold;">Petugas Verifikator</div>
                                <div class="ttd-space"></div>
                                <div style="font-size: 11px;">( .............................................. )</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>