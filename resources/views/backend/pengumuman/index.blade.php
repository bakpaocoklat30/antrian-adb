@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto w-full flex flex-col h-full">
    
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-6 shrink-0">
        <div>
            <h1 class="text-2xl font-black text-gray-800 text-left flex items-center">
                <i class="fa-solid fa-graduation-cap text-blue-600 mr-2"></i> Data Kelulusan SPMB
            </h1>
            <p class="text-sm text-gray-500 mt-1">Impor data dari Provinsi dan pantau status penerimaan map fisik daftar ulang.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2.5 rounded-lg font-bold transition flex items-center">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

    <div id="statsContainer" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 shrink-0">
        @php
            $totalSiswa = \App\Models\SpmbResult::count();
            $sudahDaftar = \App\Models\SpmbResult::where('status_daftar_ulang', 'sudah')->count();
            $belumDaftar = \App\Models\SpmbResult::where('status_daftar_ulang', 'belum')->count();
            $persentase = $totalSiswa > 0 ? round(($sudahDaftar / $totalSiswa) * 100, 1) : 0;
        @endphp
        
        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex items-center gap-5 relative overflow-hidden transition-all hover:shadow-md">
            <div class="absolute -right-4 -top-4 w-28 h-28 bg-blue-50 rounded-full blur-2xl"></div>
            <div class="w-16 h-16 shrink-0 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center text-3xl font-black relative z-10 shadow-inner">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="relative z-10 flex-1">
                <div class="text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Total Lulus</div>
                <div class="text-3xl font-black text-gray-800 tracking-tighter">{{ number_format($totalSiswa, 0, ',', '.') }} <span class="text-sm font-bold text-gray-400">Siswa</span></div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex items-center gap-5 relative overflow-hidden transition-all hover:shadow-md">
            <div class="absolute -right-4 -top-4 w-28 h-28 bg-green-50 rounded-full blur-2xl"></div>
            <div class="w-16 h-16 shrink-0 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center text-3xl font-black relative z-10 shadow-inner">
                <i class="fa-solid fa-box-archive"></i>
            </div>
            <div class="relative z-10 flex-1">
                <div class="text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Berkas Diterima</div>
                <div class="flex items-end gap-2">
                    <div class="text-3xl font-black text-gray-800 tracking-tighter">{{ number_format($sudahDaftar, 0, ',', '.') }}</div>
                    <div class="text-sm font-black text-green-600 mb-1 bg-green-100 px-2 py-0.5 rounded-md">({{ $persentase }}%)</div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm flex items-center gap-5 relative overflow-hidden transition-all hover:shadow-md">
            <div class="absolute -right-4 -top-4 w-28 h-28 bg-red-50 rounded-full blur-2xl"></div>
            <div class="w-16 h-16 shrink-0 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center text-3xl font-black relative z-10 shadow-inner">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="relative z-10 flex-1">
                <div class="text-xs font-black text-gray-500 uppercase tracking-widest mb-1">Belum Daftar Ulang</div>
                <div class="text-3xl font-black text-gray-800 tracking-tighter">{{ number_format($belumDaftar, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-200 mb-6 relative overflow-hidden shrink-0">
        <div class="absolute right-0 top-0 w-32 h-32 bg-blue-50 rounded-full blur-3xl"></div>
        <div class="mb-5 relative z-10">
            <h2 class="text-lg font-black text-gray-800 uppercase tracking-tight"><i class="fa-solid fa-sliders text-blue-600 mr-2"></i> Mapping Jadwal & Warna Map</h2>
            <p class="text-sm text-gray-500 mt-1 font-medium">Tentukan jadwal kedatangan dan warna map untuk masing-masing jurusan secara dinamis.</p>
        </div>
        
        @php
            $setting = \App\Models\Setting::first();
            $warnaArray = $setting && $setting->warna_map ? json_decode($setting->warna_map, true) : [];
        @endphp

        <form action="{{ route('admin.pengumuman.jadwal') }}" method="POST" class="relative z-10">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-5">
                @foreach(['TPFL', 'TK', 'TM', 'DPIB', 'TE', 'TO', 'TJKT'] as $jur)
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-3">
                    <label class="block text-sm font-black text-blue-800 uppercase border-b border-gray-200 pb-1"><i class="fa-solid fa-tag text-gray-400 mr-1"></i> JURUSAN {{ $jur }}</label>
                    
                    <div>
                        <label class="block text-[10px] text-gray-500 font-bold mb-1 uppercase tracking-wider">Hari & Tanggal</label>
                        <input type="text" name="jadwal[{{ $jur }}]" value="{{ $jadwalArray[$jur] ?? '' }}" required placeholder="Cth: Senin, 15 Juli 2026" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none text-xs font-bold">
                    </div>

                    <div>
                        <label class="block text-[10px] text-gray-500 font-bold mb-1 uppercase tracking-wider">Warna Map Fisik</label>
                        <input type="text" name="warna_map[{{ $jur }}]" value="{{ $warnaArray[$jur] ?? '' }}" required placeholder="Cth: MERAH" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-400 outline-none text-xs font-black text-gray-700 uppercase">
                    </div>
                </div>
                @endforeach
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl text-sm font-black shadow-md transition flex items-center gap-2">
                <i class="fa-solid fa-save"></i> Simpan Konfigurasi
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 items-start shrink-0 mb-6">
        <div class="lg:col-span-1 space-y-6">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h2 class="text-md font-bold text-gray-800"><i class="fa-solid fa-file-csv text-green-600 mr-2"></i> Import CSV</h2>
                    <a href="{{ route('admin.pengumuman.template') }}" class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded hover:bg-blue-100 transition whitespace-nowrap">
                        <i class="fa-solid fa-download"></i> Template
                    </a>
                </div>
                <form action="{{ route('admin.pengumuman.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:bg-gray-50 transition cursor-pointer relative">
                        <input type="file" name="file_csv" accept=".csv" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="document.getElementById('fileName').innerText = this.files[0].name">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-gray-400 mb-2"></i>
                        <p class="text-xs font-bold text-gray-600" id="fileName">Klik atau Drag file CSV</p>
                        <p class="text-[10px] text-gray-400 mt-1">Header: NISN_ATAU_NO_PENDAFTARAN, NAMA, JALUR_PENDAFTARAN, JURUSAN</p>
                    </div>
                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-3 rounded-lg shadow-md hover:bg-green-700 transition" onclick="showLoading(this)">Mulai Import</button>
                </form>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-md font-bold text-gray-800 border-b pb-3 mb-4"><i class="fa-solid fa-user-plus text-blue-600 mr-2"></i> Tambah Manual</h2>
                <form action="{{ route('admin.pengumuman.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-700">NISN / No Pendaftaran</label>
                        <input type="text" name="nisn" required maxlength="16" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();" class="w-full border border-gray-300 p-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Nama Siswa</label>
                        <input type="text" name="nama" required class="w-full border border-gray-300 p-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 outline-none uppercase">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Jalur Pendaftaran</label>
                        <select name="jalur_pendaftaran" required class="w-full border border-gray-300 p-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 outline-none cursor-pointer">
                            <option value="">-- Pilih Jalur --</option>
                            <option value="AFIRMASI">AFIRMASI</option>
                            <option value="PRESTASI">PRESTASI</option>
                            <option value="DOMISILI">DOMISILI TERDEKAT</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700">Jurusan</label>
                        <select name="jurusan" required class="w-full border border-gray-300 p-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 outline-none cursor-pointer">
                            <option value="">-- Pilih --</option>
                            <option value="TPFL">TPFL</option>
                            <option value="TK">TK</option>
                            <option value="TM">TM</option>
                            <option value="DPIB">DPIB</option>
                            <option value="TE">TE</option>
                            <option value="TO">TO</option>
                            <option value="TJKT">TJKT</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg shadow-md hover:bg-blue-700 transition mt-2">Simpan Data</button>
                </form>
            </div>
            
            <div class="bg-red-50 p-6 rounded-2xl shadow-sm border border-red-200">
                <h2 class="text-md font-bold text-red-800 border-b border-red-200 pb-3 mb-4"><i class="fa-solid fa-trash-can mr-2"></i> Danger Zone</h2>
                <form action="{{ route('admin.pengumuman.truncate') }}" method="POST" class="delete-form" data-msg="PERINGATAN: Semua data kelulusan akan dihapus permanen! Anda yakin?">
                    @csrf
                    <button type="submit" class="w-full bg-red-600 text-white font-bold py-2.5 rounded-lg shadow-md hover:bg-red-700 transition text-sm">
                        <i class="fa-solid fa-trash-can mr-1"></i> Kosongkan Semua Data
                    </button>
                </form>
            </div>
        </div>
        
        <div class="lg:col-span-3 bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full relative">
            <div class="flex justify-between items-center border-b pb-3 mb-4 shrink-0">
                <h2 class="text-lg font-bold text-gray-800 flex items-center">
                    Daftar Siswa & Tracking Berkas
                    <span id="radarBadge" class="ml-3 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-50 text-green-700 text-[10px] font-black border border-green-200 shadow-sm transition-all duration-300" title="Auto-Sync Aktif">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span id="radarPulse" class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-600 transition-colors duration-300"></span>
                        </span>
                        <span id="radarText">LIVE RADAR</span>
                    </span>
                </h2>
                
                <div class="flex items-center gap-3">
                    <a id="btnExportCsv" href="{{ route('admin.pengumuman.export') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 rounded-xl font-black text-sm shadow-md hover:shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-file-export text-lg"></i> Export CSV
                    </a>
                    <button onclick="bukaScannerBerkas()" class="bg-gradient-to-r from-blue-700 to-indigo-600 hover:from-blue-800 hover:to-indigo-700 text-white px-5 py-2.5 rounded-xl font-black text-sm shadow-md hover:shadow-lg transition flex items-center gap-2">
                        <i class="fa-solid fa-qrcode text-lg"></i> Scan Terima Berkas
                    </button>
                </div>
            </div>

            <form id="filterForm" class="bg-gray-50 p-4 rounded-xl border border-gray-200 mb-4 grid grid-cols-1 md:grid-cols-5 gap-4 items-end shrink-0">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-600 mb-1"><i class="fa-solid fa-magnifying-glass"></i> Cari NISN / Nama / Asal</label>
                    <input type="text" id="searchInput" name="search" value="{{ request('search') }}" placeholder="Ketik pencarian..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all shadow-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1"><i class="fa-solid fa-route"></i> Filter Jalur</label>
                    <select id="jalurSelect" name="jalur" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all cursor-pointer shadow-sm">
                        <option value="">Semua Jalur</option>
                        <option value="AFIRMASI" {{ request('jalur') == 'AFIRMASI' ? 'selected' : '' }}>AFIRMASI</option>
                        <option value="PRESTASI" {{ request('jalur') == 'PRESTASI' ? 'selected' : '' }}>PRESTASI</option>
                        <option value="DOMISILI" {{ request('jalur') == 'DOMISILI' ? 'selected' : '' }}>DOMISILI</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1"><i class="fa-solid fa-layer-group"></i> Filter Jurusan</label>
                    <select id="jurusanSelect" name="jurusan" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all cursor-pointer shadow-sm">
                        <option value="">Semua Jurusan</option>
                        @foreach(['TPFL', 'TK', 'TM', 'DPIB', 'TE', 'TO', 'TJKT'] as $j)
                            <option value="{{ $j }}" {{ request('jurusan') == $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1"><i class="fa-solid fa-clipboard-check"></i> Status Berkas</label>
                    <select id="statusSelect" name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-blue-500 outline-none transition-all cursor-pointer shadow-sm">
                        <option value="">Semua Status</option>
                        <option value="sudah" {{ request('status') == 'sudah' ? 'selected' : '' }}>Sudah Diterima</option>
                        <option value="belum" {{ request('status') == 'belum' ? 'selected' : '' }}>Belum Daftar Ulang</option>
                    </select>
                </div>
            </form>
            
            <div class="relative overflow-x-auto flex-1 min-h-[300px] border border-gray-200 rounded-lg custom-scrollbar">
                <div id="tableLoader" class="hidden absolute inset-0 bg-white/70 backdrop-blur-[2px] z-20 flex items-center justify-center rounded-lg transition-all duration-300">
                    <div class="flex flex-col items-center bg-white px-6 py-4 rounded-2xl shadow-lg border border-blue-100">
                        <i class="fa-solid fa-circle-notch fa-spin text-4xl text-blue-600 mb-3"></i>
                        <span class="text-xs font-black text-blue-800 tracking-widest uppercase animate-pulse">Menarik Data...</span>
                    </div>
                </div>

                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="bg-gray-100 text-gray-600 uppercase text-[11px] tracking-wider sticky top-0 z-10 shadow-sm">
                            <th class="p-3 border-b border-gray-200 font-black">No</th>
                            <th class="p-3 border-b border-gray-200 font-black">Data Siswa</th>
                            <th class="p-3 border-b border-gray-200 font-black">Asal Sekolah</th>
                            <th class="p-3 border-b border-gray-200 font-black text-center">Jalur & Jurusan</th>
                            <th class="p-3 border-b border-gray-200 font-black text-center">Status Berkas</th>
                            <th class="p-3 border-b border-gray-200 font-black text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody">
                        @forelse($results as $key => $res)
                        <tr class="border-b transition {{ (isset($res->status_daftar_ulang) && $res->status_daftar_ulang === 'sudah') ? 'bg-green-50/50 hover:bg-green-50' : 'hover:bg-gray-50' }}">
                            <td class="p-3 font-bold text-gray-500">{{ $results->firstItem() + $key }}</td>
                            <td class="p-3">
                                <div class="font-mono text-blue-600 font-bold text-xs">{{ $res->nisn }}</div>
                                <div class="font-black text-gray-800 uppercase">{{ $res->nama }}</div>
                            </td>
                            <td class="p-3">
                                @if($res->asal_sekolah)
                                    <span class="font-bold text-gray-800 uppercase text-xs">{{ $res->asal_sekolah }}</span>
                                @else
                                    <span class="text-[10px] font-bold text-red-500 bg-red-50 px-2 py-1 rounded border border-red-100"><i class="fa-solid fa-spinner fa-spin mr-1 opacity-50"></i> Menunggu...</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <div class="font-bold text-gray-600 text-[10px] mb-1 uppercase">{{ $res->jalur_pendaftaran }}</div>
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-black text-xs border border-blue-200 shadow-sm">{{ $res->jurusan }}</span>
                            </td>
                            <td class="p-3 text-center">
                                @if(isset($res->status_daftar_ulang) && $res->status_daftar_ulang === 'sudah')
                                    <span class="bg-green-100 text-green-700 border border-green-300 px-3 py-1 rounded-md font-bold text-xs shadow-sm"><i class="fa-solid fa-check mr-1"></i> DITERIMA</span>
                                @else
                                    <span class="bg-gray-100 text-gray-500 border border-gray-300 px-3 py-1 rounded-md font-bold text-xs shadow-sm"><i class="fa-solid fa-clock mr-1"></i> BELUM</span>
                                @endif
                            </td>
                            <td class="p-3 text-center flex justify-center gap-2">
                                <button onclick="openEditModal({{ $res->id }}, '{{ $res->nisn }}', '{{ addslashes($res->nama) }}', '{{ addslashes($res->jalur_pendaftaran) }}', '{{ $res->jurusan }}')" class="bg-yellow-100 text-yellow-700 w-8 h-8 rounded-lg font-bold text-xs shadow-sm hover:bg-yellow-200 transition" title="Edit Data"><i class="fa-solid fa-edit"></i></button>
                                <form action="{{ route('admin.pengumuman.destroy', $res->id) }}" method="POST" class="delete-form" data-msg="Hapus data siswa ini?">@csrf @method('DELETE') <button class="bg-red-100 text-red-700 w-8 h-8 rounded-lg font-bold text-xs shadow-sm hover:bg-red-200 transition" title="Hapus"><i class="fa-solid fa-trash"></i></button></form>
                            </td>
                        </tr>
                        @empty 
                        <tr>
                            <td colspan="6" class="text-center p-16">
                                <i class="fa-solid fa-folder-open text-5xl text-gray-300 mb-3"></i>
                                <div class="text-gray-500 font-bold text-lg">Tidak ada data ditemukan</div>
                                <div class="text-gray-400 text-sm">Coba sesuaikan kata kunci atau filter pencarian Anda.</div>
                            </td>
                        </tr> 
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div id="paginationContainer" class="mt-4 border-t border-gray-200 pt-4 shrink-0 flex justify-between items-center">
                {{ $results->links() }}
                
                <div class="w-32 text-right">
                    <select id="perPageSelect" name="per_page" class="border border-gray-300 rounded-lg px-3 py-1 text-xs font-bold transition-all focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20 Baris</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 Baris</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 Baris</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm hidden flex items-center justify-center z-50 p-4">
    <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-2xl">
        <h3 class="text-xl font-black text-gray-800 mb-4 border-b pb-3">Edit Data Siswa</h3>
        <form id="editForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-bold text-gray-700">NISN / No Pendaftaran</label>
                <input type="text" name="nisn" id="e_nisn" required maxlength="16" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();" class="w-full border border-gray-300 p-2 rounded-lg text-sm bg-gray-50 focus:ring-2 focus:ring-blue-400 outline-none">
            </div>
            <div><label class="block text-xs font-bold text-gray-700">Nama Siswa</label><input type="text" name="nama" id="e_nama" required class="w-full border border-gray-300 p-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 uppercase outline-none"></div>
            <div>
                <label class="block text-xs font-bold text-gray-700">Jalur Pendaftaran</label>
                <select name="jalur_pendaftaran" id="e_jalur" required class="w-full border border-gray-300 p-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 outline-none cursor-pointer">
                    <option value="AFIRMASI">AFIRMASI</option>
                    <option value="PRESTASI">PRESTASI</option>
                    <option value="DOMISILI">DOMISILI TERDEKAT</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700">Jurusan</label>
                <select name="jurusan" id="e_jurusan" required class="w-full border border-gray-300 p-2 rounded-lg text-sm focus:ring-2 focus:ring-blue-400 outline-none cursor-pointer">
                    <option value="TPFL">TPFL</option>
                    <option value="TK">TK</option>
                    <option value="TM">TM</option>
                    <option value="DPIB">DPIB</option>
                    <option value="TE">TE</option>
                    <option value="TO">TO</option>
                    <option value="TJKT">TJKT</option>
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('editModal')" class="w-1/2 bg-gray-100 text-gray-700 py-3 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                <button type="submit" class="w-1/2 bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition shadow-md">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
    @if(session('error')) Swal.fire({ icon: 'error', title: 'Gagal!', text: '{!! session('error') !!}', confirmButtonColor: '#e3342f' }); @endif
    @if(session('success')) Swal.fire({ icon: 'success', title: 'Berhasil!', text: '{!! session('success') !!}', confirmButtonColor: '#3490dc' }); @endif
    @if($errors->any()) Swal.fire({ icon: 'error', title: 'Validasi Gagal!', text: '{{ $errors->first() }}', confirmButtonColor: '#e3342f' }); @endif

    function showLoading(btn) {
        if(document.querySelector('input[name="file_csv"]').files.length > 0) {
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sedang Mengimport...';
            btn.classList.add('opacity-75', 'cursor-not-allowed');
        }
    }

    function openEditModal(id, nisn, nama, jalur, jurusan) {
        document.getElementById('editForm').action = '/admin/pengumuman/' + id;
        document.getElementById('e_nisn').value = nisn;
        document.getElementById('e_nama').value = nama;
        
        // AUTO-SELECT JALUR FIX
        let jalurSelect = document.getElementById('e_jalur');
        let mappedJalur = jalur.toUpperCase();
        if(mappedJalur.includes('DOMISILI') || mappedJalur.includes('TERDEKAT')) mappedJalur = 'DOMISILI';
        
        for (let i = 0; i < jalurSelect.options.length; i++) {
            if (jalurSelect.options[i].value === mappedJalur) {
                jalurSelect.selectedIndex = i;
                break;
            }
        }

        document.getElementById('e_jurusan').value = jurusan;
        document.getElementById('editModal').classList.remove('hidden');
    }
    
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

    let html5QrCodeScanner = null;

    function prosesTerimaBerkas(nisnScan) {
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
                    Swal.fire({ title: 'SUDAH DITERIMA!', text: data.message, icon: 'info', confirmButtonColor: '#3b82f6' })
                    .then(() => { if (html5QrCodeScanner && html5QrCodeScanner.isScanning) html5QrCodeScanner.resume(); });
                } else {
                    Swal.fire({ title: 'TERVERIFIKASI!', text: data.message, icon: 'success', confirmButtonColor: '#10b981' })
                    .then(() => { triggerFilter(false); }); 
                }
            } else {
                let audio = new Audio('https://www.soundjay.com/buttons/sounds/button-10.mp3'); audio.play();
                Swal.fire({ title: 'DITOLAK!', text: data.message, icon: 'error', confirmButtonColor: '#ef4444' })
                    .then(() => { if (html5QrCodeScanner && html5QrCodeScanner.isScanning) html5QrCodeScanner.resume(); });
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Terjadi kesalahan jaringan.', 'error');
        });
    }

    function bukaScannerBerkas() {
        Swal.fire({
            title: 'Scanner Terima Berkas Map',
            html: `
                <div class="text-sm text-gray-500 mb-4 font-semibold">Arahkan kamera ke QR Code di sampul depan Map Siswa.</div>
                <div id="berkas-reader-box" class="w-full overflow-hidden rounded-2xl border-4 border-indigo-100 bg-black flex items-center justify-center relative shadow-inner mb-4" style="min-height: 250px;">
                    <i class="fa-solid fa-qrcode text-6xl text-gray-600 absolute z-0 opacity-40"></i>
                    <div id="berkas-reader" class="w-full relative z-10"></div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <hr class="flex-1 border-gray-300"><span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Atau Ketik Manual</span><hr class="flex-1 border-gray-300">
                </div>
                <div class="flex items-center gap-2 mt-3">
                    <input id="berkas-input" type="text" maxlength="16" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();" class="w-full text-center text-lg font-black tracking-widest rounded-xl border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 p-3 bg-gray-50 outline-none transition" placeholder="Ketik Kode..." autocomplete="off">
                    <button id="btnVerifManual" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-3 rounded-xl font-bold transition shadow-md flex items-center justify-center">
                        <i class="fa-solid fa-check-double"></i>
                    </button>
                </div>
            `,
            showConfirmButton: false, showCancelButton: true, cancelButtonText: 'Tutup Scanner', 
            didOpen: () => {
                const config = { fps: 10, qrbox: { width: 200, height: 200 } };
                html5QrCodeScanner = new Html5Qrcode("berkas-reader");
                html5QrCodeScanner.start({ facingMode: "environment" }, config, (decodedText) => {
                    html5QrCodeScanner.pause(); 
                    document.getElementById('berkas-input').value = decodedText;
                    document.getElementById('btnVerifManual').click(); 
                }).catch(err => {
                    html5QrCodeScanner.start({ facingMode: "user" }, config, (decodedText) => {
                        html5QrCodeScanner.pause();
                        document.getElementById('berkas-input').value = decodedText;
                        document.getElementById('btnVerifManual').click(); 
                    }).catch(err2 => { console.log("Kamera tidak ditemukan."); });
                });

                document.getElementById('btnVerifManual').addEventListener('click', function() {
                    const val = document.getElementById('berkas-input').value.trim();
                    if(val) { document.getElementById('berkas-input').value = ''; prosesTerimaBerkas(val); } 
                    else { Swal.showValidationMessage('Data tidak boleh kosong!'); }
                });

                document.getElementById('berkas-input').addEventListener('keypress', function(e) { 
                    if(e.key === 'Enter') document.getElementById('btnVerifManual').click(); 
                });
            },
            willClose: () => { if (html5QrCodeScanner && html5QrCodeScanner.isScanning) { html5QrCodeScanner.stop().catch(e => console.error(e)); } }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filterForm');
        const searchInput = document.getElementById('searchInput');
        const jalurSelect = document.getElementById('jalurSelect');
        const jurusanSelect = document.getElementById('jurusanSelect');
        const statusSelect = document.getElementById('statusSelect');
        const perPageSelect = document.getElementById('perPageSelect'); 
        const btnExportCsv = document.getElementById('btnExportCsv');
        const tableLoader = document.getElementById('tableLoader');
        let typingTimer;

        function fetchLive(url, isSilent = false) {
            let radarBadge = document.getElementById('radarBadge');
            let radarPulse = document.getElementById('radarPulse');
            let radarText = document.getElementById('radarText');

            if (!isSilent && tableLoader) {
                tableLoader.classList.remove('hidden'); 
            } else if (isSilent && radarPulse && radarText && radarBadge) {
                radarBadge.classList.remove('bg-green-50', 'text-green-700', 'border-green-200');
                radarBadge.classList.add('bg-yellow-50', 'text-yellow-700', 'border-yellow-200');
                radarPulse.classList.remove('bg-green-600');
                radarPulse.classList.add('bg-yellow-500');
                radarText.innerText = 'SYNCING...';
            }

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(response => response.text())
            .then(html => {
                const doc = new DOMParser().parseFromString(html, 'text/html');
                
                const newBody = doc.getElementById('dataTableBody');
                if(newBody) document.getElementById('dataTableBody').innerHTML = newBody.innerHTML;
                
                const newPagination = doc.getElementById('paginationContainer');
                if(newPagination) {
                    document.getElementById('paginationContainer').innerHTML = newPagination.innerHTML;
                    attachPaginationEvents(); 
                    
                    const newPerPageSelect = document.getElementById('perPageSelect');
                    if (newPerPageSelect) {
                        newPerPageSelect.addEventListener('change', () => { triggerFilter(false); });
                    }
                }

                const newStats = doc.getElementById('statsContainer');
                if(newStats) document.getElementById('statsContainer').innerHTML = newStats.innerHTML;
                
                if(!isSilent) {
                    window.history.replaceState({}, document.title, url);
                    if (tableLoader) tableLoader.classList.add('hidden'); 
                } else if(radarPulse && radarText && radarBadge) {
                    setTimeout(() => {
                        radarBadge.classList.remove('bg-yellow-50', 'text-yellow-700', 'border-yellow-200');
                        radarBadge.classList.add('bg-green-50', 'text-green-700', 'border-green-200');
                        radarPulse.classList.remove('bg-yellow-500');
                        radarPulse.classList.add('bg-green-600');
                        radarText.innerText = 'LIVE RADAR';
                    }, 800); 
                }
            })
            .catch(error => { 
                console.error('AJAX Error:', error); 
                if (tableLoader) tableLoader.classList.add('hidden');
            });
        }

        function triggerFilter(isSilent = false) {
            const url = new URL(window.location.pathname, window.location.origin);
            const exportUrl = new URL("{{ route('admin.pengumuman.export') }}", window.location.origin);

            if (searchInput.value) {
                url.searchParams.set('search', searchInput.value);
                exportUrl.searchParams.set('search', searchInput.value);
            }
            if (jalurSelect.value) {    
                url.searchParams.set('jalur', jalurSelect.value);
                exportUrl.searchParams.set('jalur', jalurSelect.value);
            }
            if (jurusanSelect.value) {
                url.searchParams.set('jurusan', jurusanSelect.value);
                exportUrl.searchParams.set('jurusan', jurusanSelect.value);
            }
            if (statusSelect.value) {
                url.searchParams.set('status', statusSelect.value);
                exportUrl.searchParams.set('status', statusSelect.value);
            }
            
            const activePerPageSelect = document.getElementById('perPageSelect');
            if (activePerPageSelect && activePerPageSelect.value) {
                url.searchParams.set('per_page', activePerPageSelect.value);
            }
            
            btnExportCsv.href = exportUrl.toString();
            fetchLive(url.toString(), isSilent);
        }

        function attachPaginationEvents() {
            document.querySelectorAll('#paginationContainer a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault(); 
                    fetchLive(this.href, false); 
                });
            });
        }

        attachPaginationEvents();
        form.addEventListener('submit', e => { e.preventDefault(); triggerFilter(false); });
        
        searchInput.addEventListener('keyup', () => {
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => { triggerFilter(false); }, 500);
        });
        searchInput.addEventListener('keydown', () => clearTimeout(typingTimer));
        
        jalurSelect.addEventListener('change', () => { triggerFilter(false); });    
        jurusanSelect.addEventListener('change', () => { triggerFilter(false); });
        statusSelect.addEventListener('change', () => { triggerFilter(false); });
        
        if(perPageSelect) {
            perPageSelect.addEventListener('change', () => { triggerFilter(false); });
        }

        document.body.addEventListener('submit', function(e) {
            if (e.target && e.target.classList.contains('delete-form')) {
                e.preventDefault();
                Swal.fire({
                    title: 'Hapus Data?', text: e.target.getAttribute('data-msg'), icon: 'warning',
                    showCancelButton: true, confirmButtonColor: '#e3342f', cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Lanjutkan!', cancelButtonText: 'Batal'
                }).then((result) => { if (result.isConfirmed) e.target.submit(); });
            }
        });

        setInterval(() => {
            let isModalClosed = document.getElementById('editModal').classList.contains('hidden');
            let isNotTyping = document.activeElement !== searchInput;
            
            if (isModalClosed && isNotTyping) {
                triggerFilter(true); 
            }
        }, 10000); 

    });
</script>
<style>
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 8px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
@endpush