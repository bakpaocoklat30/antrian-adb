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

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 shrink-0">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl mr-4 shrink-0"><i class="fa-solid fa-layer-group"></i></div>
                <div><p class="text-sm text-gray-500 font-bold">Event Aktif</p><p class="text-2xl font-black text-gray-800">{{ $stats['events_active'] }} Event</p></div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl mr-4 shrink-0"><i class="fa-solid fa-ticket"></i></div>
                <div><p class="text-sm text-gray-500 font-bold">Total Pendaftar</p><p class="text-2xl font-black text-gray-800">{{ $stats['total'] }} Orang</p></div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center text-xl mr-4 shrink-0"><i class="fa-solid fa-school"></i></div>
                <div><p class="text-sm text-gray-500 font-bold">Hadir di Lokasi</p><p class="text-2xl font-black text-gray-800">{{ $stats['hadir'] }} Orang</p></div>
            </div>
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center">
                <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center text-xl mr-4 shrink-0"><i class="fa-solid fa-check-double"></i></div>
                <div><p class="text-sm text-gray-500 font-bold">Selesai Dilayani</p><p class="text-2xl font-black text-gray-800">{{ $stats['selesai'] }} Orang</p></div>
            </div>
        </div>

        <h2 class="text-lg font-bold text-gray-800 mb-4 shrink-0"><i class="fa-solid fa-grid-2 text-blue-600 mr-2"></i> Menu Navigasi Utama</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 shrink-0">

            <a href="{{ route('admin.manage_events') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition"><i class="fa-solid fa-layer-group"></i></div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Manajemen Event</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Nama Event & Field Form Dinamis.</p>
                </div>
            </a>

            <button onclick="openSpmbModal()" class="group bg-gradient-to-br from-gray-900 to-gray-800 p-4 rounded-xl shadow-lg border border-gray-700 hover:shadow-xl hover:border-yellow-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-gray-800 text-yellow-400 border border-gray-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-white mb-0.5">Akses Publik Antrian</h2>
                    <p class="text-gray-400 text-[10px] leading-tight">Atur Waktu Buka & Kunci Form.</p>
                </div>
            </button>

            <a href="{{ route('admin.manage_sessions') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-blue-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition"><i class="fa-solid fa-calendar-alt"></i></div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Manajemen Sesi</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Atur Kalender & Kuota Antrian.</p>
                </div>
            </a>

            <a href="{{ route('admin.manage_users') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-green-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-green-50 text-green-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition"><i class="fa-solid fa-users-gear"></i></div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Manajemen Akun</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">{{ ($hasCheckinEvents ?? false) ? 'Kelola Petugas Loket & Scanner.' : 'Kelola Petugas Loket & Go-Show.' }}</p>
                </div>
            </a>

            <a href="{{ route('admin.manage_display') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-purple-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition"><i class="fa-solid fa-tv"></i></div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Pengaturan Layar</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Teks, Video & Suara Panggilan.</p>
                </div>
            </a>

            <a href="{{ route('admin.attendance') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-orange-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-orange-50 text-orange-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">
                    <i class="fa-solid fa-clipboard-user"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Pantau Kehadiran</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Lacak Antrian, Mutasi & Export CSV.</p>
                </div>
            </a>

            <a href="{{ route('ticket.layar') }}" target="_blank" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-indigo-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">
                    <i class="fa-solid fa-display"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Buka Layar Monitor</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Tampilan TV Aula (Tab Baru).</p>
                </div>
            </a>

            <a href="{{ route('admin.backup_db') }}" class="group bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md hover:border-emerald-400 transition flex items-center gap-4 text-left">
                <div class="w-14 h-14 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-2xl shrink-0 group-hover:scale-110 transition">
                    <i class="fa-solid fa-database"></i>
                </div>
                <div>
                    <h2 class="text-sm font-black text-gray-800 mb-0.5">Backup Database (.sql)</h2>
                    <p class="text-gray-500 text-[10px] leading-tight">Unduh Cadangan Lengkap Data &amp; Event.</p>
                </div>
            </a>

        </div>
    </div>
