@extends('layouts.app')

@section('content')

@php
    $isLocked = false;
    $openAt = null;
    $isMaintenance = false;
    $isTicketExhausted = false;
    $secondsRemaining = 0;
    $timerActive = false;

    $tz = 'Asia/Jakarta';
    \Carbon\Carbon::setLocale('id');

    if (isset($setting)) {
        $isMaintenance = (bool) $setting->is_maintenance;
        $isTicketExhausted = (bool) $setting->is_ticket_exhausted;
        $openAt = $setting->spmb_open_at ? \Carbon\Carbon::parse($setting->spmb_open_at, $tz) : null;

        if ($openAt) {
            $secondsRemaining = now($tz)->diffInSeconds($openAt, false);
            if ($secondsRemaining > 0) {
                $timerActive = true;
            }
        }

        if ($isMaintenance || $isTicketExhausted || $timerActive) {
            $isLocked = true;
        }
    }

    $activeEventCount = \App\Models\Event::where('is_active', true)->count();
@endphp

@if($isLocked)
<div class="max-w-3xl mx-auto w-full px-4 relative z-10 my-10">
    <div class="bg-white/90 backdrop-blur-xl p-8 md:p-14 rounded-[2.5rem] shadow-2xl border border-white/60 text-center relative overflow-hidden">

        @if($isMaintenance)
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-red-100/60 blur-[80px] -z-10 rounded-full"></div>
        @elseif($isTicketExhausted)
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-yellow-100/60 blur-[80px] -z-10 rounded-full"></div>
        @else
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-blue-100/40 blur-[80px] -z-10 rounded-full"></div>
        @endif

        <img src="{{ asset('images/logo-adb.jpg') }}" alt="Logo" class="w-32 h-32 mx-auto mb-8 object-contain rounded-full shadow-lg border-4 border-white bg-white p-1">

        <h1 class="text-4xl md:text-5xl font-black tracking-tight mb-6 drop-shadow-sm uppercase">
            @if($isMaintenance)
                <span class="text-gray-800">SISTEM</span> <span class="text-red-600">MAINTENANCE</span>
            @elseif($isTicketExhausted)
                <span class="text-gray-800">KUOTA</span> <span class="text-yellow-500">HABIS / DITUTUP</span>
            @else
                <span class="text-gray-800">ANTRIAN</span> <span class="text-blue-700">BELUM DIBUKA</span>
            @endif
        </h1>

        @if($isMaintenance)
            <p class="text-lg md:text-xl font-bold text-gray-600 mb-10 max-w-xl mx-auto leading-relaxed">
                Mohon maaf, sistem pendaftaran antrian sedang dalam perbaikan teknis. Silakan kembali lagi nanti.
            </p>
        @elseif($isTicketExhausted)
            <div class="max-w-md mx-auto mb-10">
                <div class="relative group mb-6">
                    <img src="{{ asset('images/time_open.jpg') }}" alt="Jadwal Pembukaan Tiket" class="relative w-full rounded-2xl shadow-xl border-4 border-white object-contain">
                </div>
                <p class="text-lg font-bold text-gray-600 leading-relaxed">
                    @if($timerActive)
                        Tiket Antrian selanjutnya akan dibuka otomatis dalam:
                    @else
                        Mohon maaf, kuota antrian saat ini sudah penuh atau pendaftaran sementara ditutup.
                    @endif
                </p>
            </div>
        @else
            <p class="text-lg md:text-xl font-bold text-gray-600 mb-10 max-w-xl mx-auto leading-relaxed">
                Pendaftaran antrian {{ $event?->name ?? 'pelayanan' }} akan segera dibuka dalam waktu:
            </p>
        @endif

        @if(!$isMaintenance && $timerActive)
        <div class="inline-flex items-center gap-3 bg-blue-50 border border-blue-200 px-6 py-3 rounded-full text-blue-800 font-bold text-sm shadow-sm mb-6">
            <i class="fa-regular fa-calendar-check text-blue-600 text-lg"></i>
            {{ $openAt->translatedFormat('l, d F Y - H:i') }} WIB
        </div>

        <div class="grid grid-cols-4 gap-3 md:gap-6 max-w-2xl mx-auto mb-8">
            <div class="bg-slate-500 border border-slate-400 rounded-2xl overflow-hidden shadow-lg flex flex-col">
                <div class="p-4 md:p-6 pb-2 md:pb-3 flex-1 flex items-center justify-center">
                    <div id="cd-days" class="text-4xl md:text-6xl font-black text-white drop-shadow-md">00</div>
                </div>
                <div class="bg-blue-600 py-2 text-[10px] md:text-sm font-bold text-white uppercase tracking-widest shrink-0">Hari</div>
            </div>

            <div class="bg-slate-500 border border-slate-400 rounded-2xl overflow-hidden shadow-lg flex flex-col">
                <div class="p-4 md:p-6 pb-2 md:pb-3 flex-1 flex items-center justify-center">
                    <div id="cd-hours" class="text-4xl md:text-6xl font-black text-white drop-shadow-md">00</div>
                </div>
                <div class="bg-blue-600 py-2 text-[10px] md:text-sm font-bold text-white uppercase tracking-widest shrink-0">Jam</div>
            </div>

            <div class="bg-slate-500 border border-slate-400 rounded-2xl overflow-hidden shadow-lg flex flex-col">
                <div class="p-4 md:p-6 pb-2 md:pb-3 flex-1 flex items-center justify-center">
                    <div id="cd-minutes" class="text-4xl md:text-6xl font-black text-white drop-shadow-md">00</div>
                </div>
                <div class="bg-blue-600 py-2 text-[10px] md:text-sm font-bold text-white uppercase tracking-widest shrink-0">Menit</div>
            </div>

            <div class="bg-slate-500 border border-slate-400 rounded-2xl overflow-hidden shadow-lg flex flex-col">
                <div class="p-4 md:p-6 pb-2 md:pb-3 flex-1 flex items-center justify-center">
                    <div id="cd-seconds" class="text-4xl md:text-6xl font-black text-yellow-300 drop-shadow-md">00</div>
                </div>
                <div class="bg-blue-600 py-2 text-[10px] md:text-sm font-bold text-white uppercase tracking-widest shrink-0">Detik</div>
            </div>
        </div>
        @endif

        <div class="mt-8 text-center text-sm text-gray-500 font-semibold border-t border-gray-100 pt-6">
            Sudah mendaftar tapi lupa / hilang tiketnya?
            <button onclick="document.getElementById('reprintModal').classList.remove('hidden')" class="text-blue-600 hover:text-blue-800 hover:underline font-black ml-1 transition">
                <i class="fa-solid fa-print"></i> Cetak Ulang di sini
            </button>
        </div>

    </div>
