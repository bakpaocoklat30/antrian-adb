@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto w-full flex flex-col">
    
    <div class="flex justify-between items-center bg-gray-900 p-6 rounded-2xl shadow-lg border border-gray-800 mb-6 shrink-0">
        <div><h1 class="text-2xl font-black text-white"><i class="fa-solid fa-crown text-yellow-400 mr-2"></i> Portal Super Admin</h1></div>
        <form id="logoutForm" action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="button" onclick="confirmLogout()" class="bg-gray-800 text-white px-4 py-2 rounded-lg font-bold hover:bg-red-600 transition">
                <i class="fa-solid fa-power-off"></i> Logout
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-xl mb-6 font-bold shadow-sm">
            <i class="fa-solid fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col min-h-[70vh]">
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 shrink-0">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl mr-4 shrink-0"><i class="fa-solid fa-ticket"></i></div>
                <div><p class="text-sm text-gray-500 font-bold">Total Pendaftar</p><p class="text-2xl font-black text-gray-800">{{ $stats['total'] }} Siswa</p></div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center text-xl mr-4 shrink-0"><i class="fa-solid fa-school"></i></div>
                <div><p class="text-sm text-gray-500 font-bold">Hadir di Lokasi</p><p class="text-2xl font-black text-gray-800">{{ $stats['hadir'] }} Siswa</p></div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl mr-4 shrink-0"><i class="fa-solid fa-check-double"></i></div>
                <div><p class="text-sm text-gray-500 font-bold">Selesai Dilayani</p><p class="text-2xl font-black text-gray-800">{{ $stats['selesai'] }} Siswa</p></div>
            </div>
        </div>

        <h2 class="text-lg font-bold text-gray-800 mb-4 shrink-0"><i class="fa-solid fa-grid-2 text-blue-600 mr-2"></i> Menu Navigasi Utama</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 shrink-0">

            <a href="{{ route('admin.pengumuman.index') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">
                    <i class="fa-solid fa-graduation-cap"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Kelulusan SPMB</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Import CSV & Data Pendaftar.</p>
                </div>
            </a>
            
            <button onclick="openSpmbModal()" class="group bg-gradient-to-br from-gray-900 to-gray-800 p-4 rounded-xl shadow-lg border border-gray-700 hover:shadow-xl hover:border-yellow-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-gray-800 text-yellow-400 border border-gray-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-white mb-0.5">Akses Publik SPMB</h2>
                    <p class="text-gray-400 text-[10px] leading-tight">Atur Waktu Buka & Gembok Web.</p>
                </div>
            </button>

            <button onclick="bukaCekButaWarna()" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-teal-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-teal-50 text-teal-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Cek Buta Warna</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Scan QR / Ketik NISN Peserta.</p>
                </div>
            </button>

            <a href="{{ route('admin.manage_sessions') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition"><i class="fa-solid fa-calendar-alt"></i></div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Manajemen Sesi</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Atur Kalender & Kuota.</p>
                </div>
            </a>

            <a href="{{ route('admin.manage_users') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-green-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-green-50 text-green-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition"><i class="fa-solid fa-users-gear"></i></div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Manajemen Akun</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Kelola Petugas / OSIS.</p>
                </div>
            </a>

            <a href="{{ route('admin.manage_display') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition"><i class="fa-solid fa-tv"></i></div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Pengaturan Layar</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Teks & Video Proyektor.</p>
                </div>
            </a>
       
            <a href="{{ route('admin.attendance') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-orange-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">
                    <i class="fa-solid fa-clipboard-user"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Pantau Kehadiran</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Lacak siswa belum hadir.</p>
                </div>
            </a>

            <a href="{{ route('admin.backup.index') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-red-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-red-50 text-red-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">
                    <i class="fa-solid fa-database"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Backup / Restore</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Amankan & Pulihkan Database.</p>
                </div>
            </a>
            
        </div>
    </div>
</div>

