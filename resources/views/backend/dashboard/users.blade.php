@extends('layouts.app')
@section('content')

@php 
    $maxLoket = 25; 
@endphp

<div class="max-w-7xl mx-auto w-full">
    
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-6">
        <h1 class="text-2xl font-black text-gray-800 text-left">
            <i class="fa-solid fa-users-gear text-green-600 mr-2"></i> Management Akun
        </h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition">
            <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch min-h-[70vh]">
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full">
            <h2 class="text-lg font-bold text-gray-800 border-b pb-3 mb-4"><i class="fa-solid fa-user-plus text-green-600 mr-2"></i> Tambah Akun Baru</h2>
            <form action="{{ route('admin.users.store') }}" method="POST" class="flex flex-col flex-1">
                @csrf
                <div class="space-y-4 flex-1">
                    <div><label class="block text-sm font-medium mb-1">Nama</label><input type="text" name="name" required class="w-full border p-2 rounded focus:ring-2 focus:ring-green-400 outline-none transition"></div>
                    <div><label class="block text-sm font-medium mb-1">Email Login</label><input type="email" name="email" required class="w-full border p-2 rounded focus:ring-2 focus:ring-green-400 outline-none transition"></div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Password</label>
                        <div class="relative">
                            <input type="password" id="add_password" name="password" required class="w-full border p-2 pr-10 rounded focus:ring-2 focus:ring-green-400 outline-none transition">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePasswordVisibility('add_password', 'eyeIconAdd', 'text-green-600')">
                                <i id="eyeIconAdd" class="fa-solid fa-eye text-gray-400 hover:text-green-600 transition text-lg"></i>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Role</label>
                        <select name="role" id="roleSelect" onchange="toggleLoket('roleSelect', 'loketInput')" required class="w-full border p-2 rounded focus:ring-2 focus:ring-green-400 outline-none transition cursor-pointer">
                            <option value="verifikator">Verifikator</option>
                            <option value="scanner">Scanner OSIS</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    
                    <div id="loketInput">
                        <label class="block text-sm font-medium mb-1">No Loket</label>
                        <select name="loket_number" class="w-full border p-2 rounded focus:ring-2 focus:ring-green-400 outline-none transition cursor-pointer bg-gray-50">
                            @for($i = 1; $i <= $maxLoket; $i++)
                                <option value="{{ $i }}" {{ in_array($i, $usedLokets) ? 'disabled' : '' }} class="{{ in_array($i, $usedLokets) ? 'text-gray-400 bg-gray-100' : 'text-gray-800' }}">
                                    Loket {{ $i }} {{ in_array($i, $usedLokets) ? '(Sedang Dipakai)' : '' }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg shadow-md hover:bg-green-700 transition mt-6"><i class="fa-solid fa-save"></i> Simpan Akun</button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full relative">
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end border-b pb-4 mb-4 gap-4 relative">
                <h2 class="text-lg font-bold text-gray-800 shrink-0"><i class="fa-solid fa-list text-gray-400 mr-2"></i> Daftar Akun Petugas</h2>
                
                <div id="loadingIndicator" class="hidden absolute top-0 right-0 bg-white border border-green-200 px-3 py-1 rounded-full shadow-sm text-xs font-bold text-green-600 animate-pulse z-10">
                    <i class="fa-solid fa-circle-notch fa-spin mr-1"></i> Memuat Data...
                </div>

                <form id="filterForm" class="w-full md:w-auto flex flex-col sm:flex-row gap-2">
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                        <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Cari nama / email..." class="w-full border border-gray-300 rounded-lg pl-9 pr-3 py-2 text-sm focus:ring-2 focus:ring-green-400 outline-none transition bg-gray-50 focus:bg-white">
                    </div>

                    <select id="perPageSelect" name="u_per_page" class="w-full sm:w-auto border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold text-gray-700 focus:ring-2 focus:ring-green-400 outline-none transition cursor-pointer bg-gray-50">
                        <option value="5" {{ request('u_per_page') == 5 ? 'selected' : '' }}>5 Baris</option>
                        <option value="10" {{ request('u_per_page') == 10 ? 'selected' : '' }}>10 Baris</option>
                        <option value="50" {{ request('u_per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                    </select>
                </form>
            </div>
            
            <div class="overflow-x-auto flex-1">
                <table class="w-full text-left border-collapse min-w-max mb-4">
                    <thead>
                        <tr class="bg-gray-50 text-sm text-gray-600">
                            <th class="p-3 border-b">Nama Petugas</th>
                            <th class="p-3 border-b">Akses Role</th>
                            <th class="p-3 border-b">Status</th>
                            <th class="p-3 border-b text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        @forelse($users as $u)
                        <tr class="border-b transition {{ $u->role === 'super_admin' ? 'bg-gray-100' : 'bg-white hover:bg-green-50' }}">
                            <td class="p-3 font-bold text-gray-800">{{ $u->name }}<br><span class="font-normal text-xs text-gray-500">{{ $u->email }}</span></td>
                            <td class="p-3"><span class="px-2 py-1 bg-white text-xs font-bold rounded uppercase border border-gray-200">{{ str_replace('_', ' ', $u->role) }} {{ $u->loket_number ? '(Lkt '.$u->loket_number.')' : '' }}</span></td>
                            <td class="p-3"><span class="px-2 py-1 text-xs font-bold rounded {{ $u->is_active ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                            
                            <td class="p-3 text-center flex justify-center gap-2">
                                @if($u->role === 'super_admin')
                                    <button type="button" class="bg-gray-50 text-gray-300 px-2 py-1.5 rounded font-bold text-xs shadow-sm cursor-not-allowed" title="Super Admin tidak dapat dinonaktifkan"><i class="fa-solid fa-power-off"></i></button>
                                    <button onclick="openEditUser({{ $u->id }}, '{{ $u->name }}', '{{ $u->email }}', '{{ $u->role }}', '{{ $u->loket_number }}')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1.5 rounded font-bold text-xs transition shadow-sm" title="Edit Data"><i class="fa-solid fa-edit"></i></button>
                                    <button type="button" class="bg-red-50 text-red-200 px-2 py-1.5 rounded font-bold text-xs shadow-sm cursor-not-allowed" title="Super Admin tidak dapat dihapus"><i class="fa-solid fa-trash"></i></button>
                                @else
                                    <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST">@csrf <button class="bg-white hover:bg-gray-200 text-gray-700 px-2 py-1.5 rounded font-bold text-xs transition shadow-sm border border-gray-200" title="Toggle Aktif/Non-Aktif"><i class="fa-solid fa-power-off"></i></button></form>
                                    <button onclick="openEditUser({{ $u->id }}, '{{ $u->name }}', '{{ $u->email }}', '{{ $u->role }}', '{{ $u->loket_number }}')" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1.5 rounded font-bold text-xs transition shadow-sm" title="Edit Data"><i class="fa-solid fa-edit"></i></button>
                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="delete-form" data-msg="Hapus Akun ini secara permanen?">@csrf @method('DELETE') <button class="bg-red-100 hover:bg-red-200 text-red-700 px-2 py-1.5 rounded font-bold text-xs transition shadow-sm" title="Hapus Akun"><i class="fa-solid fa-trash"></i></button></form>
                                @endif
                            </td>
                        </tr>
                        @empty 
                        <tr><td colspan="4" class="text-center p-8 text-gray-500 font-medium">Pencarian tidak menemukan hasil.</td></tr> 
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="paginationContainer" class="mt-4 border-t pt-4">{{ $users->links() }}</div>
        </div>
    </div>
</div>

<div id="editUserModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded-2xl w-full max-w-md mx-4 shadow-2xl">
        <h3 class="text-xl font-black mb-4 text-gray-800"><i class="fa-solid fa-user-pen text-blue-500 mr-2"></i> Edit Akun Petugas</h3>
        <form id="editUserForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div><label class="block text-sm font-bold text-gray-700 mb-1">Nama</label><input type="text" name="name" id="eu_name" required class="w-full border-gray-300 border p-2 rounded-lg outline-none focus:ring-2 focus:ring-blue-400 transition"></div>
            <div><label class="block text-sm font-bold text-gray-700 mb-1">Email</label><input type="email" name="email" id="eu_email" required class="w-full border-gray-300 border p-2 rounded-lg outline-none focus:ring-2 focus:ring-blue-400 transition"></div>
            
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Password Baru <span class="text-xs font-normal text-red-500">(Kosongkan jika tak diubah)</span></label>
                <div class="relative">
                    <input type="password" id="edit_password" name="password" class="w-full border-gray-300 border p-2 pr-10 rounded-lg outline-none focus:ring-2 focus:ring-blue-400 transition">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePasswordVisibility('edit_password', 'eyeIconEdit', 'text-blue-600')">
                        <i id="eyeIconEdit" class="fa-solid fa-eye text-gray-400 hover:text-blue-600 transition text-lg"></i>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Akses Role</label>
                <select name="role" id="eu_role" onchange="toggleLoket('eu_role', 'eu_loketInput')" class="w-full border-gray-300 border p-2 rounded-lg outline-none focus:ring-2 focus:ring-blue-400 transition cursor-pointer">
                    <option value="verifikator">Verifikator</option><option value="scanner">Scanner</option><option value="super_admin">Super Admin</option>
                </select>
            </div>
            
            <div id="eu_loketInput">
                <label class="block text-sm font-bold text-gray-700 mb-1">No Loket</label>
                <select name="loket_number" id="eu_loket" class="w-full border-gray-300 border p-2 rounded-lg outline-none focus:ring-2 focus:ring-blue-400 transition cursor-pointer bg-gray-50">
                    </select>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeModal('editUserModal')" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 py-3 rounded-xl font-bold transition">Batal</button>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-bold shadow-md transition">Update Data</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 🔥 VARIABEL JS MENGAMBIL DARI BLADE (LIMIT LOKET & DATA TERPAKAI) 🔥
    const usedLokets = @json($usedLokets);
    const maxLoket = {{ $maxLoket }};

    @if ($errors->any())
        Swal.fire({
            icon: 'error',
            title: 'Gagal Menyimpan!',
            text: '{!! $errors->first() !!}',
            confirmButtonColor: '#e3342f'
        });
    @endif

    function togglePasswordVisibility(inputId, iconId, activeColorClass) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById(iconId);

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            eyeIcon.classList.remove("fa-eye");
            eyeIcon.classList.add("fa-eye-slash", activeColorClass); 
        } else {
            passwordInput.type = "password";
            eyeIcon.classList.remove("fa-eye-slash", activeColorClass);
            eyeIcon.classList.add("fa-eye");
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const searchInput = document.getElementById('searchInput');
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
                
                const newBody = doc.getElementById('usersTableBody');
                if(newBody) document.getElementById('usersTableBody').innerHTML = newBody.innerHTML;

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
            if (perPageSelect.value) url.searchParams.set('u_per_page', perPageSelect.value);
            
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

        searchInput.addEventListener('keyup', function () {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(triggerFilter, doneTypingInterval);
        });

        searchInput.addEventListener('keydown', function () { clearTimeout(typingTimer); });
        searchInput.addEventListener('search', function() { triggerFilter(); });
        perPageSelect.addEventListener('change', triggerFilter);
        
        let val = searchInput.value;
        searchInput.value = '';
        searchInput.value = val;
        if(val) searchInput.focus();

        document.body.addEventListener('submit', function(e) {
            if (e.target && e.target.classList.contains('delete-form')) {
                e.preventDefault();
                Swal.fire({ 
                    title: 'Konfirmasi Penghapusan', 
                    text: e.target.getAttribute('data-msg'), 
                    icon: 'warning', 
                    showCancelButton: true, 
                    confirmButtonColor: '#e3342f', 
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => { 
                    if (result.isConfirmed) e.target.submit(); 
                });
            }
        });

        @if(session('success')) 
            setTimeout(() => { Swal.fire('Berhasil!', '{{ session('success') }}', 'success'); }, 500);
        @endif
    });

    function toggleLoket(selectId, inputId) { 
        document.getElementById(inputId).style.display = document.getElementById(selectId).value === 'verifikator' ? 'block' : 'none'; 
    }
    
    function closeModal(id) { 
        document.getElementById('edit_password').type = 'password';
        document.getElementById('eyeIconEdit').className = 'fa-solid fa-eye text-gray-400 hover:text-blue-600 transition text-lg';
        document.getElementById(id).classList.add('hidden'); 
    }
    
    // 🔥 MENGGUNAKAN VARIABEL maxLoket UNTUK LOOPING JS 🔥
    function openEditUser(id, name, email, role, currentLoket) {
        document.getElementById('editUserForm').action = "/admin/users/" + id;
        document.getElementById('eu_name').value = name; 
        document.getElementById('eu_email').value = email;
        document.getElementById('edit_password').value = ''; 
        document.getElementById('eu_role').value = role; 
        
        const selectLoket = document.getElementById('eu_loket');
        selectLoket.innerHTML = ''; 
        
        for(let i = 1; i <= maxLoket; i++) {
            let option = document.createElement('option');
            option.value = i;
            
            if (usedLokets.includes(i) && i != currentLoket) {
                option.disabled = true;
                option.text = `Loket ${i} (Sedang Dipakai)`;
                option.className = 'text-gray-400 bg-gray-100';
            } else {
                option.text = `Loket ${i}`;
                option.className = 'text-gray-800';
            }
            
            if (i == currentLoket) {
                option.selected = true;
            }
            
            selectLoket.appendChild(option);
        }

        toggleLoket('eu_role', 'eu_loketInput');
        document.getElementById('editUserModal').classList.remove('hidden');
    }
</script>
@endpush