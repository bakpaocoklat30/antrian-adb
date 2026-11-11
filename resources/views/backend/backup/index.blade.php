@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto w-full">
    
    <div class="flex justify-between items-center bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-6">
        <h1 class="text-2xl font-black text-gray-800 text-left"><i class="fa-solid fa-server text-purple-600 mr-2"></i> Manajemen Database</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-bold transition"><i class="fa-solid fa-arrow-left mr-2"></i> Kembali</a>
    </div>

    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100 min-h-[70vh] flex flex-col">
        
        <p class="text-gray-500 mb-6 font-medium border-b border-gray-100 pb-4">
            Kelola data sistem Anda di sini. Lakukan Backup rutin, Restore saat darurat, atau bersihkan data untuk memulai sesi pendaftaran baru.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 flex-1">
            
            <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 flex flex-col">
                <label class="block text-lg font-black text-blue-800 mb-2"><i class="fa-solid fa-cloud-arrow-down text-blue-500 mr-2"></i> Backup Database</label>
                <div class="text-sm text-gray-600 mt-1 space-y-2 bg-white p-4 rounded-lg border border-blue-200 flex-1 mb-6">
                    <p>Unduh seluruh data pendaftar, pengaturan layar, dan tiket ke dalam format <b>.sql</b>.</p>
                </div>
                
                <button type="button" onclick="confirmBackup()" class="w-full bg-blue-600 text-white text-center font-black text-lg py-4 rounded-xl hover:bg-blue-700 transition shadow-lg mt-auto">
                    <i class="fa-solid fa-download mr-2"></i> DOWNLOAD BACKUP
                </button>
            </div>

            <div class="bg-red-50 p-6 rounded-xl border border-red-100 flex flex-col">
                <label class="block text-lg font-black text-red-800 mb-2"><i class="fa-solid fa-clock-rotate-left text-red-500 mr-2"></i> Restore Database</label>
                
                <div class="text-[11px] text-red-700 mt-1 space-y-1 bg-red-100 p-3 rounded-lg border border-red-200 mb-4 font-bold">
                    <p><i class="fa-solid fa-triangle-exclamation"></i> PERINGATAN KERAS:</p>
                    <p>Proses ini akan MENGHAPUS & MENIMPA seluruh data saat ini!</p>
                </div>

                <form id="formRestore" action="{{ route('admin.restore.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-1">
                    @csrf
                    <div class="mb-6 flex-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Upload File SQL Backup</label>
                        <input type="file" name="file_sql" id="file_sql" accept=".sql" required class="w-full rounded-lg border-gray-300 border p-3 bg-white outline-none focus:ring-2 focus:ring-red-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-bold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                    </div>
                    
                    <button type="button" onclick="confirmRestore()" class="w-full bg-red-600 text-white font-black text-lg py-4 rounded-xl hover:bg-red-700 transition shadow-lg mt-auto">
                        <i class="fa-solid fa-upload mr-2"></i> RESTORE SEKARANG
                    </button>
                </form>
            </div>

            <div class="bg-orange-50 p-6 rounded-xl border border-orange-100 flex flex-col">
                <label class="block text-lg font-black text-orange-800 mb-2"><i class="fa-solid fa-broom text-orange-500 mr-2"></i> Clean / Reset</label>
                
                <div class="text-sm text-gray-600 mt-1 space-y-2 bg-white p-4 rounded-lg border border-orange-200 mb-4 flex-1">
                    <p>Aksi ini hanya mengosongkan data Pendaftar & Antrian. Akun Admin tetap aman.</p>
                </div>

                <form id="formReset" action="{{ route('admin.backup.reset') }}" method="POST" class="mt-auto">
                    @csrf
                    <button type="button" onclick="confirmReset()" class="w-full bg-orange-600 text-white font-black text-lg py-4 rounded-xl hover:bg-orange-700 transition shadow-lg">
                        <i class="fa-solid fa-trash-can mr-2"></i> BERSIHKAN DATA
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // A. Notifikasi Sukses/Gagal dari Controller
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            @if(session('success')) 
                Swal.fire({
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonColor: '#9333ea'
                });
            @endif

            @if(session('error')) 
                Swal.fire({
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            @endif
        }, 500);
    });

    // B. Fungsi Tombol Backup
    function confirmBackup() {
        // Tampilkan loading Backup
        Swal.fire({
            title: 'Sedang Backup Data...',
            text: 'Jangan tutup atau refresh halaman ini sampai selesai.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        // Paksa browser mendownload
        window.location.href = "{{ route('admin.backup.download') }}";

        // Ganti loading jadi sukses setelah 4 detik (karena download tidak refresh halaman)
        setTimeout(() => {
            Swal.fire({
                title: 'Berhasil!',
                text: 'File database berhasil di-backup dan sedang diunduh.',
                icon: 'success',
                confirmButtonColor: '#9333ea'
            });
        }, 4000);
    }

    // C. Fungsi Tombol Restore
    function confirmRestore() {
        const fileInput = document.getElementById('file_sql');
        if (!fileInput.value) {
            Swal.fire({
                title: 'Peringatan', 
                text: 'Silakan pilih file SQL terlebih dahulu!', 
                icon: 'warning',
                confirmButtonColor: '#9333ea'
            });
            return;
        }

        Swal.fire({
            title: 'Lanjutkan Restore?',
            text: "Data saat ini akan dihapus dan diganti dengan data dari file backup!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Restore!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sedang Memproses Restore...',
                    text: 'Mohon tunggu, database sedang diperbarui.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() }
                });
                document.getElementById('formRestore').submit();
            }
        });
    }

    // D. Fungsi Tombol Reset
    function confirmReset() {
        Swal.fire({
            title: 'Bersihkan Semua Data?',
            text: "Semua data antrian akan dihapus dan kuota akan direset menjadi 0!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ea580c',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Bersihkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sedang Clean / Reset Data...',
                    text: 'Jangan tutup atau refresh halaman ini sampai selesai.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                document.getElementById('formReset').submit();
            }
        });
    }
</script>
@endsection