<div id="spmbModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden flex items-center justify-center z-50 px-4">
    <div class="bg-white p-8 rounded-3xl w-full max-w-lg text-left relative shadow-2xl border-t-8 border-gray-900 overflow-y-auto max-h-[95vh]">
        <button onclick="closeSpmbModal()" class="absolute top-5 right-5 text-gray-400 hover:text-red-500 transition bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center"><i class="fa-solid fa-times"></i></button>
        
        <h3 class="text-2xl font-black text-gray-800 mb-2"><i class="fa-solid fa-lock text-gray-600 mr-2"></i> Akses Publik SPMB</h3>
        <p class="text-sm text-gray-500 mb-6 font-semibold">Atur kapan form pendaftaran dan generator dokumen di halaman depan mulai dibuka.</p>
        
        <form action="{{ route('admin.spmb_access') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl">
                <label class="block text-sm font-bold text-blue-800 mb-2"><i class="fa-solid fa-clock mr-1"></i> Waktu Pendaftaran / Pengumuman Dibuka</label>
                <input type="datetime-local" name="spmb_open_at" value="{{ (isset($setting) && $setting->spmb_open_at) ? \Carbon\Carbon::parse($setting->spmb_open_at)->format('Y-m-d\TH:i') : '' }}" class="w-full p-3 bg-white border border-blue-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 font-bold text-gray-800">
                <p class="text-[11px] text-blue-600 mt-2 font-semibold">*) Isikan target tanggal dan jam dibukanya pengumuman kelulusan serentak.</p>
            </div>

            <div class="space-y-3">
                <p class="text-sm font-bold text-gray-800 border-b pb-2">Status Sistem Antrian:</p>
                
                <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 {{ (!$setting || ($setting->is_maintenance == 0 && $setting->is_ticket_exhausted == 0 && (!isset($setting->is_pengumuman) || $setting->is_pengumuman == 0))) ? 'border-green-400 bg-green-50 shadow-inner' : 'border-gray-200 bg-white' }}">
                    <input type="radio" name="system_status" value="normal" {{ (!$setting || ($setting->is_maintenance == 0 && $setting->is_ticket_exhausted == 0 && (!isset($setting->is_pengumuman) || $setting->is_pengumuman == 0))) ? 'checked' : '' }} class="mt-1 w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300">
                    <div>
                        <span class="block text-sm font-black text-green-800 mb-1">NORMAL (SESUAI JADWAL)</span>
                        <span class="block text-xs font-semibold text-green-600 leading-tight">Sistem berjalan normal mengikuti waktu buka pendaftaran yang diatur di atas.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 {{ (isset($setting) && isset($setting->is_pengumuman) && $setting->is_pengumuman == 1) ? 'border-blue-400 bg-blue-50 shadow-inner' : 'border-gray-200 bg-white' }}">
                    <input type="radio" name="system_status" value="pengumuman" {{ (isset($setting) && isset($setting->is_pengumuman) && $setting->is_pengumuman == 1) ? 'checked' : '' }} class="mt-1 w-5 h-5 text-blue-600 focus:ring-blue-500 border-gray-300">
                    <div>
                        <span class="block text-sm font-black text-blue-800 mb-1">TUTUP PAKSA (PENGUMUMAN KELULUSAN)</span>
                        <span class="block text-xs font-semibold text-blue-600 leading-tight">Form antrian ditutup total, hanya menampilkan jam hitung mundur pengumuman kelulusan. Jika waktu habis, layar siswa otomatis redirect ke gerbang /welcome-adb.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 {{ (isset($setting) && $setting->is_ticket_exhausted == 1 && (!isset($setting->is_pengumuman) || $setting->is_pengumuman == 0)) ? 'border-yellow-400 bg-yellow-50 shadow-inner' : 'border-gray-200 bg-white' }}">
                    <input type="radio" name="system_status" value="exhausted" {{ (isset($setting) && $setting->is_ticket_exhausted == 1 && (!isset($setting->is_pengumuman) || $setting->is_pengumuman == 0)) ? 'checked' : '' }} class="mt-1 w-5 h-5 text-yellow-600 focus:ring-yellow-500 border-gray-300">
                    <div>
                        <span class="block text-sm font-black text-yellow-800 mb-1">TUTUP PAKSA (TIKET HABIS / BELUM DIBUKA)</span>
                        <span class="block text-xs font-semibold text-yellow-700 leading-tight">Form antrian ditutup total. Namun fitur <b class="font-black text-yellow-900 border-b border-yellow-400">Generator Dokumen</b> tetap bisa diakses oleh siswa dari rumah.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 {{ (isset($setting) && $setting->is_maintenance == 1) ? 'border-red-400 bg-red-50 shadow-inner' : 'border-gray-200 bg-white' }}">
                    <input type="radio" name="system_status" value="maintenance" {{ (isset($setting) && $setting->is_maintenance == 1) ? 'checked' : '' }} class="mt-1 w-5 h-5 text-red-600 focus:ring-red-500 border-gray-300">
                    <div>
                        <span class="block text-sm font-black text-red-800 mb-1">TUTUP PAKSA (MAINTENANCE TOTAL)</span>
                        <span class="block text-xs font-semibold text-red-600 leading-tight">Seluruh halaman depan (baik form antrian maupun generator) ditutup total dari akses publik.</span>
                    </div>
                </label>
            </div>

            <button type="submit" class="w-full bg-gray-900 text-white font-black text-lg py-4 rounded-xl hover:bg-black transition shadow-md mt-4">
                <i class="fa-solid fa-save mr-2"></i> Simpan Pengaturan
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    let bwHtml5QrCode = null;

    function confirmLogout() {
        Swal.fire({
            title: 'Keluar dari Sistem?',
            text: "Anda akan mengakhiri sesi Super Admin saat ini.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e3342f',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Logout!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logoutForm').submit();
            }
        });
    }

    function openSpmbModal() { document.getElementById('spmbModal').classList.remove('hidden'); }
    function closeSpmbModal() { document.getElementById('spmbModal').classList.add('hidden'); }

    function processCekButaWarna(inputData) {
        Swal.fire({ title: 'Memproses Data...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

        let finalInput = inputData;
        if(inputData.includes('/ticket/download/')) {
            let parts = inputData.split('/');
            finalInput = parts[parts.length - 1];
        }

        fetch(`{{ route('admin.cek_butawarna') }}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ input_data: finalInput })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                let d = data.data;
                let isNormal = d.status === 'normal';
                let statusColor = isNormal ? 'text-green-600' : 'text-red-600';
                let statusBg = isNormal ? 'bg-green-100 border-green-200' : 'bg-red-100 border-red-200';
                let statusIcon = isNormal ? '<i class="fa-solid fa-check-circle"></i>' : '<i class="fa-solid fa-triangle-exclamation"></i>';
                
                let htmlContent = `
                    <div class="text-left text-sm space-y-2 bg-gray-50 p-5 rounded-2xl border shadow-inner">
                        <p class="text-gray-500 font-bold mb-2">IDENTITAS PESERTA</p>
                        <p class="text-lg font-black text-gray-800 uppercase">${d.nama}</p>
                        <p class="font-mono font-bold text-gray-600 border-b pb-3">${d.nisn}</p>
                        <div class="mt-4 p-4 rounded-xl ${statusBg} border text-center">
                            <p class="text-xs font-bold text-gray-600 mb-1 tracking-widest uppercase">Hasil Pemeriksaan Visual</p>
                            <h4 class="text-2xl font-black ${statusColor} mb-1">${statusIcon} ${d.status.toUpperCase()}</h4>
                            <p class="text-sm font-bold text-gray-800">${d.keterangan}</p>
                            <div class="mt-3 inline-block bg-white/50 px-3 py-1 rounded-lg text-xs font-bold text-gray-600 border border-white/60 shadow-sm">
                                Skor Jawab: ${d.score} / 10
                            </div>
                        </div>
                    </div>
                `;

                Swal.fire({
                    title: 'Hasil Pemeriksaan', html: htmlContent, showCancelButton: true, cancelButtonText: 'Tutup',
                    confirmButtonText: isNormal ? '<i class="fa-solid fa-print"></i> Cetak PDF' : '<i class="fa-solid fa-times"></i> Mengerti',
                    confirmButtonColor: isNormal ? '#16a34a' : '#9ca3af',
                }).then((printRes) => { if (printRes.isConfirmed && isNormal) window.open(data.print_url, '_blank'); });
            } else {
                Swal.fire({ title: 'Tidak Ditemukan', text: data.message, icon: 'warning' });
            }
        }).catch(err => { Swal.fire('Error', 'Gagal memproses data atau koneksi terputus.', 'error'); });
    }

    function bukaCekButaWarna() {
        Swal.fire({
            title: 'Scanner Buta Warna',
            html: `
                <div class="text-sm text-gray-500 mb-4">Arahkan Scanner ke Barcode Tiket, atau ketik NISN manual.</div>
                <div id="bw-reader-box" class="hidden w-full overflow-hidden rounded-xl border-4 border-blue-100 mb-4 bg-black aspect-square flex items-center justify-center relative">
                    <i id="bw-placeholder-icon" class="fa-solid fa-qrcode text-5xl text-gray-600 absolute z-0 opacity-50"></i>
                    <div id="bw-reader" class="w-full relative z-10"></div>
                </div>
                <input id="bw-input" class="w-full text-center text-xl font-black tracking-widest rounded-xl border-gray-300 shadow-inner focus:border-indigo-500 focus:ring-indigo-500 p-4 border bg-gray-50 outline-none mb-4" placeholder="Ketik NISN..." autocomplete="off">
                <button id="btnBukaKamera" type="button" class="w-full bg-slate-800 text-white font-bold py-3 px-4 rounded-xl shadow-md hover:bg-slate-900 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-camera"></i> Gunakan Kamera HP
                </button>
            `,
            showCancelButton: true, confirmButtonText: '<i class="fa-solid fa-search"></i> Cek Data', cancelButtonText: 'Batal', confirmButtonColor: '#4f46e5',
            didOpen: () => {
                setTimeout(() => document.getElementById('bw-input').focus(), 100);
                document.getElementById('bw-input').addEventListener('keypress', function(e) { if(e.key === 'Enter') Swal.clickConfirm(); });

                document.getElementById('btnBukaKamera').addEventListener('click', function() {
                    const box = document.getElementById('bw-reader-box');
                    const input = document.getElementById('bw-input');
                    const btn = document.getElementById('btnBukaKamera');
                    
                    box.classList.remove('hidden'); input.classList.add('hidden'); btn.classList.add('hidden');
                    bwHtml5QrCode = new Html5Qrcode("bw-reader");
                    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

                    bwHtml5QrCode.start({ facingMode: "environment" }, config, (decodedText) => {
                        bwHtml5QrCode.stop().then(() => { Swal.close(); processCekButaWarna(decodedText); });
                    }).catch(err => {
                        bwHtml5QrCode.start({ facingMode: "user" }, config, (decodedText) => {
                            bwHtml5QrCode.stop().then(() => { Swal.close(); processCekButaWarna(decodedText); });
                        }).catch(err2 => {
                            Swal.fire({ title: 'Kamera Tidak Ditemukan', text: 'Pastikan izin kamera di browser telah di-Allow.', icon: 'error', confirmButtonColor: '#2563eb' }).then(() => {
                                box.classList.add('hidden'); input.classList.remove('hidden'); btn.classList.remove('hidden');
                            });
                        });
                    });
                });
            },
            willClose: () => { if(bwHtml5QrCode && bwHtml5QrCode.isScanning) bwHtml5QrCode.stop().catch(e => console.error(e)); },
            preConfirm: () => {
                const val = document.getElementById('bw-input').value.trim();
                if (!val) { Swal.showValidationMessage('Input tidak boleh kosong!'); return false; }
                return val;
            }
        }).then((result) => { if (result.isConfirmed) processCekButaWarna(result.value); });
    }
</script>
@endpush