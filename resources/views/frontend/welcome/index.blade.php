@extends('layouts.app')
@section('content')
<div class="min-h-[85vh] w-full flex items-center justify-center p-4 sm:p-6 lg:p-8 relative">
    
    <div id="formCard" class="max-w-5xl w-full bg-white rounded-[2rem] shadow-2xl overflow-hidden flex flex-col md:flex-row relative z-10 border border-gray-100 transition-all duration-300">
        
        <div class="md:w-5/12 bg-gradient-to-br from-blue-700 via-blue-800 to-indigo-900 p-10 text-white relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-white opacity-5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 -mb-16 -ml-16 w-48 h-48 bg-blue-400 opacity-20 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex flex-col h-full min-h-[450px]">
                
                <div>
                    <img src="{{ asset('images/logo-adb.jpg') }}" alt="Logo ADB" class="w-20 h-20 drop-shadow-2xl rounded-full mb-6 border-2 border-white/30">
                    <h2 class="text-3xl font-black mb-2 tracking-tight leading-tight">
                        PORTAL <br><span class="text-blue-300">PENGUMUMAN</span> SPMB
                    </h2>
                    <p class="text-blue-100 text-sm leading-relaxed font-medium">
                        Selamat datang di layanan Pengumuman dan Daftar Ulang SMK Negeri 1 Adiwerna Tahun 2026.
                    </p>
                </div>

                <div class="flex-1 flex flex-col justify-center py-6">
                    <div class="w-full bg-white/10 border border-white/20 rounded-2xl p-5 backdrop-blur-sm shadow-xl relative overflow-hidden">
                        <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-400/30 rounded-full blur-xl"></div>
                        
                        <p class="text-blue-200 text-[11px] font-black uppercase tracking-widest mb-3 border-b border-blue-400/30 pb-2 relative z-10">
                            <i class="fa-regular fa-calendar-check mr-2 text-blue-300"></i> Jadwal Daftar Ulang
                        </p>
                        
                        @php
                            $setting = \App\Models\Setting::first();
                            $jadwalRaw = $setting && !empty($setting->jadwal_daftar_ulang) ? $setting->jadwal_daftar_ulang : null;
                            
                            $jadwalArray = [];
                            if ($jadwalRaw) {
                                $decoded = json_decode($jadwalRaw, true);
                                if (json_last_error() === JSON_ERROR_NONE) $jadwalArray = $decoded;
                            }

                            if (empty($jadwalArray)) {
                                $jadwalArray = [
                                    'TPFL' => 'Senin, 22 Juni 2026', 'TK' => 'Senin, 22 Juni 2026', 'TM' => 'Senin, 22 Juni 2026',
                                    'DPIB' => 'Selasa, 23 Juni 2026', 'TE' => 'Selasa, 23 Juni 2026',
                                    'TO' => 'Rabu, 24 Juni 2026', 'TJKT' => 'Rabu, 24 Juni 2026',
                                ];
                            }
                            
                            $groupedJadwal = [];
                            foreach($jadwalArray as $jur => $tgl) {
                                $tglBersih = strtoupper(trim($tgl));
                                if(!empty($tglBersih)){
                                    $groupedJadwal[$tglBersih][] = $jur;
                                }
                            }
                        @endphp
                        
                        <div class="space-y-3 relative z-10">
                            @foreach($groupedJadwal as $tgl => $jurusans)
                                <div class="flex items-start gap-3 bg-white/5 p-3 rounded-xl border border-white/10">
                                    <div class="mt-1 w-6 h-6 rounded-full bg-blue-500/40 flex items-center justify-center shrink-0 border border-blue-400/50">
                                        <i class="fa-solid fa-calendar-day text-[10px] text-white"></i>
                                    </div>
                                    <div>
                                        <span class="block text-white text-[13px] font-black tracking-wide mb-1">{{ $tgl }}</span>
                                        <span class="block text-[11px] text-blue-200 font-bold tracking-widest leading-relaxed">
                                            ( {{ implode(', ', $jurusans) }} )
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="mt-auto space-y-5 relative">
                    <div class="absolute left-[15px] top-4 bottom-4 w-0.5 bg-blue-500/30"></div>
                    <div class="flex items-center gap-4 relative">
                        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center font-bold text-sm border-2 border-blue-400 shadow-lg z-10">1</div>
                        <span class="text-sm font-medium text-blue-50">Cari Data Login</span>
                    </div>
                    <div class="flex items-center gap-4 relative">
                        <div class="w-8 h-8 rounded-full bg-indigo-800/80 flex items-center justify-center font-bold text-sm border border-blue-500/50 z-10 text-blue-300">2</div>
                        <span class="text-sm font-medium text-blue-200">Lengkapi Biodata Diri</span>
                    </div>
                    <div class="flex items-center gap-4 relative">
                        <div class="w-8 h-8 rounded-full bg-indigo-800/80 flex items-center justify-center font-bold text-sm border border-blue-500/50 z-10 text-blue-300">3</div>
                        <span class="text-sm font-medium text-blue-200">Cetak Berkas Daftar Ulang</span>
                    </div>
                </div>

            </div>
        </div>

        <div class="md:w-7/12 p-8 md:p-12 bg-white flex flex-col justify-center">
            <div class="text-center md:text-left mb-8">
                <h3 class="text-2xl font-black text-gray-800">Cek Status Penerimaan</h3>
                <p class="text-sm text-gray-500 mt-2 font-medium">Silakan masukkan NISN <b class="text-blue-600">ATAU</b> Nomor Pendaftaran Anda.</p>
            </div>

            <form id="checkForm" onsubmit="submitCheck(event)">
                @csrf
                <div class="mb-8">
                    <label class="block text-[11px] font-black text-gray-500 uppercase tracking-widest mb-3">NISN / Nomor Pendaftaran</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <i class="fa-solid fa-id-card text-gray-400 group-focus-within:text-blue-600 transition-colors text-lg"></i>
                        </div>
                        <input type="text" name="nisn" id="nisn_input" required maxlength="16" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();" placeholder="Ketik data di sini..." 
                               class="block w-full pl-14 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-gray-800 text-xl font-mono font-bold tracking-widest focus:bg-white focus:ring-4 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-all placeholder:text-gray-300 placeholder:font-sans placeholder:font-normal placeholder:tracking-normal">
                    </div>
                </div>

                <button type="submit" id="btnSubmit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black py-4 rounded-2xl shadow-lg hover:shadow-indigo-500/30 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-3 uppercase tracking-wider text-sm">
                    <i class="fa-solid fa-magnifying-glass text-lg"></i> Cek Pengumuman Sekarang
                </button>
            </form>

            <div class="mt-8 flex items-start gap-3 bg-amber-50 p-4 rounded-xl border border-amber-100">
                <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
                <p class="text-[11px] text-amber-800 leading-relaxed font-bold">
                    Gunakan data NISN atau Nomor Pendaftaran yang sesuai dengan hasil seleksi Provinsi Jawa Tengah.
                </p>
            </div>
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