</div>

@push('scripts')
@if(!$isMaintenance && $timerActive)
<script>
    let distance = {{ $secondsRemaining * 1000 }};

    let countdownInterval = setInterval(function () {
        distance -= 1000;

        if (distance <= 0) {
            clearInterval(countdownInterval);
            window.location.replace(window.location.href);
        } else {
            document.getElementById("cd-days").innerText = Math.floor(distance / (1000 * 60 * 60 * 24)).toString().padStart(2, '0');
            document.getElementById("cd-hours").innerText = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)).toString().padStart(2, '0');
            document.getElementById("cd-minutes").innerText = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60)).toString().padStart(2, '0');
            document.getElementById("cd-seconds").innerText = Math.floor((distance % (1000 * 60)) / 1000).toString().padStart(2, '0');
        }
    }, 1000);
</script>
@endif
@endpush

@else
<div class="max-w-4xl mx-auto w-full mb-20">
    <div class="bg-white p-8 md:p-12 rounded-3xl shadow-xl border border-gray-100 relative overflow-hidden">

        @if($activeEventCount > 1)
            <div class="mb-6">
                <a href="{{ route('ticket.create') }}" class="inline-flex items-center gap-2 text-sm font-bold text-blue-600 hover:text-blue-800 transition">
                    <i class="fa-solid fa-arrow-left"></i> Pilih Event Lain
                </a>
            </div>
        @endif

        <div class="text-center mb-10">
            <img src="{{ asset('images/logo-adb.jpg') }}" alt="Logo" class="w-24 h-24 mx-auto mb-5 object-contain drop-shadow-sm rounded-full">
            <span class="inline-block px-3 py-1 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-xs font-black uppercase tracking-widest mb-3">
                Pendaftaran Antrian
            </span>
            <h1 class="text-3xl md:text-4xl font-black text-gray-800 tracking-tight mb-2">
                {{ $event->name }}
            </h1>
            @if($event->subtitle)
                <h2 class="text-xl md:text-2xl font-bold text-gray-500">{{ $event->subtitle }}</h2>
            @endif
            @if($event->description)
                <p class="text-sm text-gray-500 max-w-2xl mx-auto mt-3">{{ $event->description }}</p>
            @endif
            <div class="w-20 h-1.5 bg-blue-600 mx-auto mt-6 rounded-full opacity-80"></div>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded-xl mb-8 font-bold flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-600 text-red-700 p-4 rounded-xl mb-8 font-bold">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="ticketForm" action="{{ route('ticket.store') }}" method="POST" class="space-y-6">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fa-solid fa-id-card text-blue-500 mr-1"></i> {{ $event->identifier_label }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nisn" required value="{{ old('nisn') }}" placeholder="{{ $event->identifier_placeholder ?: 'Masukkan ' . $event->identifier_label . '...' }}" class="w-full p-3.5 bg-gray-50 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition font-mono font-bold text-gray-800">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        <i class="fa-solid fa-user text-blue-500 mr-1"></i> {{ $event->name_label }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_cpd" required value="{{ old('nama_cpd') }}" placeholder="Ketik {{ $event->name_label }}..." class="w-full p-3.5 bg-gray-50 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition uppercase font-bold text-gray-800">
                </div>

                @foreach($event->form_fields ?? [] as $field)
                    @php
                        $key = $field['key'] ?? '';
                        $label = $field['label'] ?? $key;
                        $type = $field['type'] ?? 'text';
                        $isRequired = !empty($field['required']);
                        $placeholder = $field['placeholder'] ?? '';
                        $options = $field['options'] ?? [];
                        $oldValue = old("fields.$key");
                        $isFullWidth = in_array($type, ['textarea', 'checkbox'], true);
                    @endphp
                    <div class="{{ $isFullWidth ? 'md:col-span-2' : '' }}">
                        <label class="block text-sm font-bold text-gray-700 mb-2">
                            {{ $label }}
                            @if($isRequired)<span class="text-red-500">*</span>@else <span class="text-xs text-gray-400 font-normal">(Opsional)</span>@endif
                        </label>

                        @if($type === 'textarea')
                            <textarea name="fields[{{ $key }}]" rows="3" {{ $isRequired ? 'required' : '' }} placeholder="{{ $placeholder }}" class="w-full p-3.5 bg-gray-50 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition font-bold text-gray-800">{{ $oldValue }}</textarea>
                        @elseif($type === 'select')
                            <select name="fields[{{ $key }}]" {{ $isRequired ? 'required' : '' }} class="w-full p-3.5 bg-gray-50 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition font-bold text-gray-800 cursor-pointer">
                                <option value="">-- Pilih {{ $label }} --</option>
                                @foreach($options as $opt)
                                    <option value="{{ $opt }}" {{ $oldValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>
                        @elseif($type === 'checkbox')
                            @php $oldArr = is_array($oldValue) ? $oldValue : []; @endphp
                            <div class="flex flex-wrap gap-4 p-3.5 bg-gray-50 border border-gray-300 rounded-xl items-center min-h-[54px]" @if($isRequired) data-required-checkbox-group="{{ $label }}" @endif>
                                @foreach($options as $opt)
                                    <label class="flex items-center gap-2 cursor-pointer font-bold text-gray-800 hover:text-blue-600 transition">
                                        <input type="checkbox" name="fields[{{ $key }}][]" value="{{ $opt }}" {{ in_array($opt, $oldArr, true) ? 'checked' : '' }} class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500 cursor-pointer">
                                        {{ $opt }}
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <input type="{{ $type }}" name="fields[{{ $key }}]" value="{{ $oldValue }}" {{ $isRequired ? 'required' : '' }} placeholder="{{ $placeholder }}" class="w-full p-3.5 bg-gray-50 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition font-bold text-gray-800">
                        @endif
                    </div>
                @endforeach
            </div>

            @if(!empty($event->confirmation_text))
                <div class="mt-8 bg-yellow-50 border-2 border-yellow-300 p-4 md:p-5 rounded-xl flex items-start gap-4 shadow-sm">
                    <input type="checkbox" id="check_confirmation" class="mt-1 w-6 h-6 text-blue-600 rounded border-gray-400 focus:ring-blue-500 cursor-pointer flex-shrink-0">
                    <label for="check_confirmation" class="cursor-pointer select-none">
                        <span class="block text-sm md:text-base font-bold text-gray-800">{{ $event->confirmation_text }}</span>
                    </label>
                </div>
            @endif

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black text-xl py-5 rounded-xl transition shadow-lg mt-4 flex justify-center items-center gap-3">
                <i class="fa-solid fa-ticket text-2xl"></i> AMBIL TIKET SEKARANG
            </button>
        </form>

        <div class="mt-8 text-center text-sm text-gray-500 font-semibold border-t border-gray-100 pt-6">
            Sudah mendaftar tapi lupa / hilang tiketnya?
            <button onclick="document.getElementById('reprintModal').classList.remove('hidden')" class="text-blue-600 hover:text-blue-800 hover:underline font-black ml-1 transition">
                <i class="fa-solid fa-print"></i> Cetak Ulang di sini
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const ticketForm = document.getElementById('ticketForm');
    if (ticketForm) {
        ticketForm.addEventListener('submit', function (e) {
            const confBox = document.getElementById('check_confirmation');
            if (confBox && !confBox.checked) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Konfirmasi Diperlukan',
                    text: 'Silakan centang kotak pernyataan konfirmasi terlebih dahulu.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }

            const requiredGroups = document.querySelectorAll('[data-required-checkbox-group]');
            for (const group of requiredGroups) {
                const checked = group.querySelectorAll('input[type="checkbox"]:checked').length;
                if (checked === 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data Belum Lengkap',
                        text: 'Pilih minimal satu opsi pada "' + group.getAttribute('data-required-checkbox-group') + '".',
                        confirmButtonColor: '#2563eb'
                    });
                    return;
                }
            }
        });
    }
</script>
@endpush
@endif

<div id="reprintModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden flex items-center justify-center z-50 px-4">
    <div class="bg-white p-8 rounded-3xl w-full max-w-sm text-center relative shadow-2xl">
        <button onclick="document.getElementById('reprintModal').classList.add('hidden')" class="absolute top-5 right-5 text-gray-400 hover:text-red-500 transition bg-gray-100 rounded-full w-8 h-8 flex items-center justify-center"><i class="fa-solid fa-times"></i></button>

        <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-print text-4xl text-blue-600"></i>
        </div>
        <h3 class="text-2xl font-black text-gray-800 mb-2">Cetak Ulang</h3>
        <p class="text-sm text-gray-500 mb-8 font-semibold">Masukkan {{ $event?->identifier_label ?? 'Identitas' }} Anda untuk menarik kembali tiket yang telah didaftarkan.</p>

        <form id="reprintForm" class="space-y-4">
            <div>
                <input type="text" id="reprintNisn" placeholder="Ketik {{ $event?->identifier_label ?? 'Identitas' }}..." required class="w-full p-4 bg-gray-50 border border-gray-300 rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition font-mono text-center text-xl font-black text-gray-800 tracking-widest">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-black text-lg py-4 rounded-xl hover:bg-blue-700 transition shadow-md">
                <i class="fa-solid fa-search mr-2"></i> Cari Tiket Saya
            </button>
        </form>
        <div id="reprintError" class="hidden text-red-600 text-sm font-bold mt-4 bg-red-50 p-3 rounded-lg border border-red-200"></div>
    </div>
</div>

@push('scripts')
<script>
    const reprintForm = document.getElementById('reprintForm');
    if (reprintForm) {
        reprintForm.addEventListener('submit', function (e) {
            e.preventDefault();
            let nisn = document.getElementById('reprintNisn').value;
            let errorBox = document.getElementById('reprintError');
            let btn = this.querySelector('button');

            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mencari...';
            btn.disabled = true;
            errorBox.classList.add('hidden');

            fetch("{{ route('ticket.reprint') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ nisn: nisn, event_id: {{ $event?->id ?? 'null' }} })
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

@endsection