</div>

<div id="spmbModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden flex items-center justify-center z-50 px-4">
    <div class="bg-white p-8 rounded-3xl w-full max-w-lg text-left relative shadow-2xl border-t-8 border-gray-900 overflow-y-auto max-h-[95vh]">
        <button onclick="closeSpmbModal()" class="absolute top-5 right-5 text-gray-400 hover:text-red-500 transition bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center"><i class="fa-solid fa-times"></i></button>

        <h3 class="text-2xl font-black text-gray-800 mb-2"><i class="fa-solid fa-lock text-gray-600 mr-2"></i> Akses Publik Antrian</h3>
        <p class="text-sm text-gray-500 mb-6 font-semibold">Atur jadwal pembukaan dan status pendaftaran tiket antrian.</p>

        <form action="{{ route('admin.spmb_access') }}" method="POST" class="space-y-6">
            @csrf

            <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl">
                <label class="block text-sm font-bold text-blue-800 mb-2"><i class="fa-solid fa-clock mr-1"></i> Waktu Pendaftaran Dibuka</label>
                <input type="datetime-local" name="spmb_open_at" value="{{ (isset($setting) && $setting->spmb_open_at) ? \Carbon\Carbon::parse($setting->spmb_open_at)->format('Y-m-d\TH:i') : '' }}" class="w-full p-3 bg-white border border-blue-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500 font-bold text-gray-800">
                <p class="text-[11px] text-blue-600 mt-2 font-semibold">*) Kosongkan jika pendaftaran dibuka langsung tanpa hitung mundur.</p>
            </div>

            <div class="space-y-3">
                <p class="text-sm font-bold text-gray-800 border-b pb-2">Status Sistem Antrian:</p>

                <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 {{ (!$setting || (!$setting->is_maintenance && !$setting->is_ticket_exhausted)) ? 'border-green-400 bg-green-50 shadow-inner' : 'border-gray-200 bg-white' }}">
                    <input type="radio" name="system_status" value="normal" {{ (!$setting || (!$setting->is_maintenance && !$setting->is_ticket_exhausted)) ? 'checked' : '' }} class="mt-1 w-5 h-5 text-green-600 focus:ring-green-500 border-gray-300">
                    <div>
                        <span class="block text-sm font-black text-green-800 mb-1">NORMAL (SESUAI JADWAL)</span>
                        <span class="block text-xs font-semibold text-green-600 leading-tight">Sistem berjalan normal mengikuti waktu buka pendaftaran di atas.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 {{ (isset($setting) && $setting->is_ticket_exhausted) ? 'border-yellow-400 bg-yellow-50 shadow-inner' : 'border-gray-200 bg-white' }}">
                    <input type="radio" name="system_status" value="exhausted" {{ (isset($setting) && $setting->is_ticket_exhausted) ? 'checked' : '' }} class="mt-1 w-5 h-5 text-yellow-600 focus:ring-yellow-500 border-gray-300">
                    <div>
                        <span class="block text-sm font-black text-yellow-800 mb-1">TUTUP PAKSA (TIKET HABIS / BELUM DIBUKA)</span>
                        <span class="block text-xs font-semibold text-yellow-700 leading-tight">Form pengambilan tiket antrian ditutup sementara.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 border rounded-xl cursor-pointer transition hover:bg-gray-50 {{ (isset($setting) && $setting->is_maintenance) ? 'border-red-400 bg-red-50 shadow-inner' : 'border-gray-200 bg-white' }}">
                    <input type="radio" name="system_status" value="maintenance" {{ (isset($setting) && $setting->is_maintenance) ? 'checked' : '' }} class="mt-1 w-5 h-5 text-red-600 focus:ring-red-500 border-gray-300">
                    <div>
                        <span class="block text-sm font-black text-red-800 mb-1">TUTUP PAKSA (MAINTENANCE TOTAL)</span>
                        <span class="block text-xs font-semibold text-red-600 leading-tight">Halaman pendaftaran ditutup karena perbaikan sistem.</span>
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
<script>
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
</script>
@endpush