<script>
    @if(session('error')) Swal.fire({ icon: 'error', title: 'Akses Ditolak', text: '{!! session('error') !!}', confirmButtonColor: '#e3342f' }); @endif

    function submitCheck(e) {
        e.preventDefault();
        let btn = document.getElementById('btnSubmit');
        let card = document.getElementById('formCard');
        let originalText = btn.innerHTML;
        let nisn = document.getElementById('nisn_input').value;

        // 🔥 REVISI MUTLAK JAVASCRIPT: Wajib 10 atau 16 Karakter 🔥
        if(nisn.length !== 10 && nisn.length !== 16) {
            Swal.fire('Validasi Gagal', 'Data harus berjumlah 10 karakter (NISN) atau 16 karakter (Nomor Pendaftaran)!', 'warning');
            return;
        }

        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-lg"></i> Memeriksa Data...';
        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');

        fetch("{{ route('welcome.check') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ nisn: nisn })
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');

            if(data.success) {
                var duration = 4 * 1000;
                var animationEnd = Date.now() + duration;
                var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 999999 };

                function randomInRange(min, max) { return Math.random() * (max - min) + min; }

                var interval = setInterval(function() {
                    var timeLeft = animationEnd - Date.now();
                    if (timeLeft <= 0) return clearInterval(interval);
                    var particleCount = 50 * (timeLeft / duration);
                    confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
                    confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
                }, 250);

                confetti({ particleCount: 120, angle: 60, spread: 70, origin: { x: 0, y: 0.9 }, zIndex: 999999 });
                confetti({ particleCount: 120, angle: 120, spread: 70, origin: { x: 1, y: 0.9 }, zIndex: 999999 });

                Swal.fire({
                    title: 'SELAMAT!',
                    html: `Anda dinyatakan <b class="text-green-600 uppercase text-lg tracking-wide">DITERIMA</b> di SMKN 1 Adiwerna.`,
                    icon: 'success',
                    showConfirmButton: true,
                    confirmButtonText: 'Lanjut Isi Formulir <i class="fa-solid fa-arrow-right ml-1"></i>',
                    confirmButtonColor: '#2563eb',
                    allowOutsideClick: false,
                    customClass: {
                        popup: 'rounded-3xl shadow-2xl border border-gray-100',
                        confirmButton: 'rounded-xl font-bold uppercase tracking-wider px-6 py-3 shadow-lg hover:shadow-blue-500/30 hover:-translate-y-0.5 transition-all'
                    },
                    didOpen: () => {
                        const backdrop = document.querySelector('.swal2-backdrop-show');
                        if(backdrop){
                            backdrop.style.backdropFilter = 'blur(8px)';
                            backdrop.style.background = 'rgba(0, 0, 0, 0.6)';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = data.redirect;
                });
            } else {
                card.classList.add('animate-shake');
                setTimeout(() => card.classList.remove('animate-shake'), 500);

                confetti({
                    particleCount: 50, angle: 90, spread: 80, origin: { x: 0.5, y: 0 }, 
                    colors: ['#94a3b8', '#64748b', '#cbd5e1', '#f59e0b'], drift: 0, ticks: 150, gravity: 0.9, startVelocity: 5, zIndex: 999999
                });

                Swal.fire({
                    title: 'Data Tidak Ditemukan',
                    text: data.message,
                    icon: 'error',
                    confirmButtonColor: '#e3342f',
                    allowOutsideClick: false,
                    customClass: { popup: 'rounded-3xl shadow-2xl border border-red-100' },
                    didOpen: () => {
                        const backdrop = document.querySelector('.swal2-backdrop-show');
                        if(backdrop){ backdrop.style.backdropFilter = 'blur(8px)'; backdrop.style.background = 'rgba(15, 23, 42, 0.75)'; }
                    }
                });
            }
        }).catch(err => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            Swal.fire('Error', 'Terjadi kesalahan jaringan, silakan coba lagi.', 'error');
        });
    }
</script>
<style>
    @keyframes horizontalShake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
        20%, 40%, 60%, 80% { transform: translateX(8px); }
    }
    .animate-shake {
        animation: horizontalShake 0.5s ease-in-out;
        box-shadow: 0 0 25px rgba(239, 68, 68, 0.2); 
    }
</style>
@endpush