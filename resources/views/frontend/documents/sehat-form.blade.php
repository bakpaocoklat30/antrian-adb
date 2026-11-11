<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Surat Pernyataan Sehat</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-adb.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4">

    <div class="container mx-auto max-w-3xl p-8 bg-white rounded-2xl shadow-xl border border-gray-200">
        
        <a href="{{ route('dokumen.index') }}" class="inline-flex items-center text-green-600 hover:text-green-800 font-medium mb-6">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Menu Dokumen
        </a>

        <div class="text-center mb-8 border-b pb-6">
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-800">Surat Pernyataan Sehat</h2>
            <p class="text-gray-500 text-sm mt-2">Khusus untuk pemenuhan syarat masuk SMK Negeri.</p>
        </div>

        <form action="{{ route('dokumen.sehat.download') }}" method="POST" class="space-y-8">
            @csrf

            <div class="bg-green-50/50 p-6 rounded-xl border border-green-100">
                <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                    <span class="bg-green-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">1</span>
                    Data Diri & Pilihan Keahlian
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap Siswa</label>
                        <input type="text" name="siswa_nama" required class="w-full rounded-lg border-gray-300 p-3 border focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">NISN</label>
                        <input type="number" name="siswa_nisn" required class="w-full rounded-lg border-gray-300 p-3 border focus:ring-green-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Program Keahlian yang Dituju</label>
                        <input type="text" name="program_keahlian" required placeholder="Contoh: Teknik Komputer dan Jaringan" class="w-full rounded-lg border-gray-300 p-3 border focus:ring-green-500">
                    </div>
                </div>
            </div>

            <div class="bg-blue-50/50 p-6 rounded-xl border border-blue-100">
                <h3 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
                    <span class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">2</span>
                    Detail Alamat Siswa
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-5">
                    <div class="col-span-2 md:col-span-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Jalan / Perumahan</label>
                        <input type="text" name="alamat_jalan" required class="w-full rounded-lg border-gray-300 p-3 border">
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">RT</label>
                        <input type="number" name="alamat_rt" required class="w-full rounded-lg border-gray-300 p-3 border">
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">RW</label>
                        <input type="number" name="alamat_rw" required class="w-full rounded-lg border-gray-300 p-3 border">
                    </div>
                    <div class="col-span-2 md:col-span-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kelurahan / Desa</label>
                        <input type="text" name="alamat_desa" required class="w-full rounded-lg border-gray-300 p-3 border">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kecamatan</label>
                        <input type="text" name="alamat_kecamatan" required class="w-full rounded-lg border-gray-300 p-3 border">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kabupaten / Kota</label>
                        <input type="text" name="alamat_kota" required class="w-full rounded-lg border-gray-300 p-3 border">
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                    <span class="bg-gray-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs mr-2">3</span>
                    Penandatangan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 items-center">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Orang Tua / Wali</label>
                        <input type="text" name="ortu_nama" required class="w-full rounded-lg border-gray-300 p-3 border">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Kota Tempat Tanda Tangan</label>
                        <input type="text" name="lokasi_cetak" placeholder="Contoh: Tegal" required class="w-full rounded-lg border-gray-300 p-3 border">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-green-600 text-white font-extrabold py-4 px-4 rounded-xl hover:bg-green-700 transition duration-300 text-lg shadow-lg">
                📄 GENERATE PDF SURAT SEHAT
            </button>
        </form>
    </div>

</body>
</html>