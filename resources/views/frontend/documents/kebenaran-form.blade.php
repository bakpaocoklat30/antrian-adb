<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Kebenaran Dokumen</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-adb.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4">

    <div class="container mx-auto max-w-3xl p-8 bg-white rounded-2xl shadow-xl border border-gray-200">
        
        <a href="{{ route('dokumen.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Menu Dokumen
        </a>

        <div class="text-center mb-8 border-b pb-6">
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-800">Surat Pernyataan Kebenaran Dokumen</h2>
            <p class="text-gray-500 text-sm mt-2">Isi formulir di bawah ini dengan data yang sebenarnya. PDF akan otomatis terunduh setelah Anda menekan tombol di bawah.</p>
        </div>

        <form action="{{ route('dokumen.kebenaran.download') }}" method="POST" class="space-y-8">
            @csrf

            <div class="bg-blue-50/50 p-6 rounded-xl border border-blue-100">
                <h3 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
                    <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">1</span>
                    Data Orang Tua / Wali
                </h3>
                <div class="grid grid-cols-1 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap Orang Tua/Wali <span class="text-red-500">*</span></label>
                        <input type="text" name="ortu_nama" required placeholder="Contoh: Budi Santoso" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pekerjaan <span class="text-red-500">*</span></label>
                        <input type="text" name="ortu_pekerjaan" required placeholder="Contoh: Wiraswasta" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap (Sesuai KK) <span class="text-red-500">*</span></label>
                        <textarea name="ortu_alamat" rows="3" required placeholder="Contoh: Jl. Merdeka No. 10, RT 01/RW 02, Desa Maju..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-3 border"></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-green-50/50 p-6 rounded-xl border border-green-100">
                <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                    <span class="bg-green-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">2</span>
                    Data Calon Murid Baru (CMB)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap CMB <span class="text-red-500">*</span></label>
                        <input type="text" name="siswa_nama" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 p-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">NISN <span class="text-red-500">*</span></label>
                        <input type="number" name="siswa_nisn" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 p-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Asal Sekolah <span class="text-red-500">*</span></label>
                        <input type="text" name="siswa_asal_sekolah" required placeholder="Contoh: SMP Negeri 1 Tegal" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 p-3 border">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat Lengkap CMB <span class="text-red-500">*</span></label>
                        <textarea name="siswa_alamat" rows="2" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 p-3 border"></textarea>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-gray-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">3</span>
                    Waktu & Tempat Tanda Tangan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-center">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kota/Kabupaten <span class="text-red-500">*</span></label>
                        <input type="text" name="lokasi_cetak" placeholder="Contoh: Tegal" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 p-3 border">
                    </div>
                    <div class="text-sm text-gray-500 bg-white p-3 rounded-lg border border-gray-200 shadow-sm mt-6 md:mt-0">
                        <p>📅 <strong>Tanggal Surat:</strong> <br>Akan otomatis terisi dengan tanggal hari ini ({{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}).</p>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-extrabold py-4 px-4 rounded-xl hover:bg-blue-700 transition duration-300 text-lg shadow-lg hover:shadow-blue-500/30">
                📄 GENERATE & DOWNLOAD PDF SEKARANG
            </button>
        </form>
    </div>

</body>
</html>