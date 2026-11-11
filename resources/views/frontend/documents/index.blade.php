<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pusat Generator Dokumen SPMB</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-adb.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center py-12 px-4">

    <div class="max-w-4xl w-full">
        <div class="text-center mb-10">
            <h1 class="text-3xl md:text-4xl font-extrabold text-blue-900 mb-4">Pusat Layanan Dokumen SPMB Mandiri</h1>
            <p class="text-gray-600 text-lg">Pilih jenis dokumen yang ingin Anda buat. Sistem akan otomatis menyusunnya ke dalam format PDF yang siap dicetak dan ditandatangani.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-xl transition duration-300">
                <div class="p-6">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Surat Pernyataan Kebenaran Dokumen</h3>
                    <p class="text-gray-600 mb-6 text-sm h-16">Dokumen wajib yang menyatakan bahwa seluruh berkas persyaratan yang diserahkan adalah benar dan asli.</p>
                    
                    <a href="{{ route('dokumen.kebenaran.form') }}" class="inline-block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                        Buat Dokumen Ini &rarr;
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-xl transition duration-300">
                <div class="p-6">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Surat Pernyataan Sehat</h3>
                    <p class="text-gray-600 mb-6 text-sm h-16">Pernyataan kondisi sehat khusus untuk pemenuhan syarat program keahlian SMK Negeri.</p>
                    
                    <a href="{{ route('dokumen.sehat.form') }}" class="inline-block w-full text-center bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                        Buat Dokumen Ini &rarr;
                    </a>
                </div>
            </div>
{{-- 
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-xl transition duration-300">
                <div class="p-6">
                    <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10l4 4v10a2 2 0 01-2 2z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-6 4h6m-6-8h.01"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Surat Pernyataan Daftar Ulang</h3>
                    <p class="text-gray-600 mb-6 text-sm h-16">Dokumen pernyataan kebenaran data dan kesediaan mematuhi tata tertib bagi siswa yang dinyatakan diterima.</p>
                    
                    <a href="{{ route('dokumen.kebenaran-du.form') }}" class="inline-block w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                        Buat Dokumen Ini &rarr;
                    </a>
                </div>
            </div>
--}}
            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-xl transition duration-300">
                <div class="p-6">
                    <div class="w-12 h-12 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Surat Pernyataan ATS</h3>
                    <p class="text-gray-600 mb-6 text-sm h-16">Pernyataan bagi pendaftar Anak Tidak Sekolah (ATS) yang memastikan bahwa saat ini tidak terdaftar di Dapodik SMA/SMK manapun.</p>
                    
                    <a href="{{ route('dokumen.ats.form') }}" class="inline-block w-full text-center bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                        Buat Dokumen Ini &rarr;
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200 hover:shadow-xl transition duration-300">
                <div class="p-6 border-t-4 border-red-500">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center mb-4">
                        <i class="fa-solid fa-eye text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Surat Pernyataan Tidak Buta Warna</h3>
                    <p class="text-gray-600 mb-6 text-sm h-16">Pernyataan bebas buta warna yang diawali dengan Screening Tes Ishihara Interaktif secara online.</p>
                    
                    <a href="{{ route('dokumen.butawarna.form') }}" class="inline-block w-full text-center bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                        Mulai Tes & Buat Dokumen &rarr;
                    </a>
                </div>
            </div>

        </div>

        <div class="mt-12 text-center">
            <a href="/" class="text-blue-600 hover:underline font-medium">&larr; Kembali ke Beranda</a>
        </div>
    </div>

</body>
</html>