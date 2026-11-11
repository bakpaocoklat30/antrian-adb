@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto w-full flex flex-col">
    
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-6 shrink-0">
        <h1 class="text-2xl font-black text-gray-800 text-left flex items-center">
            <i class="fa-solid fa-clipboard-user text-orange-500 mr-2"></i> Tracker Kehadiran (No-Show)
            
            <span class="bg-green-100 text-green-700 text-[10px] px-3 py-1 rounded-full animate-pulse border border-green-300 ml-4 uppercase tracking-widest font-bold shadow-sm">
                <i class="fa-solid fa-satellite-dish mr-1"></i> Live Auto-Update
            </span>

            <button onclick="bukaMutasi()" class="bg-blue-100 hover:bg-blue-200 text-blue-700 text-[10px] px-3 py-1 rounded-full border border-blue-300 ml-3 uppercase tracking-widest font-bold shadow-sm transition cursor-pointer">
                <i class="fa-solid fa-life-ring mr-1"></i> Mutasi Siswa
            </button>
        </h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition"><i class="fa-solid fa-arrow-left mr-2"></i> Kembali</a>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col min-h-[70vh]">
        
        <form id="filterForm" class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 items-end shrink-0 relative">
            
            <div id="loadingIndicator" class="hidden absolute -top-3 right-4 bg-white border border-orange-200 px-3 py-1 rounded-full shadow-sm text-xs font-bold text-orange-500 animate-pulse z-10">
                <i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Memuat data...
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Filter Event</label>
                <select id="eventSelect" name="event_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 outline-none transition cursor-pointer">
                    <option value="">-- Semua Event --</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" {{ request('event_id') == $event->id ? 'selected' : '' }}>
                            {{ $event->name }}{{ !$event->is_active ? ' (Nonaktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Cari Nama / Identitas / Tiket</label>
                <div class="relative">
                    <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci..." class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 outline-none transition">
                </div>
            </div>
            
            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Jadwal Sesi</label>
                <select id="dateSelect" name="date_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 outline-none transition cursor-pointer">
                    <option value="">-- Semua Jadwal --</option>
                    @foreach($dates as $date)
                        <option value="{{ $date->id }}" {{ request('date_id') == $date->id ? 'selected' : '' }}>
                            {{ $date->event ? $date->event->name . ' - ' : '' }}{{ \Carbon\Carbon::parse($date->service_date)->translatedFormat('d M Y') }} ({{ $date->session_name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Status Kehadiran</label>
                <select id="statusSelect" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 outline-none transition cursor-pointer">
                    <option value="">-- Semua Status --</option>
                    <option value="menunggu" {{ request('status') == 'menunggu' ? 'selected' : '' }}>⏳ Menunggu</option>
                    <option value="hadir_di_lokasi" {{ request('status') == 'hadir_di_lokasi' ? 'selected' : '' }}>✅ Hadir di Lokasi</option>
                    <option value="dipanggil" {{ request('status') == 'dipanggil' ? 'selected' : '' }}>📢 Dipanggil</option>
                    <option value="menghadap" {{ request('status') == 'menghadap' ? 'selected' : '' }}>👨‍💻 Menghadap</option>
                    <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>🏁 Selesai</option>
                    <option value="dilewati" {{ request('status') == 'dilewati' ? 'selected' : '' }}>⏭️ Dilewati</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-600 mb-1">Baris & Export</label>
                <div class="flex gap-2">
                    <select id="perPageSelect" name="per_page" class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-400 outline-none transition cursor-pointer font-bold text-gray-700">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>

                    <button type="button" onclick="exportData()" class="w-1/2 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-2 rounded-lg shadow-sm text-xs transition flex justify-center items-center">
                        <i class="fa-solid fa-file-excel mr-1 text-sm"></i> Export
                    </button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto flex-1 mb-4 rounded-lg border border-gray-200 shadow-sm">
            <table class="w-full text-left border-collapse min-w-max" id="attendanceTable">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-sm">
                        <th class="p-3 border-b whitespace-nowrap w-[11%]">No Antrian</th>
                        <th class="p-3 border-b w-[23%]">Nama & Identitas</th>
                        <th class="p-3 border-b w-[24%]">Event & Data Isian</th>
                        <th class="p-3 border-b w-[17%] whitespace-nowrap">Jadwal Sesi</th>
                        <th class="p-3 border-b text-center w-[12%]">Status</th>
                        <th class="p-3 border-b text-center w-[13%]">Follow-Up / Info</th>
                    </tr>
                </thead>
                <tbody class="text-sm" id="attendanceBody">
                    @forelse($tickets as $ticket)
                    <tr class="hover:bg-orange-50 border-b transition">
                        <td class="p-3 font-black text-gray-800 text-lg">{{ explode('-', $ticket->ticket_number)[0] }}</td>
                        
                        <td class="p-3 break-words">
                            <p class="font-bold text-gray-900 uppercase">{{ $ticket->nama_cpd }}</p>
                            <p class="text-xs text-gray-500 font-mono">{{ $ticket->nisn }}</p>
                        </td>
                        
                        <td class="p-3 text-gray-600 break-words">
                            @if($ticket->event)
                                <span class="inline-block mb-1 px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-200 text-[10px] font-bold">{{ $ticket->event->name }}</span>
                            @endif
                            @php $details = $ticket->displayDetails(); @endphp
                            @if(count($details) > 0)
                                <div class="text-xs text-gray-700">
                                    <span class="font-semibold">{{ $details[0]['label'] ?? '' }}:</span> {{ \Illuminate\Support\Str::limit((string) ($details[0]['value'] ?? '-'), 35) }}
                                </div>
                                <button type="button" onclick="lihatDetailPeserta(this)" data-detail="{{ json_encode($ticket->detailPayload()) }}" class="mt-1 text-[11px] text-blue-600 hover:underline font-bold">
                                    <i class="fa-solid fa-eye mr-1"></i>Lihat Semua ({{ count($details) }})
                                </button>
                            @else
                                <span class="text-xs text-gray-400 italic">-</span>
                            @endif
                        </td>
                        
                        <td class="p-3 text-gray-600 text-xs font-medium">
                            <span class="text-gray-800 font-bold whitespace-nowrap">{{ $ticket->serviceDate ? \Carbon\Carbon::parse($ticket->serviceDate->service_date)->translatedFormat('d M Y') : '-' }}</span><br>
                            @if($ticket->serviceDate)
                            <span class="text-blue-600 font-bold bg-blue-50 px-2 py-0.5 rounded border border-blue-100 mt-1 inline-block whitespace-nowrap">
                                <i class="fa-regular fa-clock"></i> 
                                {{ \Carbon\Carbon::parse($ticket->serviceDate->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($ticket->serviceDate->end_time)->format('H:i') }}
                            </span>
                            @endif
                        </td>

                        <td class="p-3 text-center">
                            @php
                                $sText = ''; $sClass = ''; $sIcon = '';
                                if($ticket->status == 'menunggu') { $sText = 'Menunggu'; $sClass = 'bg-yellow-100 text-yellow-700 border-yellow-200'; $sIcon = 'fa-clock'; }
                                elseif($ticket->status == 'hadir_di_lokasi') { $sText = 'Hadir'; $sClass = 'bg-green-100 text-green-700 border-green-200'; $sIcon = 'fa-check-double'; }
                                elseif($ticket->status == 'dipanggil') { $sText = 'Dipanggil'; $sClass = 'bg-red-100 text-red-700 border-red-200 animate-pulse shadow-sm shadow-red-300'; $sIcon = 'fa-bullhorn'; }
                                elseif($ticket->status == 'menghadap') { $sText = 'Verifikasi'; $sClass = 'bg-blue-100 text-blue-700 border-blue-200'; $sIcon = 'fa-user-check'; }
                                elseif($ticket->status == 'selesai') { $sText = 'Selesai'; $sClass = 'bg-emerald-100 text-emerald-700 border-emerald-200'; $sIcon = 'fa-flag-checkered'; }
                                elseif($ticket->status == 'dilewati') { $sText = 'Dilewati'; $sClass = 'bg-gray-200 text-gray-700 border-gray-300'; $sIcon = 'fa-forward-step'; }
                            @endphp
                            <span class="{{ $sClass }} px-3 py-1 rounded-full text-[10px] font-black border uppercase tracking-wider whitespace-nowrap shadow-sm"><i class="fa-solid {{ $sIcon }} mr-1"></i> {{ $sText }}</span>
                        </td>
                        
                        <td class="p-3 text-center">
                            @php
                                $waRaw = $ticket->fieldValue('whatsapp');
                                $waNumber = $waRaw ? preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $waRaw)) : null;
                                $pesanWa = '';
                                $eventName = $ticket->event?->name ?? 'Layanan';

                                if ($ticket->serviceDate) {
                                    $tglFormat = \Carbon\Carbon::parse($ticket->serviceDate->service_date)->translatedFormat('d F Y');
                                    $namaSesi = $ticket->serviceDate->session_name;
                                    $jamMulai = \Carbon\Carbon::parse($ticket->serviceDate->start_time)->format('H:i');
                                    $jamSelesai = \Carbon\Carbon::parse($ticket->serviceDate->end_time)->format('H:i');

                                    if($ticket->status == 'menunggu') {
                                        $pesanWa = "Halo {$ticket->nama_cpd}, kami dari Panitia {$eventName}. Mengingatkan bahwa *jadwal Anda adalah hari ini {$tglFormat} Sesi {$namaSesi} ({$jamMulai} - {$jamSelesai} WIB)*. Apakah Anda dalam perjalanan?";
                                    } elseif($ticket->status == 'dilewati') {
                                        $pesanWa = "Halo {$ticket->nama_cpd}, antrian Anda pada {$eventName} baru saja DIPANGGIL namun belum merespon, sehingga dilewati sementara. Mohon segera menuju meja panitia.";
                                    } elseif($ticket->status == 'selesai') {
                                        $pesanWa = "Halo {$ticket->nama_cpd}, terima kasih telah mengikuti layanan {$eventName}. Proses Anda telah selesai.";
                                    }
                                }
                            @endphp

                            @if($pesanWa && $waNumber)
                                <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode($pesanWa) }}" target="_blank" class="inline-block bg-green-500 hover:bg-green-600 text-white font-bold py-1 px-3 rounded-lg shadow-sm transition text-[10px] whitespace-nowrap">
                                    <i class="fa-brands fa-whatsapp text-sm align-middle mr-1"></i> WhatsApp
                                </a>
                            @elseif($pesanWa)
                                <span class="text-gray-400 text-[10px] italic">Tanpa WA</span>
                            @else
                                <span class="text-gray-400 text-[10px] italic font-medium whitespace-nowrap"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Dalam Proses...</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-12 text-center text-gray-500 font-medium">Tidak ada data peserta ditemukan pada filter ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="shrink-0 border-t pt-4" id="paginationContainer">
            {{ $tickets->links() }}
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let isActionActive = false;

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function(ch) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[ch];
        });
    }

    function lihatDetailPeserta(btn) {
        isActionActive = true;
        const info = JSON.parse(btn.dataset.detail || '{}');
        const details = Array.isArray(info.details) ? info.details : [];
        const rows = details.map((item) =>
            `<div class="flex justify-between gap-4 border-b border-gray-100 py-1.5 text-xs">
                <span class="text-gray-500 font-semibold">${escapeHtml(item.label || item.key || '-')}</span>
                <span class="text-gray-800 font-bold text-right">${escapeHtml(item.value ?? '-')}</span>
            </div>`
        ).join('') || '<div class="text-xs text-gray-400 italic py-2">Tidak ada data tambahan.</div>';

        Swal.fire({
            title: escapeHtml(info.name || 'Detail Peserta'),
            html: `<div class="text-left bg-gray-50 p-4 rounded-xl border border-gray-200 space-y-1.5">
                <div class="flex justify-between gap-4 border-b border-gray-200 pb-2 mb-1 text-xs">
                    <span class="text-indigo-700 font-black">${escapeHtml(info.event)}</span>
                    <span class="font-mono font-black text-blue-700">${escapeHtml(info.ticket)}</span>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 py-1.5 text-xs">
                    <span class="text-gray-500 font-semibold">${escapeHtml(info.identifier_label || 'Identitas')}</span>
                    <span class="text-gray-800 font-mono font-bold">${escapeHtml(info.identifier)}</span>
                </div>
                ${rows}
            </div>`,
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#2563eb'
        }).then(() => { isActionActive = false; });
    }

    // 🔥 EXPORT TETAP AMAN KARENA MENGAMBIL DARI INPUT VALUE 🔥
    function exportData() {
        const url = new URL(window.location.origin + window.location.pathname);
        url.searchParams.set('event_id', document.getElementById('eventSelect').value);
        url.searchParams.set('search', document.getElementById('searchInput').value);
        url.searchParams.set('date_id', document.getElementById('dateSelect').value);
        url.searchParams.set('status', document.getElementById('statusSelect').value);
        url.searchParams.set('export', 'true');
        
        window.location.href = url.toString();
    }

    // =========================================================
    // 🔥 FUNGSI MUTASI PESERTA (TETAP AMAN) 🔥
    // =========================================================
    function bukaMutasi() {
        isActionActive = true; 
        Swal.fire({
            title: 'Mutasi Jadwal Peserta',
            text: "Masukkan Identitas (NISN/NIK) atau Nomor Tiket:",
            input: 'text',
            inputAttributes: { placeholder: 'Ketik Identitas atau No. Tiket...' },
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-search"></i> Cari Data',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#2563eb',
            showLoaderOnConfirm: true,
            preConfirm: (nisn) => {
                if(!nisn || !nisn.trim()) { Swal.showValidationMessage('Identitas / No. Tiket tidak boleh kosong!'); return false; }
                return fetch(`{{ route('admin.cari_nisn') }}`, {
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ nisn: nisn.trim() })
                })
                .then(response => response.json())
                .catch(error => { Swal.showValidationMessage(`Request failed: ${error}`); });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if(result.value.success) {
                    let s = result.value.data;
                    let oldAntrian = s.ticket_number.split('-')[0];
                    
                    let warningHtml = result.value.is_today_full 
                        ? `<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded-lg mt-3 text-xs font-black animate-pulse">
                            <i class="fa-solid fa-triangle-exclamation"></i> PERHATIAN: Kuota Hari Ini sudah PENUH. Mutasi akan menerobos batas maksimal kuota (Force Override).
                           </div>` 
                        : '';
                    
                    Swal.fire({
                        title: 'Data Ditemukan!',
                        html: `<div class="text-left text-sm space-y-2 bg-gray-50 p-4 rounded-lg border mt-2 mb-2 shadow-inner">
                                <p><b>Event:</b> <span class="text-indigo-700 font-bold">${escapeHtml(result.value.event_name || '-')}</span></p>
                                <p><b>Identitas:</b> ${escapeHtml(s.nisn)}</p>
                                <p><b>Nama:</b> ${escapeHtml(s.nama_cpd)}</p>
                                <p><b>Status Jadwal:</b> <span class="text-red-600 font-black">${escapeHtml(result.value.status_jadwal)}</span></p>
                                <p><b>Jadwal Lama:</b> ${escapeHtml(result.value.session_date)}</p>
                                <p><b>No Tiket Lama:</b> ${escapeHtml(oldAntrian)}</p>
                               </div>
                               <p class="text-xs text-gray-500">Aksi ini akan mencabut tiket lama dan memberikan <b>Nomor Tiket Baru</b> untuk dimasukkan ke antrian hari ini.</p>
                               ${warningHtml}`,
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: '<i class="fa-solid fa-exchange-alt"></i> Proses Mutasi',
                        cancelButtonText: 'Batal'
                    }).then((mutasiResult) => {
                        if (mutasiResult.isConfirmed) {
                            fetch(`{{ route('admin.mutasi') }}`, {
                                method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ ticket_id: s.id })
                            })
                            .then(res => res.json())
                            .then(mutasiData => {
                                if(mutasiData.success) {
                                    Swal.fire('Berhasil!', mutasiData.message, 'success').then(() => location.reload());
                                } else {
                                    Swal.fire('Gagal!', mutasiData.message, 'error').then(() => { isActionActive = false; });
                                }
                            });
                        } else { isActionActive = false; }
                    });
                } else {
                    Swal.fire({
                        title: 'Info Peringatan', 
                        html: result.value.message, 
                        icon: 'warning'
                    }).then(() => { isActionActive = false; });
                }
            } else { isActionActive = false; }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const eventSelect = document.getElementById('eventSelect');
        const searchInput = document.getElementById('searchInput');
        const dateSelect = document.getElementById('dateSelect');
        const statusSelect = document.getElementById('statusSelect');
        const perPageSelect = document.getElementById('perPageSelect');
        const loadingIndicator = document.getElementById('loadingIndicator');

        let typingTimer;                
        const doneTypingInterval = 500; 
        
        // 🔥 MEMORI RADAR (Menyimpan URL Aktif) 🔥
        let currentSpaUrl = window.location.href;

        // =========================================================
        // 🔥 ENGINE LIVE LOAD DATA (AJAX SPA) 🔥
        // =========================================================
        function fetchLive(url) {
            currentSpaUrl = url; // Update ingatan Radar!
            loadingIndicator.classList.remove('hidden');
            
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newBody = doc.getElementById('attendanceBody');
                if(newBody) document.getElementById('attendanceBody').innerHTML = newBody.innerHTML;

                const newPagination = doc.getElementById('paginationContainer');
                if(newPagination) {
                    document.getElementById('paginationContainer').innerHTML = newPagination.innerHTML;
                    attachPaginationEvents(); 
                }
                
                loadingIndicator.classList.add('hidden');
                
                // Murni Kosongkan URL tanpa ?page=...
                window.history.replaceState({}, document.title, window.location.pathname);
            })
            .catch(error => { console.error('AJAX Error:', error); loadingIndicator.classList.add('hidden'); });
        }

        function triggerFilter() {
            const url = new URL(window.location.pathname, window.location.origin);
            if (eventSelect && eventSelect.value) url.searchParams.set('event_id', eventSelect.value);
            if (searchInput.value) url.searchParams.set('search', searchInput.value);
            if (dateSelect.value) url.searchParams.set('date_id', dateSelect.value);
            if (statusSelect.value) url.searchParams.set('status', statusSelect.value);
            if (perPageSelect.value) url.searchParams.set('per_page', perPageSelect.value);
            
            fetchLive(url.toString());
        }

        // Bajak Paginasi Bawaan Laravel
        function attachPaginationEvents() {
            document.querySelectorAll('#paginationContainer a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault(); 
                    fetchLive(this.href);
                });
            });
        }

        attachPaginationEvents();

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            triggerFilter();
        });

        searchInput.addEventListener('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(triggerFilter, doneTypingInterval);
        });

        searchInput.addEventListener('keydown', function () { clearTimeout(typingTimer); });
        searchInput.addEventListener('search', function() { triggerFilter(); });

        if (eventSelect) eventSelect.addEventListener('change', triggerFilter);
        dateSelect.addEventListener('change', triggerFilter);
        statusSelect.addEventListener('change', triggerFilter);
        perPageSelect.addEventListener('change', triggerFilter);
        
        let val = searchInput.value;
        searchInput.value = '';
        searchInput.value = val;
        if(val) searchInput.focus();

        // =========================================================
        // 🔥 ENGINE LIVE AUTO-UPDATE SILENT BACKGROUND 🔥
        // =========================================================
        setInterval(() => {
            if (document.activeElement === searchInput || isActionActive || Swal.isVisible()) return;

            // RADAR MEMAKAI INGATAN (currentSpaUrl) BUKAN window.location.href
            fetch(currentSpaUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newBody = doc.getElementById('attendanceBody');
                const oldBody = document.getElementById('attendanceBody');
                if(newBody && oldBody) oldBody.innerHTML = newBody.innerHTML;

                const newPagination = doc.getElementById('paginationContainer');
                const oldPagination = document.getElementById('paginationContainer');
                if(newPagination && oldPagination) {
                    oldPagination.innerHTML = newPagination.innerHTML;
                    attachPaginationEvents(); // Pasang ulang event untuk jaga-jaga
                }
            })
            .catch(error => console.error('Gagal memuat live update:', error));
            
        }, 5000); 
    });
</script>
@endpush