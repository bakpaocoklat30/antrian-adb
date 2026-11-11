@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto w-full my-8">
    <div class="bg-white p-8 md:p-12 rounded-3xl shadow-xl border border-gray-100">

        <div class="text-center mb-10">
            <img src="{{ asset('images/logo-adb.jpg') }}" alt="Logo Instansi" class="w-24 h-24 mx-auto mb-5 object-contain drop-shadow-sm rounded-full">
            <h1 class="text-3xl md:text-4xl font-black text-gray-800 tracking-tight mb-2">
                Layanan Antrian <span class="text-blue-600">di-SAPA</span>
            </h1>
            <p class="text-base md:text-lg font-semibold text-gray-500">
                @if($activeEvents->count() > 0)
                    Silakan pilih event atau jenis pelayanan yang ingin Anda ikuti.
                @else
                    Belum ada event antrian yang dibuka saat ini.
                @endif
            </p>
            <div class="w-20 h-1.5 bg-blue-600 mx-auto mt-6 rounded-full opacity-80"></div>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded-xl mb-8 font-bold flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        @if($activeEvents->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                @foreach($activeEvents as $ev)
                    @php
                        $sisaKuota = max(0, (int) ($ev->total_quota ?? 0) - (int) ($ev->total_booked ?? 0));
                    @endphp
                    <div class="group bg-gray-50 hover:bg-white p-6 rounded-2xl border-2 border-gray-200 hover:border-blue-500 transition shadow-sm hover:shadow-xl flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <span class="px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider {{ $sisaKuota > 0 ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-yellow-100 text-yellow-700 border border-yellow-200' }}">
                                    {{ $sisaKuota > 0 ? "Sisa Kuota: {$sisaKuota}" : 'Cek Jadwal / Kuota' }}
                                </span>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                                    ID: {{ $ev->identifier_label }}
                                </span>
                            </div>

                            <h2 class="text-xl font-black text-gray-800 group-hover:text-blue-700 transition mb-1">
                                {{ $ev->name }}
                            </h2>
                            @if($ev->subtitle)
                                <p class="text-xs font-bold text-blue-600 uppercase tracking-wider mb-3">{{ $ev->subtitle }}</p>
                            @endif
                            @if($ev->description)
                                <p class="text-sm text-gray-600 leading-relaxed mb-6">{{ $ev->description }}</p>
                            @endif
                        </div>

                        <a href="{{ route('ticket.event', $ev->slug) }}" class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-black py-3.5 px-5 rounded-xl transition shadow-md">
                            <i class="fa-solid fa-ticket"></i> Ambil Antrian <i class="fa-solid fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-10 text-center text-gray-500 mb-8">
                <i class="fa-solid fa-calendar-xmark text-5xl text-slate-400 mb-4"></i>
                <p class="font-bold text-lg text-gray-700">Tidak Ada Event Aktif</p>
                <p class="text-sm mt-1">Silakan kembali lagi nanti atau hubungi panitia penyelenggara.</p>
            </div>
        @endif

        <div class="text-center text-sm text-gray-500 font-semibold border-t border-gray-100 pt-6">
            Sudah mendaftar tapi lupa / hilang tiketnya?
            <button onclick="document.getElementById('reprintModal').classList.remove('hidden')" class="text-blue-600 hover:text-blue-800 hover:underline font-black ml-1 transition">
                <i class="fa-solid fa-print"></i> Cetak Ulang di sini
            </button>
        </div>
    </div>
</div>

<div id="reprintModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden flex items-center justify-center z-50 px-4">
    <div class="bg-white p-8 rounded-3xl w-full max-w-sm text-center relative shadow-2xl">
        <button onclick="document.getElementById('reprintModal').classList.add('hidden')" class="absolute top-5 right-5 text-gray-400 hover:text-red-500 transition bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center"><i class="fa-solid fa-times"></i></button>

        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-print text-4xl text-blue-600"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-800 mb-2">Cetak Ulang Tiket</h3>
        <p class="text-sm text-gray-500 mb-6 font-semibold">Pilih event (opsional) lalu masukkan nomor identitas yang Anda daftarkan.</p>

        <form id="reprintForm" class="space-y-4">
            @if($activeEvents->count() > 0)
            <div>
                <select id="reprintEventId" class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 text-sm font-bold text-gray-700">
                    <option value="">-- Semua Event --</option>
                    @foreach($activeEvents as $ev)
                        <option value="{{ $ev->id }}">{{ $ev->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div>
                <input type="text" id="reprintNisn" placeholder="Ketik Nomor Identitas..." required class="w-full p-4 bg-gray-50 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition font-mono text-center text-lg font-black text-gray-800 tracking-widest">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-black text-lg py-4 rounded-xl hover:bg-blue-700 transition shadow-md">
                <i class="fa-solid fa-search mr-2"></i> Cari Tiket Saya
            </button>
        </form>
        <div id="reprintError" class="hidden text-red-600 text-sm font-bold mt-4 bg-red-50 p-3 rounded-lg border border-red-200"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const reprintForm = document.getElementById('reprintForm');
    if (reprintForm) {
        reprintForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let nisn = document.getElementById('reprintNisn').value;
            let eventEl = document.getElementById('reprintEventId');
            let eventId = eventEl ? eventEl.value : '';
            let errorBox = document.getElementById('reprintError');
            let btn = this.querySelector('button');

            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mencari...';
            btn.disabled = true;
            errorBox.classList.add('hidden');

            fetch("{{ route('ticket.reprint') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ nisn: nisn, event_id: eventId || null })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    errorBox.innerHTML = '<i class="fa-solid fa-circle-xmark mr-1"></i> ' + data.message;
                    errorBox.classList.remove('hidden');
                    btn.innerHTML = '<i class="fa-solid fa-search mr-2"></i> Cari Tiket Saya';
                    btn.disabled = false;
                }
            })
            .catch(() => {
                errorBox.innerHTML = '<i class="fa-solid fa-wifi mr-1"></i> Terjadi kesalahan koneksi.';
                errorBox.classList.remove('hidden');
                btn.innerHTML = '<i class="fa-solid fa-search mr-2"></i> Cari Tiket Saya';
                btn.disabled = false;
            });
        });
    }
</script>
@endpush
