@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto w-full">
    
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-6">
        <h1 class="text-2xl font-black text-gray-800 text-left">
            <i class="fa-solid fa-calendar-alt text-blue-600 mr-2"></i> Management Sesi
        </h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch min-h-[70vh]">
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4"><i class="fa-solid fa-plus-circle text-blue-600 mr-2"></i> Buka Sesi Baru</h2>
            <form action="{{ route('admin.service_date.store') }}" method="POST" class="flex flex-col flex-1">
                @csrf
                <div class="space-y-4 flex-1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal</label>
                        <input type="date" name="service_date" required class="w-full border p-2 rounded outline-none focus:ring-2 focus:ring-blue-400">
                    </div>
                    
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Nama Sesi</label>
                            <input type="text" name="session_name" placeholder="Pagi / Siang" required class="w-full border p-2 rounded outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kode Sesi</label>
                            <input type="text" name="session_code" placeholder="P/SI" maxlength="3" required class="w-full border p-2 rounded outline-none focus:ring-2 focus:ring-blue-400 uppercase font-bold text-blue-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="block text-sm text-gray-700">Mulai</label><input type="time" name="start_time" required class="w-full border p-2 rounded outline-none focus:ring-2 focus:ring-blue-400"></div>
                        <div><label class="block text-sm text-gray-700">Selesai</label><input type="time" name="end_time" required class="w-full border p-2 rounded outline-none focus:ring-2 focus:ring-blue-400"></div>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700">Batas Kuota</label><input type="number" name="quota" value="50" required class="w-full border p-2 rounded outline-none focus:ring-2 focus:ring-blue-400"></div>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg shadow-md hover:bg-blue-700 transition mt-6">Simpan Sesi</button>
            </form>
        </div>
        
        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full relative">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4 shrink-0">Daftar Kalender Layanan</h2>

            <form id="filterForm" class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-6 flex flex-col md:flex-row gap-4 items-end shrink-0 relative">
                <div id="loadingIndicator" class="hidden absolute -top-3 right-4 bg-white border border-blue-200 px-3 py-1 rounded-full shadow-sm text-[10px] font-bold text-blue-500 animate-pulse z-10">
                    <i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Memuat Data...
                </div>

                <div class="w-full md:w-1/4 text-left">
                    <label class="block text-xs font-bold text-gray-600 mb-1">Cari Nama Sesi</label>
                    <div class="relative">
                        <i class="fa-solid fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci..." class="w-full border border-gray-300 rounded-lg pl-10 pr-3 py-2 text-xs focus:ring-2 focus:ring-blue-400 outline-none transition">
                    </div>
                </div>
                
                <div class="w-full md:w-1/4 text-left">
                    <label class="block text-xs font-bold text-gray-600 mb-1">Filter Tanggal</label>
                    <input type="date" id="dateFilter" name="date" value="{{ request('date') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-400 outline-none transition">
                </div>

                <div class="w-full md:w-1/4 text-left">
                    <label class="block text-xs font-bold text-gray-600 mb-1">Status Sesi</label>
                    <select id="statusSelect" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-400 outline-none transition cursor-pointer">
                        <option value="">-- Semua Status --</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>✅ Aktif</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>❌ Tutup</option>
                    </select>
                </div>

                <div class="w-full md:w-1/4 text-left">
                    <label class="block text-xs font-bold text-gray-600 mb-1">Tampilkan Data</label>
                    <select id="perPageSelect" name="sd_per_page" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-blue-400 outline-none transition cursor-pointer font-bold text-gray-700">
                        <option value="5" {{ request('sd_per_page') == 5 || !request('sd_per_page') ? 'selected' : '' }}>5 Baris</option>
                        <option value="10" {{ request('sd_per_page') == 10 ? 'selected' : '' }}>10 Baris</option>
                        <option value="50" {{ request('sd_per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                    </select>
                </div>
            </form>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead>
                        <tr class="bg-gray-50 text-sm">
                            <th class="p-3 border-b">Tanggal & Sesi</th>
                            <th class="p-3 border-b">Waktu</th>
                            <th class="p-3 border-b">Status/Kuota</th>
                            <th class="p-3 border-b text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="sessionsTableBody">
                        @forelse($serviceDates as $date)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="p-3 font-bold text-gray-800">
                                {{ \Carbon\Carbon::parse($date->service_date)->translatedFormat('d M Y') }} - 
                                <span class="text-blue-600">{{ $date->session_name }}</span>
                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px] ml-1 uppercase font-black" title="Kode Sesi Prefix">
                                    {{ $date->session_code ?? substr($date->session_name, 0, 1) }}
                                </span>
                            </td>
                            <td class="p-3 text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($date->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($date->end_time)->format('H:i') }}
                            </td>
                            <td class="p-3">
                                <span class="{{ $date->is_active ? 'text-green-600' : 'text-red-600' }} font-bold text-xs uppercase">{{ $date->is_active ? 'AKTIF' : 'TUTUP' }}</span>
                                <div class="text-xs text-gray-500">{{ $date->booked_count }}/{{ $date->quota }} Terisi</div>
                            </td>
                            <td class="p-3 text-center flex justify-center gap-2">
                                <button onclick="viewStudents({{ $date->id }}, '{{ \Carbon\Carbon::parse($date->service_date)->format('d M Y') }} - {{ $date->session_name }}')" class="bg-purple-100 text-purple-700 px-2 py-1 rounded font-bold text-xs shadow-sm hover:bg-purple-200 transition" title="Pendaftar"><i class="fa-solid fa-eye"></i></button>
                                
                                <form action="{{ route('admin.service_date.toggle', $date->id) }}" method="POST">@csrf <button class="bg-gray-100 text-gray-700 px-2 py-1 rounded font-bold text-xs shadow-sm hover:bg-gray-200 transition" title="Toggle Status"><i class="fa-solid fa-power-off"></i></button></form>
                                
                                <button onclick="openEditModal({{ $date->id }}, '{{ \Carbon\Carbon::parse($date->service_date)->format('Y-m-d') }}', '{{ $date->session_name }}', '{{ $date->session_code }}', '{{ \Carbon\Carbon::parse($date->start_time)->format('H:i') }}', '{{ \Carbon\Carbon::parse($date->end_time)->format('H:i') }}', {{ $date->quota }})" class="bg-blue-100 text-blue-700 px-2 py-1 rounded font-bold text-xs shadow-sm hover:bg-blue-200 transition" title="Edit"><i class="fa-solid fa-edit"></i></button>
                                
                                <form action="{{ route('admin.service_date.destroy', $date->id) }}" method="POST" class="delete-form" data-msg="Hapus Sesi ini?">@csrf @method('DELETE') <button class="bg-red-100 text-red-700 px-2 py-1 rounded font-bold text-xs shadow-sm hover:bg-red-200 transition" title="Hapus"><i class="fa-solid fa-trash"></i></button></form>
                            </td>
                        </tr>
                        @empty 
                        <tr><td colspan="4" class="text-center p-12 text-gray-400 italic">Pencarian tidak menemukan hasil.</td></tr> 
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="paginationContainer" class="mt-4 border-t pt-4 shrink-0">{{ $serviceDates->links() }}</div>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-2xl w-full max-w-md mx-4 shadow-2xl">
        <h3 class="text-xl font-bold mb-4">Edit Sesi</h3>
        <form id="editForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div><label class="block text-sm">Tanggal</label><input type="date" name="service_date" id="e_date" required class="w-full border p-2 rounded"></div>
            
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm">Nama Sesi</label>
                    <input type="text" name="session_name" id="e_session" required class="w-full border p-2 rounded">
                </div>
                <div>
                    <label class="block text-sm">Kode Sesi</label>
                    <input type="text" name="session_code" id="e_code" maxlength="3" required class="w-full border p-2 rounded uppercase font-bold text-blue-600">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div><label class="block text-sm">Mulai</label><input type="time" name="start_time" id="e_start" required class="w-full border p-2 rounded"></div>
                <div><label class="block text-sm">Selesai</label><input type="time" name="end_time" id="e_end" required class="w-full border p-2 rounded"></div>
            </div>
            <div><label class="block text-sm">Kuota</label><input type="number" name="quota" id="e_quota" required class="w-full border p-2 rounded"></div>
            <div class="flex gap-2"><button type="button" onclick="closeModal('editModal')" class="w-full bg-gray-200 py-2 rounded-lg font-bold">Batal</button><button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg font-bold">Update</button></div>
        </form>
    </div>
</div>

<div id="studentModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-2xl w-full max-w-3xl mx-4 max-h-[80vh] flex flex-col shadow-2xl">
        <div class="flex justify-between items-center mb-4 border-b pb-2">
            <h3 class="text-xl font-bold" id="studentModalTitle">Daftar Pendaftar</h3>
            <button onclick="closeModal('studentModal')" class="text-red-500 font-bold"><i class="fa-solid fa-times"></i> Tutup</button>
        </div>
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-left border-collapse text-sm">
                <thead><tr class="bg-gray-100"><th class="p-3 border-b">Antrian</th><th class="p-3 border-b">Data Siswa (NISN | Nama)</th><th class="p-3 border-b text-center">Aksi</th></tr></thead>
                <tbody id="studentTableBody"></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    @if(session('error'))
        Swal.fire({
            icon: 'error',
            title: 'Ditolak Sistem!',
            text: '{!! session('error') !!}',
            confirmButtonColor: '#e3342f'
        });
    @endif

    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{!! session('success') !!}',
            confirmButtonColor: '#3490dc'
        });
    @endif

    let currentStudents = [];

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const searchInput = document.getElementById('searchInput');
        const dateFilter = document.getElementById('dateFilter');
        const statusSelect = document.getElementById('statusSelect');
        const perPageSelect = document.getElementById('perPageSelect');
        const loadingIndicator = document.getElementById('loadingIndicator');

        let typingTimer;
        const doneTypingInterval = 500;

        function fetchLive(url) {
            loadingIndicator.classList.remove('hidden');
            
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                const newBody = doc.getElementById('sessionsTableBody');
                if(newBody) document.getElementById('sessionsTableBody').innerHTML = newBody.innerHTML;

                const newPagination = doc.getElementById('paginationContainer');
                if(newPagination) {
                    document.getElementById('paginationContainer').innerHTML = newPagination.innerHTML;
                    attachPaginationEvents(); 
                }
                
                loadingIndicator.classList.add('hidden');
                
                window.history.replaceState({}, document.title, window.location.pathname);
            })
            .catch(error => { console.error('AJAX Error:', error); loadingIndicator.classList.add('hidden'); });
        }

        function triggerFilter() {
            const url = new URL(window.location.pathname, window.location.origin);
            if (searchInput.value) url.searchParams.set('search', searchInput.value);
            if (dateFilter.value) url.searchParams.set('date', dateFilter.value);
            if (statusSelect.value) url.searchParams.set('status', statusSelect.value);
            if (perPageSelect.value) url.searchParams.set('sd_per_page', perPageSelect.value);
            
            fetchLive(url.toString());
        }

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

        searchInput.addEventListener('keyup', () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(triggerFilter, doneTypingInterval);
        });

        searchInput.addEventListener('keydown', () => clearTimeout(typingTimer));
        searchInput.addEventListener('search', triggerFilter);
        dateFilter.addEventListener('change', triggerFilter);
        statusSelect.addEventListener('change', triggerFilter);
        perPageSelect.addEventListener('change', triggerFilter);

        if(searchInput.value) {
            searchInput.focus();
            let val = searchInput.value;
            searchInput.value = '';
            searchInput.value = val;
        }

        document.body.addEventListener('submit', function(e) {
            if (e.target && e.target.classList.contains('delete-form')) {
                e.preventDefault();
                Swal.fire({
                    title: 'Hapus Sesi Jadwal?',
                    text: e.target.getAttribute('data-msg'),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus Sesi!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) e.target.submit();
                });
            }
        });
    });

    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
    
    function viewStudents(sessionId, sessionTitle) {
        document.getElementById('studentModalTitle').innerText = "Pendaftar: " + sessionTitle;
        document.getElementById('studentModal').classList.remove('hidden');
        document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="3" class="text-center p-4"><i class="fa-solid fa-spinner fa-spin"></i> Memuat Data...</td></tr>';
        
        fetch(`/admin/service-date/${sessionId}/students`)
            .then(res => res.json())
            .then(data => {
                if(data.success && data.data.length > 0) {
                    currentStudents = data.data; 
                    let html = '';
                    currentStudents.forEach((std, index) => {
                        let noAntrian = std.ticket_number.split('-')[0];
                        html += `
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="p-3 font-black text-blue-600 text-lg w-24">${noAntrian}</td>
                                <td class="p-3">
                                    <div class="font-bold text-gray-800 text-sm">${std.nisn} | ${std.nama_cpd}</div>
                                    <div class="text-[10px] text-gray-500 font-medium">Kesejahteraan: ${std.jalur_pendaftaran}</div>
                                </td>
                                <td class="p-3 text-center">
                                    <button onclick="showDetail(${index})" class="bg-gray-800 text-white px-3 py-1 rounded-lg text-[10px] font-bold hover:bg-black transition">
                                        <i class="fa-solid fa-circle-info mr-1"></i> Detail Lengkap
                                    </button>
                                </td>
                            </tr>`;
                    });
                    document.getElementById('studentTableBody').innerHTML = html;
                } else {
                    document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="3" class="p-8 text-center text-gray-400 italic">Belum ada pendaftar di sesi ini.</td></tr>';
                }
            });
    }

    // 🔥 UPDATE: Menerima parameter e_code 🔥
    function openEditModal(id, date, session, session_code, start, end, quota) {
        document.getElementById('editForm').action = '/admin/service-date/' + id;
        document.getElementById('e_date').value = date;
        document.getElementById('e_session').value = session;
        document.getElementById('e_code').value = session_code || session.substring(0,1); // Fallback jika DB lama kosong
        document.getElementById('e_start').value = start;
        document.getElementById('e_end').value = end;
        document.getElementById('e_quota').value = quota;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function showDetail(index) {
        let s = currentStudents[index];
        let noAntrian = s.ticket_number.split('-')[0];
        let tglDaftar = new Date(s.created_at).toLocaleString('id-ID', { 
            day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' 
        });

        Swal.fire({
            title: `<span class="text-blue-600 font-black uppercase">Detail Profil Pendaftar</span>`,
            html: `
                <div class="text-left mt-4 overflow-hidden border border-gray-200 rounded-2xl shadow-inner">
                    <table class="w-full text-[11px] border-collapse">
                        <tr class="bg-blue-600 text-white uppercase"><td class="p-2.5 font-bold" colspan="2">Informasi Antrian</td></tr>
                        <tr class="border-b"><td class="p-2.5 bg-gray-50 font-bold w-1/3 text-gray-500">Nomor Antrian</td><td class="p-2.5 font-black text-blue-700 text-lg">${noAntrian}</td></tr>
                        <tr class="border-b"><td class="p-2.5 bg-gray-50 font-bold text-gray-500">ID Tiket</td><td class="p-2.5 font-mono text-gray-700">${s.ticket_number}</td></tr>
                        
                        <tr class="bg-gray-100 text-gray-700 uppercase"><td class="p-2.5 font-bold" colspan="2">Identitas Siswa</td></tr>
                        <tr class="border-b"><td class="p-2.5 bg-gray-50 font-bold text-gray-500">Nama Lengkap</td><td class="p-2.5 font-black text-gray-800 uppercase">${s.nama_cpd}</td></tr>
                        <tr class="border-b"><td class="p-2.5 bg-gray-50 font-bold text-gray-500">NISN</td><td class="p-2.5 text-gray-700">${s.nisn}</td></tr>
                        <tr class="border-b"><td class="p-2.5 bg-gray-50 font-bold text-gray-500">Asal Sekolah</td><td class="p-2.5 text-gray-700">${s.asal_sekolah}</td></tr>
                        <tr class="border-b"><td class="p-2.5 bg-gray-50 font-bold text-gray-500">WhatsApp</td><td class="p-2.5 text-green-600 font-bold"><i class="fa-brands fa-whatsapp"></i> ${s.whatsapp}</td></tr>
                        <tr class="border-b"><td class="p-2.5 bg-gray-50 font-bold text-gray-500">Email</td><td class="p-2.5 text-gray-700">${s.email || '-'}</td></tr>
                        <tr class="border-b"><td class="p-2.5 bg-gray-50 font-bold text-gray-500">Alamat Lengkap</td><td class="p-2.5 text-gray-700 leading-relaxed">${s.alamat}</td></tr>

                        <tr class="bg-gray-100 text-gray-700 uppercase"><td class="p-2.5 font-bold" colspan="2">Status Pendaftaran</td></tr>
                        
                        <tr class="border-b"><td class="p-2.5 bg-gray-50 font-bold text-gray-500">Data Kesejahteraan</td><td class="p-2.5 font-black text-blue-600 uppercase italic">${s.jalur_pendaftaran}</td></tr>
                        
                        <tr class="border-b"><td class="p-2.5 bg-gray-50 font-bold text-gray-500">Waktu Register</td><td class="p-2.5 text-gray-700">${tglDaftar} WIB</td></tr>
                        <tr><td class="p-2.5 bg-gray-50 font-bold text-gray-500">Status Kehadiran</td><td class="p-2.5"><span class="bg-orange-100 text-orange-700 px-3 py-1 rounded-full font-black border border-orange-200 uppercase text-[9px] tracking-tighter">${s.status.replace(/_/g, ' ')}</span></td></tr>
                    </table>
                </div>
            `,
            width: '480px',
            confirmButtonText: 'Tutup Detail Peserta',
            confirmButtonColor: '#1e293b',
        });
    }
</script>
@endpush