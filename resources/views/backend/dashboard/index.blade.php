@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto w-full flex flex-col h-full relative">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-6 shrink-0 relative overflow-hidden">
        <div class="absolute right-0 top-0 w-32 h-32 bg-blue-50 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-center gap-4">
            <div>
                <h1 class="text-2xl font-black text-gray-800 text-left flex items-center gap-2">
                    <i class="fa-solid fa-users text-blue-600"></i> Dashboard Verifikator
                </h1>
                <p class="text-sm text-gray-500 mt-1">Login sebagai: <span class="font-bold text-blue-600">{{ Auth::user()->name }}</span> (Loket {{ Auth::user()->loket_number }})</p>
            </div>
            
            <div class="flex items-center gap-2 ml-0 md:ml-4 mt-2 md:mt-0">
                <span class="relative flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span></span>
                <span class="text-xs font-black text-green-600 tracking-widest bg-green-50 px-3 py-1 rounded-full border border-green-200 uppercase">Live Radar</span>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 mt-4 md:mt-0 relative z-10">
            <button onclick="bukaScannerBerkas()" class="bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-sm transition flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-qrcode text-lg"></i> Scan Map
            </button>
            
            <button onclick="bukaCekButaWarna()" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-4 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-eye"></i> Cek Buta Warna
            </button>
            
            <button onclick="bukaMutasi()" class="bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 px-4 py-2.5 rounded-xl font-bold text-sm transition flex items-center gap-2 whitespace-nowrap">
                <i class="fa-solid fa-right-left"></i> Mutasi Siswa
            </button>
            
            @if(Auth::user()->status_loket === 'tutup')
                <button onclick="toggleLoketStatus('buka')" class="bg-green-50 hover:bg-green-100 border border-green-200 text-green-700 px-4 py-2.5 rounded-xl font-bold text-sm transition shadow-sm animate-pulse flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-door-open"></i> Buka Loket
                </button>
            @else
                <button onclick="toggleLoketStatus('tutup')" class="bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 text-yellow-700 px-4 py-2.5 rounded-xl font-bold text-sm transition shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-door-closed"></i> Tutup Loket
                </button>
            @endif

            <form id="logoutForm" action="{{ route('logout') }}" method="POST" class="swal-form m-0" data-msg="Anda akan keluar dari pos meja loket/verifikasi.">
                @csrf
                <button type="submit" class="bg-red-600 text-white px-4 py-2.5 rounded-xl font-bold hover:bg-red-700 transition text-sm shadow-sm flex items-center gap-2 whitespace-nowrap">
                    <i class="fa-solid fa-power-off"></i> Logout
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch min-h-[70vh]">
        
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
            <div class="flex justify-between items-center border-b pb-3 mb-4 shrink-0">
                <h2 class="text-lg font-bold text-gray-800">Antrian Siap Dipanggil</h2>
                <div class="flex items-center gap-2">
                    <span id="loadingIndicator" class="hidden text-[10px] font-bold text-blue-500 animate-pulse"><i class="fa-solid fa-spinner fa-spin"></i> Memuat...</span>
                    <form id="a_form">
                        <select id="perPageSelect" name="a_per_page" class="border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold bg-gray-50 focus:ring-2 focus:ring-blue-400 outline-none cursor-pointer">
                            <option value="5" {{ request('a_per_page') == 5 || !request('a_per_page') ? 'selected' : '' }}>5 Baris</option>
                            <option value="10" {{ request('a_per_page') == 10 ? 'selected' : '' }}>10 Baris</option>
                            <option value="50" {{ request('a_per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                        </select>
                    </form>
                </div>
            </div>

            <div class="overflow-x-auto flex-1 mb-4">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-[11px] tracking-wider border-b-2 border-gray-200">
                            <th class="p-4 font-black rounded-tl-xl w-16 text-center">No</th>
                            <th class="p-4 font-black">NISN</th>
                            <th class="p-4 font-black">Nama Pendaftar</th>
                            <th class="p-4 font-black text-center">Status Buta Warna</th>
                            <th class="p-4 font-black text-center rounded-tr-xl w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="antrianBody">
                        @forelse($antrianHadir as $item)
                        <tr class="hover:bg-blue-50 border-b transition">
                            <td class="p-4 text-center font-black text-xl text-blue-600">{{ explode('-', $item->ticket_number)[0] }}</td>
                            <td class="p-4 font-mono font-bold text-gray-600 tracking-wider">{{ $item->nisn }}</td>
                            <td class="p-4 font-bold text-gray-800">{{ $item->nama_cpd }}</td>
                            
                            <td class="p-4 text-center">
                                @if($item->butawarna_status == 'normal')
                                    <span class="px-3 py-1.5 bg-green-100 text-green-700 text-[10px] font-black rounded-lg uppercase shadow-sm border border-green-200"><i class="fa-solid fa-check"></i> Normal</span>
                                @elseif($item->butawarna_status == 'terindikasi')
                                    <span class="px-3 py-1.5 bg-red-100 text-red-700 text-[10px] font-black rounded-lg uppercase shadow-sm border border-red-200"><i class="fa-solid fa-xmark"></i> Terindikasi</span>
                                @else
                                    <span class="px-3 py-1.5 bg-gray-100 text-gray-500 text-[10px] font-black rounded-lg uppercase shadow-sm border border-gray-200"><i class="fa-solid fa-clock"></i> Belum Tes</span>
                                @endif
                            </td>

                            <td class="p-4 text-center">
                                <button onclick="panggilTiket({{ $item->id }})" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2.5 rounded-lg shadow-md transition flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-microphone"></i> Panggil
                                </button>
                            </td>
                        </tr>
                        @empty 
                        <tr>
                            <td colspan="5" class="p-12 text-center text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="fa-solid fa-satellite-dish text-5xl mb-4 opacity-50 animate-pulse"></i>
                                    <span class="font-bold text-sm">Belum ada siswa yang Check-In di depan.</span>
                                </div>
                            </td>
                        </tr> 
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-auto border-t pt-4 shrink-0" id="antrianPagination">{{ $antrianHadir->links() }}</div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col h-full">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 shrink-0"><i class="fa-solid fa-desktop text-gray-400 mr-2"></i> Status Meja Anda</h2>
            <ul class="space-y-4 flex-1 overflow-y-auto pr-2 custom-scrollbar" id="mejaList">
                @forelse($riwayatHariIni as $riwayat)
                <li class="p-4 bg-gray-50 rounded-xl border transition-all {{ $riwayat->status == 'dipanggil' ? 'border-yellow-400 bg-yellow-50 shadow-md' : ($riwayat->status == 'menghadap' ? 'border-blue-400 bg-blue-50 shadow-md' : 'border-gray-200') }}">
                    <div class="flex justify-between items-center mb-3 border-b border-gray-200 pb-2">
                        <div>
                            <div class="font-black text-xl {{ $riwayat->status == 'dipanggil' ? 'text-yellow-700' : 'text-gray-800' }}">{{ explode('-', $riwayat->ticket_number)[0] }}</div>
                            <div class="text-sm font-bold text-gray-600">{{ $riwayat->nama_cpd }}</div>
                        </div>
                        @if($riwayat->status == 'dipanggil') <span class="text-xs bg-yellow-200 text-yellow-800 px-2 py-1 rounded font-bold animate-pulse shadow-sm">Menunggu ({{ $riwayat->call_count }}/3)</span>
                        @elseif($riwayat->status == 'menghadap') <span class="text-xs bg-blue-200 text-blue-800 px-2 py-1 rounded font-bold shadow-sm">Verifikasi Berkas</span>
                        @elseif($riwayat->status == 'selesai') <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-bold"><i class="fa-solid fa-check"></i> Selesai</span>
                        @else <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded font-bold"><i class="fa-solid fa-xmark"></i> Dilewati</span> @endif
                    </div>

                    @if($riwayat->status == 'selesai' && $riwayat->catatan_loket)
                        <div class="mb-3 p-2 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg text-xs font-bold shadow-inner">
                            <i class="fa-solid fa-note-sticky mr-1"></i> Catatan: {{ $riwayat->catatan_loket }}
                        </div>
                    @endif
                    
                    @if($riwayat->status == 'dipanggil')
                        <div class="flex gap-2 mb-2">
                            @if($riwayat->call_count < 3) <button onclick="panggilTiket({{ $riwayat->id }})" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-bold py-2 rounded-lg transition"><i class="fa-solid fa-bullhorn"></i> Ulang</button>
                            @else <button disabled class="flex-1 bg-gray-300 text-gray-500 text-xs font-bold py-2 rounded-lg cursor-not-allowed"><i class="fa-solid fa-microphone-slash"></i> Max Panggilan</button> @endif
                            <form action="{{ route('admin.menghadap') }}" method="POST" class="flex-1">@csrf <input type="hidden" name="ticket_id" value="{{ $riwayat->id }}"><button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-2 rounded-lg transition"><i class="fa-solid fa-user-check"></i> Menghadap</button></form>
                        </div>
                        <form action="{{ route('admin.lewati') }}" method="POST" class="swal-form" data-msg="Yakin ingin melewati siswa ini? Siswa tidak akan dilayani.">@csrf <input type="hidden" name="ticket_id" value="{{ $riwayat->id }}"><button type="submit" class="w-full bg-red-100 hover:bg-red-200 text-red-700 text-xs font-bold py-2 rounded-lg transition"><i class="fa-solid fa-forward-step"></i> Lewati / Tidak Hadir</button></form>
                    
                    @elseif($riwayat->status == 'menghadap')
                        <button type="button" onclick="tandaiSelesai({{ $riwayat->id }})" class="w-full bg-green-600 hover:bg-green-700 text-white text-sm font-bold py-3 rounded-lg transition shadow-md border-b-4 border-green-800"><i class="fa-solid fa-check-circle"></i> TANDAI SELESAI</button>
                    
                    @elseif($riwayat->status == 'dilewati')
                        <form action="{{ route('admin.restore') }}" method="POST" class="swal-form" data-msg="Siswa ini sudah datang? Mengembalikan siswa ke antrian awal?">@csrf <input type="hidden" name="ticket_id" value="{{ $riwayat->id }}"><button type="submit" class="w-full mt-1 bg-gray-700 hover:bg-gray-800 text-white text-xs font-bold py-2 rounded-lg transition shadow"><i class="fa-solid fa-rotate-left"></i> Kembalikan ke Antrian</button></form>
                    @endif
                </li>
                @empty <li class="text-sm text-gray-400 text-center py-8 font-medium italic">Meja masih kosong.</li> @endforelse
            </ul>
        </div>
    </div>
</div>

<div id="scannerLoketModal" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white p-6 rounded-3xl w-full max-w-md shadow-2xl relative">
        <button onclick="tutupScannerLoket()" class="absolute top-5 right-5 w-8 h-8 flex items-center justify-center rounded-full bg-gray-100 text-gray-500 hover:bg-red-100 hover:text-red-600 transition"><i class="fa-solid fa-xmark text-lg"></i></button>
        
        <h3 class="text-2xl font-black text-gray-800 mb-1">Scanner Penerimaan Map</h3>
        <p class="text-sm text-gray-500 mb-5">Arahkan kamera ke QR Code di sampul depan Map Siswa untuk menerima berkas.</p>

        <div id="berkas-reader-box" class="w-full overflow-hidden rounded-2xl border-4 border-green-400 mb-4 bg-black flex items-center justify-center relative shadow-inner" style="min-height: 250px;">
            <i class="fa-solid fa-qrcode text-6xl text-gray-600 absolute z-0 opacity-40"></i>
            <div id="berkas-reader" class="w-full relative z-10"></div>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <hr class="flex-1 border-gray-300"><span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Atau Ketik Manual</span><hr class="flex-1 border-gray-300">
        </div>
        
        <div class="flex items-center gap-2 mt-3">
            <input id="berkas-input" type="number" oninput="if(this.value.length > 10) this.value = this.value.slice(0, 10);" class="w-full text-center text-lg font-black tracking-widest rounded-xl border border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-200 p-3 bg-gray-50 outline-none transition" placeholder="Ketik NISN..." autocomplete="off">
            <button id="btnVerifManual" class="bg-green-600 hover:bg-green-700 text-white px-5 py-3 rounded-xl font-bold transition shadow-md flex items-center justify-center">
                <i class="fa-solid fa-check-double text-lg"></i>
            </button>
        </div>
    </div>
</div>

<div id="callingModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-2xl text-center shadow-2xl border-t-4 border-blue-600">
        <div class="text-blue-600 text-6xl mb-4 animate-bounce"><i class="fa-solid fa-satellite-dish"></i></div>
        <h3 class="text-2xl font-black mb-2 text-gray-800">Mengirim Sinyal...</h3>
        <p class="text-gray-500 text-sm font-medium">Layar Aula akan segera memanggil siswa.</p>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
    let isActionActive = false;
    let currentSpaUrl = window.location.href;
    let bwHtml5QrCode = null;

    document.body.addEventListener('submit', function(e) {
        if (e.target && e.target.classList.contains('swal-form')) {
            e.preventDefault();
            let msg = e.target.getAttribute('data-msg');
            isActionActive = true; 
            Swal.fire({
                title: 'Konfirmasi Tindakan', text: msg, icon: 'warning',
                showCancelButton: true, confirmButtonColor: '#2563eb', cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Lanjutkan!', cancelButtonText: 'Batal'
            }).then((result) => { 
                if (result.isConfirmed) { e.target.submit(); } else { isActionActive = false; }
            });
        }
    });

    function tandaiSelesai(ticketId) {
        isActionActive = true;
        Swal.fire({
            title: 'Selesaikan Verifikasi?',
            text: 'Tambahkan catatan jika berkas ada yang kurang (Kosongkan jika sudah lengkap sempurna).',
            input: 'textarea',
            inputPlaceholder: 'Ketik catatan di sini...',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fa-solid fa-check"></i> Simpan & Selesai',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('admin.selesaikan') }}";

                let csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = "{{ csrf_token() }}";
                form.appendChild(csrf);

                let tId = document.createElement('input');
                tId.type = 'hidden';
                tId.name = 'ticket_id';
                tId.value = ticketId;
                form.appendChild(tId);

                let catatan = document.createElement('input');
                catatan.type = 'hidden';
                catatan.name = 'catatan_loket';
                catatan.value = result.value || '';
                form.appendChild(catatan);

                document.body.appendChild(form);
                form.submit();
            } else {
                isActionActive = false;
            }
        });
    }

    // ==========================================================
    // 🔥 ENGINE SCANNER LOKET (KHUSUS TERIMA MAP) 🔥
    // ==========================================================
    let l_html5QrCode = null;
    let l_isProcessing = false;

    function bukaScannerBerkas() {
        isActionActive = true; 
        document.getElementById('scannerLoketModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('berkas-input').focus(), 100);
        
        // Mulai kamera otomatis
        const config = { fps: 10, qrbox: { width: 200, height: 200 } };
        l_html5QrCode = new Html5Qrcode("berkas-reader");
        
        l_html5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
            l_html5QrCode.pause(); 
            document.getElementById('berkas-input').value = decodedText;
            document.getElementById('btnVerifManual').click(); 
        }).catch(err => {
            l_html5QrCode.start({ facingMode: "user" }, config, (decodedText) => {
                l_html5QrCode.pause();
                document.getElementById('berkas-input').value = decodedText;
                document.getElementById('btnVerifManual').click(); 
            }).catch(err2 => {
                console.log("Kamera tidak ditemukan. Mode ketik manual aktif.");
            });
        });
    }

    function tutupScannerLoket() {
        if(l_html5QrCode && l_html5QrCode.isScanning) {
            l_html5QrCode.stop().catch(e => console.error(e));
        }
        document.getElementById('scannerLoketModal').classList.add('hidden');
        document.getElementById('berkas-input').value = '';
        isActionActive = false; 
    }

    function prosesTerimaBerkas(nisnScan) {
        if(l_isProcessing) return;
        l_isProcessing = true;

        Swal.fire({ title: 'Menyinkronkan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        fetch("{{ route('admin.pengumuman.scan_berkas') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ nisn: nisnScan })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let audio = new Audio('https://www.soundjay.com/buttons/sounds/button-09.mp3'); audio.play();
                if (data.already_scanned) {
                    Swal.fire({ title: 'SUDAH DITERIMA!', text: data.message, icon: 'info', confirmButtonColor: '#3b82f6', confirmButtonText: 'Oke, Lanjut' })
                    .then(() => { if (l_html5QrCode && l_html5QrCode.isScanning) l_html5QrCode.resume(); l_isProcessing = false; document.getElementById('berkas-input').value = ''; setTimeout(() => document.getElementById('berkas-input').focus(), 100); });
                } else {
                    Swal.fire({
                        title: 'MAP DITERIMA!', 
                        html: `<div class="bg-green-100 border border-green-300 p-4 rounded-xl text-green-800 text-sm mt-2"><b>${data.message}</b><br><br>Silakan masukkan Map ke keranjang panitia sesuai warna jurusannya.</div>`,
                        icon: 'success', confirmButtonColor: '#10b981', confirmButtonText: '<i class="fa-solid fa-camera"></i> Lanjut Scan'
                    }).then(() => { if (l_html5QrCode && l_html5QrCode.isScanning) l_html5QrCode.resume(); l_isProcessing = false; document.getElementById('berkas-input').value = ''; setTimeout(() => document.getElementById('berkas-input').focus(), 100); });
                }
            } else {
                let audio = new Audio('https://www.soundjay.com/buttons/sounds/button-10.mp3'); audio.play();
                Swal.fire({ title: 'DITOLAK!', text: data.message, icon: 'error', confirmButtonColor: '#ef4444' })
                .then(() => { if (l_html5QrCode && l_html5QrCode.isScanning) l_html5QrCode.resume(); l_isProcessing = false; document.getElementById('berkas-input').value = ''; setTimeout(() => document.getElementById('berkas-input').focus(), 100); });
            }
        }).catch(err => {
            Swal.fire('Error', 'Koneksi Terputus.', 'error').then(() => { if(l_html5QrCode && l_html5QrCode.isScanning) l_html5QrCode.resume(); l_isProcessing = false; });
        });
    }

    document.getElementById('btnVerifManual').addEventListener('click', function() {
        const val = document.getElementById('berkas-input').value.trim();
        if(val) {
            prosesTerimaBerkas(val);
        } else {
            Swal.fire('Peringatan', 'Harap masukkan NISN terlebih dahulu.', 'warning');
        }
    });

    document.getElementById('berkas-input').addEventListener('keypress', function(e) { 
        if(e.key === 'Enter') document.getElementById('btnVerifManual').click(); 
    });


    // ==========================================================
    // SPA LIVE RADAR & EVENT LISTENERS LAINNYA
    // ==========================================================
    function fetchLive(url) {
        currentSpaUrl = url;
        const loader = document.getElementById('loadingIndicator');
        if(loader) loader.classList.remove('hidden');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newAntrian = doc.getElementById('antrianBody');
            if(newAntrian) document.getElementById('antrianBody').innerHTML = newAntrian.innerHTML;
            const newPagination = doc.getElementById('antrianPagination');
            if(newPagination) { document.getElementById('antrianPagination').innerHTML = newPagination.innerHTML; attachPaginationEvents(); }
            const newMeja = doc.getElementById('mejaList');
            if(newMeja) document.getElementById('mejaList').innerHTML = newMeja.innerHTML;
            if(loader) loader.classList.add('hidden');
            window.history.replaceState({}, document.title, window.location.pathname);
        })
        .catch(error => { console.error('AJAX Error:', error); if(loader) loader.classList.add('hidden'); });
    }

    function triggerFilter() {
        const url = new URL(window.location.pathname, window.location.origin);
        const perPage = document.getElementById('perPageSelect').value;
        if (perPage) url.searchParams.set('a_per_page', perPage);
        fetchLive(url.toString());
    }

    function attachPaginationEvents() {
        document.querySelectorAll('#antrianPagination a').forEach(link => {
            link.addEventListener('click', function(e) { e.preventDefault(); fetchLive(this.href); });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        attachPaginationEvents();
        const perPageSelect = document.getElementById('perPageSelect');
        if (perPageSelect) perPageSelect.addEventListener('change', triggerFilter);
    });

    function panggilTiket(id) {
        isActionActive = true;
        document.getElementById('callingModal').classList.remove('hidden');
        fetch("{{ route('admin.panggil') }}", { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ ticket_id: id }) })
        .then(res => res.json()).then(data => { if(data.success) { setTimeout(() => { location.reload(); }, 2000); } })
        .catch(err => { console.error("Fetch error: ", err); location.reload(); });
    }

    function toggleLoketStatus(action) {
        isActionActive = true;
        if (action === 'tutup') {
            Swal.fire({
                title: 'Tutup Loket Sementara', text: "Masukkan alasan tutup loket (cth: ISTIRAHAT):", input: 'text', inputValue: 'ISTIRAHAT',
                showCancelButton: true, confirmButtonText: '<i class="fa-solid fa-door-closed"></i> Tutup Sekarang', cancelButtonText: 'Batal', confirmButtonColor: '#eab308', showLoaderOnConfirm: true,
                preConfirm: (alasan) => {
                    if(!alasan) { Swal.showValidationMessage('Alasan tidak boleh kosong!'); return false; }
                    return fetch(`{{ route('admin.toggle_loket') }}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ keterangan: alasan }) }).then(res => res.json());
                }
            }).then((result) => {
                if (result.isConfirmed && result.value.success) Swal.fire('Loket Ditutup!', result.value.message, 'success').then(() => location.reload());
                else isActionActive = false;
            });
        } else {
            Swal.fire({
                title: 'Buka Loket', text: "Anda siap menerima antrian kembali?", icon: 'question',
                showCancelButton: true, confirmButtonText: '<i class="fa-solid fa-door-open"></i> Buka Sekarang', cancelButtonText: 'Batal', confirmButtonColor: '#10b981',
            }).then((result) => {
                if(result.isConfirmed) {
                    fetch(`{{ route('admin.toggle_loket') }}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' } })
                    .then(res => res.json()).then(data => { if(data.success) Swal.fire('Loket Aktif!', data.message, 'success').then(() => location.reload()); });
                } else { isActionActive = false; }
            });
        }
    }

    function bukaMutasi() {
        isActionActive = true;
        Swal.fire({
            title: 'Mutasi Siswa', text: "Masukkan NISN Siswa yang tertinggal/maju jadwal:", input: 'number', inputAttributes: { placeholder: 'Ketik NISN di sini...' },
            showCancelButton: true, confirmButtonText: '<i class="fa-solid fa-search"></i> Cari Data', cancelButtonText: 'Batal', confirmButtonColor: '#2563eb', showLoaderOnConfirm: true,
            preConfirm: (nisn) => {
                if(!nisn) { Swal.showValidationMessage('NISN tidak boleh kosong!'); return false; }
                return fetch(`{{ route('admin.cari_nisn') }}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ nisn: nisn }) })
                .then(response => response.json()).catch(error => { Swal.showValidationMessage(`Request failed: ${error}`); });
            }, allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                if(result.value.success) {
                    let s = result.value.data;
                    let warningHtml = result.value.is_today_full ? `<div class="bg-red-100 border border-red-400 text-red-700 p-3 rounded-lg mt-3 text-xs font-black animate-pulse">PERHATIAN: Kuota Hari Ini sudah PENUH.</div>` : '';
                    Swal.fire({
                        title: 'Data Ditemukan!', html: `<div class="text-left text-sm space-y-2 bg-gray-50 p-4 rounded-lg border mt-2 mb-2 shadow-inner"><p><b>NISN:</b> ${s.nisn}</p><p><b>Nama:</b> ${s.nama_cpd}</p><p><b>Status Jadwal:</b> <span class="text-red-600 font-black">${result.value.status_jadwal}</span></p></div>${warningHtml}`, icon: 'info',
                        showCancelButton: true, confirmButtonColor: '#10b981', cancelButtonColor: '#6b7280', confirmButtonText: 'Proses Mutasi', cancelButtonText: 'Batal'
                    }).then((mRes) => {
                        if (mRes.isConfirmed) {
                            fetch(`{{ route('admin.mutasi') }}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ ticket_id: s.id }) })
                            .then(res => res.json()).then(mData => { if(mData.success) Swal.fire('Berhasil!', mData.message, 'success').then(() => location.reload()); else Swal.fire('Gagal!', mData.message, 'error').then(() => { isActionActive = false; }); });
                        } else { isActionActive = false; }
                    });
                } else { Swal.fire('Peringatan', result.value.message, 'warning').then(() => { isActionActive = false; }); }
            } else { isActionActive = false; }
        });
    }

    function processCekButaWarna(inputData) {
        Swal.fire({
            title: 'Memproses Data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }
        });

        let finalInput = inputData;
        if(inputData.includes('/ticket/download/')) {
            let parts = inputData.split('/');
            finalInput = parts[parts.length - 1];
        }

        fetch(`{{ route('admin.cek_butawarna') }}`, {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, body: JSON.stringify({ input_data: finalInput })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let d = data.data;
                let isNormal = d.status === 'normal';
                let statusColor = isNormal ? 'text-green-600' : 'text-red-600';
                let statusBg = isNormal ? 'bg-green-100 border-green-200' : 'bg-red-100 border-red-200';
                let statusIcon = isNormal ? '<i class="fa-solid fa-check-circle"></i>' : '<i class="fa-solid fa-triangle-exclamation"></i>';
                
                let htmlContent = `<div class="text-left text-sm space-y-2 bg-gray-50 p-5 rounded-2xl border shadow-inner"><p class="text-gray-500 font-bold mb-2">IDENTITAS PESERTA</p><p class="text-lg font-black text-gray-800 uppercase">${d.nama}</p><p class="font-mono font-bold text-gray-600 border-b pb-3">${d.nisn}</p><div class="mt-4 p-4 rounded-xl ${statusBg} border text-center"><p class="text-xs font-bold text-gray-600 mb-1 tracking-widest uppercase">Hasil Pemeriksaan Visual</p><h4 class="text-2xl font-black ${statusColor} mb-1">${statusIcon} ${d.status.toUpperCase()}</h4><p class="text-sm font-bold text-gray-800">${d.keterangan}</p><div class="mt-3 inline-block bg-white/50 px-3 py-1 rounded-lg text-xs font-bold text-gray-600 border border-white/60 shadow-sm">Skor Jawab: ${d.score} / 10</div></div></div>`;

                Swal.fire({
                    title: 'Hasil Pemeriksaan', html: htmlContent, showCancelButton: true, cancelButtonText: 'Tutup',
                    confirmButtonText: isNormal ? '<i class="fa-solid fa-print"></i> Cetak PDF' : '<i class="fa-solid fa-times"></i> Mengerti', confirmButtonColor: isNormal ? '#16a34a' : '#9ca3af',
                }).then((printRes) => {
                    if (printRes.isConfirmed && isNormal) { window.open(data.print_url, '_blank'); }
                    isActionActive = false;
                });
            } else {
                Swal.fire({ title: 'Tidak Ditemukan', text: data.message, icon: 'warning' }).then(() => { isActionActive = false; });
            }
        }).catch(err => { Swal.fire('Error', 'Gagal memproses data atau koneksi terputus.', 'error').then(() => { isActionActive = false; }); });
    }

    function bukaCekButaWarna() {
        isActionActive = true;
        Swal.fire({
            title: 'Scanner Buta Warna',
            html: `
                <div class="text-sm text-gray-500 mb-4">Arahkan Scanner ke Barcode Tiket, atau ketik NISN manual.</div>
                <div id="bw-reader-box" class="hidden w-full overflow-hidden rounded-xl border-4 border-blue-100 mb-4 bg-black aspect-square flex items-center justify-center relative"><i id="bw-placeholder-icon" class="fa-solid fa-qrcode text-5xl text-gray-600 absolute z-0 opacity-50"></i><div id="bw-reader" class="w-full relative z-10"></div></div>
                <input id="bw-input" type="number" oninput="if(this.value.length > 10) this.value = this.value.slice(0, 10);" class="w-full text-center text-xl font-black tracking-widest rounded-xl border-gray-300 shadow-inner focus:border-indigo-500 focus:ring-indigo-500 p-4 border bg-gray-50 outline-none mb-4" placeholder="Ketik NISN..." autocomplete="off">
                <button id="btnBukaKamera" type="button" class="w-full bg-slate-800 text-white font-bold py-3 px-4 rounded-xl shadow-md hover:bg-slate-900 transition flex items-center justify-center gap-2"><i class="fa-solid fa-camera"></i> Gunakan Kamera HP</button>
            `,
            showCancelButton: true, confirmButtonText: '<i class="fa-solid fa-search"></i> Cek Data', cancelButtonText: 'Batal', confirmButtonColor: '#4f46e5',
            didOpen: () => {
                setTimeout(() => document.getElementById('bw-input').focus(), 100);
                document.getElementById('bw-input').addEventListener('keypress', function(e) { if(e.key === 'Enter') Swal.clickConfirm(); });

                document.getElementById('btnBukaKamera').addEventListener('click', function() {
                    const box = document.getElementById('bw-reader-box'); const input = document.getElementById('bw-input'); const btn = document.getElementById('btnBukaKamera');
                    box.classList.remove('hidden'); input.classList.add('hidden'); btn.classList.add('hidden');

                    bwHtml5QrCode = new Html5Qrcode("bw-reader");
                    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                    bwHtml5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
                        bwHtml5QrCode.stop().then(() => { Swal.close(); processCekButaWarna(decodedText); });
                    }).catch(err => {
                        console.log("Mencoba kamera depan...", err);
                        bwHtml5QrCode.start({ facingMode: "user" }, config, (decodedText) => {
                            bwHtml5QrCode.stop().then(() => { Swal.close(); processCekButaWarna(decodedText); });
                        }).catch(err2 => {
                            Swal.fire({ title: 'Kamera Tidak Ditemukan', text: 'Pastikan izin kamera di browser (Site Settings) telah di-Allow.', icon: 'error', confirmButtonColor: '#2563eb' }).then(() => { box.classList.add('hidden'); input.classList.remove('hidden'); btn.classList.remove('hidden'); });
                        });
                    });
                });
            },
            willClose: () => { if(bwHtml5QrCode && bwHtml5QrCode.isScanning) { bwHtml5QrCode.stop().catch(e => console.error(e)); } },
            preConfirm: () => {
                const val = document.getElementById('bw-input').value.trim();
                if (!val) { Swal.showValidationMessage('Input tidak boleh kosong!'); return false; }
                return val;
            }
        }).then((result) => { if (result.isConfirmed) { processCekButaWarna(result.value); } else { isActionActive = false; } });
    }

    setInterval(() => {
        if (isActionActive || Swal.isVisible()) return;
        fetch(currentSpaUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newAntrian = doc.getElementById('antrianBody');
            const oldAntrian = document.getElementById('antrianBody');
            if(newAntrian && oldAntrian) oldAntrian.innerHTML = newAntrian.innerHTML;
            const newPagination = doc.getElementById('antrianPagination');
            const oldPagination = document.getElementById('antrianPagination');
            if(newPagination && oldPagination) { oldPagination.innerHTML = newPagination.innerHTML; attachPaginationEvents(); }
            const newMeja = doc.getElementById('mejaList');
            const oldMeja = document.getElementById('mejaList');
            if(newMeja && oldMeja) oldMeja.innerHTML = newMeja.innerHTML;
        })
        .catch(error => console.error('Gagal memuat radar:', error));
    }, 5000); 
</script>
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #1f2937; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #4b5563; }
</style>
@endpush