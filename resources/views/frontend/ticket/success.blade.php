@extends('layouts.app')

@section('content')
@php
    // 🔥 VAKSIN LOKALISASI: Memaksa Carbon berbahasa Indonesia!
    \Carbon\Carbon::setLocale('id');
@endphp

<div class="max-w-lg w-full bg-white p-8 rounded-2xl shadow-xl border border-gray-100 text-center">
    
    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
        <i class="fa-solid fa-check text-3xl text-green-600"></i>
    </div>
    
    <h2 class="text-2xl font-extrabold text-gray-900 mb-2">Pendaftaran Berhasil!</h2>
    <p class="text-gray-500 text-sm mb-6">Tunjukkan QR Code ini kepada <span class="font-bold text-gray-800">Petugas OSIS di Gerbang</span> untuk Check-In.</p>

    <div class="bg-blue-50 rounded-xl p-6 border border-blue-100 mb-4 relative overflow-hidden">
        <p class="text-sm text-blue-800 font-semibold mb-1">NOMOR ANTRIAN ANDA</p>
        <p class="text-5xl font-black text-blue-600 tracking-wider mb-2">{{ explode('-', $ticket->ticket_number)[0] }}</p>
        
        <div class="bg-blue-100/50 rounded-lg p-3 mb-4 border border-blue-200">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-1"><i class="fa-regular fa-calendar text-blue-500 mr-1"></i> Jadwal Kedatangan</p>
            <p class="text-base font-black text-gray-800">
                {{ \Carbon\Carbon::parse($ticket->serviceDate->service_date)->translatedFormat('l, d F Y') }}
            </p>
            <p class="text-sm font-bold text-blue-700 mt-0.5">
                Sesi {{ $ticket->serviceDate->session_name }} 
                ({{ \Carbon\Carbon::parse($ticket->serviceDate->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($ticket->serviceDate->end_time)->format('H:i') }} WIB)
            </p>
        </div>
        
        <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
            <p class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-2 border-b pb-2"><i class="fa-solid fa-satellite-dish text-red-500 mr-1 animate-pulse"></i> Live Monitoring</p>
            
            @php
                // Logika Status untuk Tampilan Awal (Saat Halaman Direfresh)
                $statusText = '';
                $statusIcon = '';
                $statusColor = '';
                $timeText = '-- Menit';
                $infoText = '<span class="text-yellow-500 font-bold">Lakukan Check-In di Gerbang agar estimasi waktu muncul.</span>';

                if ($ticket->status == 'menunggu') {
                    $statusText = 'Menunggu Check-In';
                    $statusIcon = 'fa-clock';
                    $statusColor = 'text-yellow-600';
                } elseif ($ticket->status == 'hadir_di_lokasi') {
                    $statusText = 'Hadir di Lokasi';
                    $statusIcon = 'fa-check-double';
                    $statusColor = 'text-green-600';
                    $infoText = '<span class="text-green-500 font-bold">Menunggu antrian dipanggil...</span>';
                } elseif ($ticket->status == 'dipanggil') {
                    $statusText = 'SEDANG DIPANGGIL!';
                    $statusIcon = 'fa-bullhorn';
                    $statusColor = 'text-red-600 animate-pulse';
                    $timeText = 'SEKARANG!';
                    $infoText = '<span class="text-red-500 font-bold">Segera menuju ke meja Verifikator!</span>';
                } elseif ($ticket->status == 'menghadap') {
                    $statusText = 'SEDANG VERIFIKASI';
                    $statusIcon = 'fa-user-check';
                    $statusColor = 'text-blue-600';
                    $timeText = 'PROSES';
                    $infoText = '<span class="text-blue-500 font-bold">Anda sedang dilayani di meja verifikator.</span>';
                } elseif ($ticket->status == 'selesai') {
                    $statusText = 'SELESAI';
                    $statusIcon = 'fa-flag-checkered';
                    $statusColor = 'text-emerald-600';
                    $timeText = 'TUNTAS';
                    $infoText = '<span class="text-emerald-500 font-bold">Proses pendaftaran Anda telah selesai. Terima kasih!</span>';
                } elseif ($ticket->status == 'dilewati') {
                    $statusText = 'DILEWATI';
                    $statusIcon = 'fa-forward-step';
                    $statusColor = 'text-red-500';
                    $timeText = 'TERLEWAT';
                    $infoText = '<span class="text-red-500 font-bold">Antrian terlewat! Silakan menunggu dipanggil kembali!</span>';
                }
            @endphp

            <div id="liveStatusContainer" class="flex items-center justify-between mt-2">
                <div class="text-left">
                    <p class="text-[10px] text-gray-400">Status Kehadiran</p>
                    <p id="liveStatusText" class="text-xs font-bold {{ $statusColor }}">
                        <i class="fa-solid {{ $statusIcon }}"></i> {{ $statusText }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-400">Estimasi Waktu Tunggu</p>
                    <p id="liveTimeText" class="text-lg font-black text-gray-800">{!! $timeText !!}</p>
                </div>
            </div>
            
            <p id="liveQueueInfo" class="text-[10px] text-gray-400 mt-3 text-left">{!! $infoText !!}</p>

            <div id="catatanLoketBox" class="{{ ($ticket->status == 'selesai' && !empty($ticket->catatan_loket)) ? '' : 'hidden' }} mt-4 p-4 bg-orange-50 border border-orange-200 rounded-xl text-left shadow-sm transition-all duration-500">
                <p class="text-[10px] font-black text-orange-800 uppercase tracking-widest mb-1">
                    <i class="fa-solid fa-thumbtack mr-1"></i> Catatan Verifikator
                </p>
                <p id="catatanLoketText" class="text-sm font-bold text-gray-800 leading-snug">
                    {{ $ticket->catatan_loket ?? '' }}
                </p>
            </div>

        </div>
    </div>

<div class="qr-code-wrapper bg-white p-4 rounded-2xl shadow-sm border inline-block">
    @php
        $urlData = route('ticket.download', $ticket->ticket_number);
        $qrCode = SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(200)
                    ->margin(2)
                    ->generate($urlData);
        $base64Qr = base64_encode($qrCode);
    @endphp
    
    <img src="data:image/svg+xml;base64,{{ $base64Qr }}" alt="QR Code Antrian" class="w-48 h-48 mx-auto">
    
    <p class="text-xs text-gray-400 mt-2 font-bold uppercase tracking-widest">Scan untuk Check-in</p>
</div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center mt-4">
        <a href="{{ route('ticket.download', $ticket->ticket_number) }}" class="inline-flex justify-center items-center px-6 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition">
            <i class="fa-solid fa-download mr-2"></i> Download Tiket PDF
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // AJAX Polling setiap 10 detik untuk Live Monitor
    setInterval(() => {
        fetch("{{ route('api.ticket.live', $ticket->ticket_number) }}")
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    const statusText = document.getElementById('liveStatusText');
                    const timeText = document.getElementById('liveTimeText');
                    const infoText = document.getElementById('liveQueueInfo');
                    
                    // DOM Element untuk Catatan
                    const catatanBox = document.getElementById('catatanLoketBox');
                    const catatanText = document.getElementById('catatanLoketText');

                    // Reset Catatan Box (Sembunyikan dulu)
                    catatanBox.classList.add('hidden');

                    if(data.status === 'menunggu') {
                        statusText.className = 'text-xs font-bold text-yellow-600';
                        statusText.innerHTML = '<i class="fa-solid fa-clock"></i> Menunggu Check-In';
                        timeText.innerText = '-- Menit';
                        infoText.innerHTML = '<span class="text-red-500 font-bold">Lakukan Check-In di Gerbang agar estimasi waktu muncul.</span>';
                    } else if(data.status === 'hadir_di_lokasi') {
                        statusText.className = 'text-xs font-bold text-green-600';
                        statusText.innerHTML = '<i class="fa-solid fa-check-double"></i> Hadir di Lokasi';
                        timeText.innerText = '± ' + data.estimasi_waktu + ' Menit';
                        infoText.innerHTML = `<span class="text-green-500 font-bold">Terdapat ${data.antrian_di_depan} antrian di depan Anda.</span>`;
                    } else if(data.status === 'dipanggil') {
                        statusText.className = 'text-xs font-bold text-red-600 animate-pulse';
                        statusText.innerHTML = '<i class="fa-solid fa-bullhorn"></i> SEDANG DIPANGGIL!';
                        timeText.innerText = 'SEKARANG!';
                        infoText.innerHTML = `<span class="text-red-500 font-bold">Segera menuju ke meja Verifikator!</span>`;
                    } else if(data.status === 'menghadap') {
                        statusText.className = 'text-xs font-bold text-blue-600';
                        statusText.innerHTML = '<i class="fa-solid fa-user-check"></i> SEDANG VERIFIKASI';
                        timeText.innerText = 'PROSES';
                        infoText.innerHTML = '<span class="text-blue-500 font-bold">Anda sedang dilayani di meja verifikator.</span>';
                    } else if(data.status === 'selesai') {
                        statusText.className = 'text-xs font-bold text-emerald-600';
                        statusText.innerHTML = '<i class="fa-solid fa-flag-checkered"></i> SELESAI';
                        timeText.innerText = 'TUNTAS';
                        infoText.innerHTML = '<span class="text-emerald-500 font-bold">Proses pendaftaran Anda telah selesai. Terima kasih!</span>';
                        
                        // 🔥 LOGIC CATATAN LIVE 🔥
                        if (data.catatan_loket) {
                            catatanBox.classList.remove('hidden');
                            catatanText.innerText = data.catatan_loket;
                        }
                    } else if(data.status === 'dilewati') {
                        statusText.className = 'text-xs font-bold text-red-500';
                        statusText.innerHTML = '<i class="fa-solid fa-forward-step"></i> DILEWATI';
                        timeText.innerText = 'TERLEWAT';
                        infoText.innerHTML = `<span class="text-red-500 font-bold">Antrian terlewat! Silakan menunggu dipanggil kembali!</span>`;
                    }
                }
            });
    }, 10000); // 10000 ms = 10 Detik
</script>
@endpush