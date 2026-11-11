@extends('layouts.app')
@section('content')
<div class="min-h-[85vh] flex flex-col items-center justify-center py-10 px-4">
    <div class="max-w-xl mx-auto w-full">
        <div class="bg-white p-8 rounded-3xl shadow-2xl border border-gray-100 text-center relative overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-3 bg-gradient-to-r from-green-400 to-emerald-600"></div>
            
            <div class="w-24 h-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-5xl mx-auto mb-6 shadow-inner">
                <i class="fa-solid fa-check"></i>
            </div>

            <h1 class="text-3xl font-black text-gray-800 uppercase tracking-tight mb-2">PENGISIAN BERHASIL!</h1>
            <p class="text-gray-600 font-medium mb-6">Terima kasih <b class="text-gray-800">{{ $siswa->nama }}</b>, data Anda telah tersimpan sementara untuk dicetak.</p>

            <div class="bg-yellow-50 border-2 border-yellow-400 border-dashed rounded-2xl p-6 mb-8 relative">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-yellow-400 text-yellow-900 text-[10px] font-black uppercase px-3 py-1 rounded-full tracking-widest shadow-sm">
                    JADWAL DAFTAR ULANG ANDA
                </span>
                <div class="text-2xl md:text-3xl font-black text-yellow-700 tracking-tighter uppercase mt-2">
                    {{ $jadwal }}
                </div>
                <p class="text-xs text-yellow-600 font-bold mt-2">Wajib Hadir Ke Sekolah Sesuai Tanggal di Atas!</p>
            </div>

            <a href="{{ route('welcome.download', $siswa->nisn) }}" class="block w-full bg-red-600 hover:bg-red-700 text-white font-black py-5 rounded-xl shadow-lg transition flex items-center justify-center gap-3 uppercase tracking-wide text-lg mb-4">
                <i class="fa-solid fa-file-pdf text-2xl"></i> DOWNLOAD BERKAS PDF SEKARANG
            </a>

            <div class="bg-gray-50 p-4 rounded-xl text-left border border-gray-200">
                <h3 class="text-xs font-black text-gray-800 uppercase mb-2"><i class="fa-solid fa-circle-exclamation text-red-500 mr-1"></i> WAJIB DIBACA:</h3>
                <ul class="text-[11px] text-gray-600 space-y-1 list-disc pl-4 font-medium leading-relaxed">
                    <li>Klik tombol merah di atas untuk mengunduh Dokumen Daftar Ulang Anda.</li>
                    <li>Cetak (Print) dokumen tersebut menggunakan kertas ukuran <b>A4</b>.</li>
                    <li>Gambarkan denah lokasi rumah Anda secara manual di halaman ke-3.</li>
                    <li>Siapkan <b>Materai Rp 10.000</b> untuk ditempel pada Surat Pernyataan di halaman ke-4, lalu ditandatangani basah.</li>
                    <li class="font-bold text-gray-900">Dokumen Daftar Ulang (Biodata, Denah, Surat Pernyataan, & Verifikasi di Halaman 2, 3, 4, 5 PDF ini).</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection