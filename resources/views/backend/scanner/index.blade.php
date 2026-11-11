@extends('layouts.app')
@section('content')
<div class="max-w-md mx-auto w-full relative">

    <div class="bg-white p-6 rounded-2xl shadow-xl border border-gray-100 text-center mb-4 relative z-10">
        <h2 class="text-2xl font-black text-gray-800">Scanner Check-In Antrian</h2>
        <p class="text-sm text-gray-500 mb-4">Scan QR Code tiket siswa atau ketik NISN / Nomor Tiket manual.</p>

        <div id="reader" class="w-full overflow-hidden rounded-xl border-4 border-blue-100 mb-4 bg-black aspect-square flex items-center justify-center relative transition-colors duration-300">
            <i class="fa-solid fa-qrcode text-5xl text-gray-600 absolute z-0 opacity-50"></i>
        </div>

        <button id="startBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-4 rounded-xl shadow-lg transition mb-3 flex items-center justify-center gap-2 text-lg">
            <i class="fa-solid fa-camera"></i> Mulai Kamera
        </button>

        <button id="stopBtn" class="w-full bg-red-100 hover:bg-red-200 text-red-600 font-bold py-3 px-4 rounded-xl transition mb-3 hidden flex items-center justify-center gap-2">
            <i class="fa-solid fa-stop"></i> Matikan Kamera
        </button>

        <div class="flex items-center gap-2 my-3">
            <hr class="flex-1 border-gray-200">
            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Atau Input Manual</span>
            <hr class="flex-1 border-gray-200">
        </div>

        <form onsubmit="submitManualCheckin(event)" class="flex gap-2 mb-4">
            <input type="text" id="manualTicketInput" placeholder="NISN atau No. Tiket..." class="flex-1 px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm font-bold text-center focus:ring-2 focus:ring-blue-500 outline-none">
            <button type="submit" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-3 rounded-xl font-bold text-sm transition">
                <i class="fa-solid fa-check"></i> Check-In
            </button>
        </form>

        <div id="resultBox" class="hidden p-4 rounded-lg font-bold text-sm mb-4"></div>

        <button onclick="openGoShowModal()" class="w-full mt-2 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-ticket"></i> Buat Tiket Go-Show (Offline)
        </button>

        <form action="{{ route('logout') }}" method="POST" class="mt-6 border-t pt-4" id="logoutForm">
            @csrf
            <button type="button" onclick="confirmLogout()" class="text-red-500 text-sm font-semibold hover:underline">
                <i class="fa-solid fa-power-off"></i> Logout Petugas
            </button>
        </form>
    </div>

    <div id="goShowModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto flex flex-col">

            <div class="sticky top-0 bg-white p-5 border-b border-gray-100 flex justify-between items-center z-10 rounded-t-2xl">
                <div>
                    <h3 class="text-xl font-black text-gray-800">Form Tiket Go-Show</h3>
                    <p class="text-xs text-gray-500">Pendaftaran Offline / Tanpa Tiket</p>
                </div>
                <button onclick="closeGoShowModal()" class="w-8 h-8 flex items-center justify-center bg-gray-100 hover:bg-red-100 hover:text-red-600 rounded-full text-gray-500 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="p-5">
                <form id="goShowForm" onsubmit="submitGoShow(event)">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">NISN *</label>
                            <input type="number" name="nisn" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none text-sm bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Nama Lengkap CPD *</label>
                            <input type="text" name="nama_cpd" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none text-sm bg-gray-50">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Asal Sekolah *</label>
                            <input type="text" name="asal_sekolah" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none text-sm bg-gray-50">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-2">Data Kesejahteraan (Opsional)</label>
                            <div class="flex items-center gap-4 bg-gray-50 px-4 py-3 rounded-xl border border-gray-200">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="cb_kip" value="KIP" class="w-4 h-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                    <span class="text-sm font-bold text-gray-700">Punya KIP</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="cb_dtks" value="DTKS" class="w-4 h-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                    <span class="text-sm font-bold text-gray-700">Punya DTKS</span>
                                </label>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="w-1/2">
                                <label class="block text-xs font-bold text-gray-600 mb-1">No. WhatsApp *</label>
                                <input type="number" name="whatsapp" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none text-sm bg-gray-50">
                            </div>
                            <div class="w-1/2">
                                <label class="block text-xs font-bold text-gray-600 mb-1">Email Aktif *</label>
                                <input type="email" name="email" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none text-sm bg-gray-50">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Alamat Rumah Lengkap *</label>
                            <textarea name="alamat" required rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-orange-500 outline-none text-sm bg-gray-50"></textarea>
                        </div>
                    </div>

                    <button type="submit" id="btnSubmitGoShow" class="w-full mt-6 bg-orange-600 hover:bg-orange-700 text-white font-bold py-4 rounded-xl shadow-lg transition flex justify-center items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Buat & Masukkan Antrian
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function playBeep(isSuccess) {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = isSuccess ? 'sine' : 'sawtooth';
            osc.frequency.value = isSuccess ? 880 : 240;
            gain.gain.setValueAtTime(0.15, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + (isSuccess ? 0.2 : 0.35));
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + (isSuccess ? 0.2 : 0.35));
        } catch (e) {}
    }

    function confirmLogout() {
        Swal.fire({
            title: 'Logout Petugas?',
            text: "Anda akan mengakhiri sesi jaga di Pos Scanner.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e3342f',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Logout!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logoutForm').submit();
            }
        });
    }

    let html5QrCode;
    let isProcessing = false;

    function processCheckin(rawInput, fromCamera) {
        if (isProcessing) return;
        isProcessing = true;

        if (fromCamera && html5QrCode && html5QrCode.getState() === 2) {
            html5QrCode.pause(true);
        }

        const resultBox = document.getElementById('resultBox');
        resultBox.className = 'p-4 rounded-lg font-bold text-sm mb-4 bg-yellow-100 text-yellow-700 relative z-10 shadow-md';
        resultBox.innerHTML = '<i class="fa-solid fa-spinner fa-spin border-0"></i> Sedang Memproses...';
        resultBox.classList.remove('hidden');

        fetch("{{ route('admin.scanner.checkin') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ ticket_number: rawInput })
        })
        .then(response => response.json())
        .then(data => {
            const resumeScanner = () => {
                resultBox.classList.add('hidden');
                if (fromCamera && html5QrCode && html5QrCode.getState() === 3) {
                    html5QrCode.resume();
                }
                isProcessing = false;
            };

            if (data.success) {
                playBeep(true);
                let sisaAntrian = data.antrian_di_depan !== undefined ? data.antrian_di_depan : '-';
                let estimasi = data.estimasi_waktu !== undefined ? data.estimasi_waktu + ' Menit' : '-';
                let nomorPendek = data.ticket_short || rawInput.split('-')[0];

                Swal.fire({
                    title: 'Check-In Berhasil!',
                    html: `
                        <div class="text-left mt-2">
                            <div class="bg-blue-50 p-3 rounded-xl border border-blue-200 mb-4 text-sm text-blue-800 flex items-center gap-3 shadow-sm">
                                <i class="fa-solid fa-check-circle text-3xl"></i>
                                <span>Tiket Antrian <b class="text-blue-900">${nomorPendek}</b> resmi terdaftar!</span>
                            </div>
                            <div class="flex justify-between bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-inner">
                                <div class="text-center w-1/2 border-r border-slate-200 px-2">
                                    <span class="block text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Antrian di Depan</span>
                                    <span class="text-4xl font-black text-blue-600">${sisaAntrian}</span>
                                </div>
                                <div class="text-center w-1/2 px-2">
                                    <span class="block text-[10px] text-slate-500 uppercase tracking-widest font-bold mb-1">Estimasi Waktu</span>
                                    <span class="text-4xl font-black text-yellow-500">${estimasi}</span>
                                </div>
                            </div>
                        </div>
                    `,
                    icon: 'success', confirmButtonColor: '#3b82f6', confirmButtonText: '<i class="fa-solid fa-check"></i> Lanjut', allowOutsideClick: false
                }).then(resumeScanner);
            } else {
                playBeep(false);
                resultBox.classList.add('hidden');
                Swal.fire({
                    title: 'Akses Ditolak!', text: data.message, icon: 'error', confirmButtonColor: '#e3342f'
                }).then(resumeScanner);
            }
        }).catch(() => handleNetworkError(fromCamera));
    }

    function onScanSuccess(decodedText) {
        processCheckin(decodedText, true);
    }

    function submitManualCheckin(e) {
        e.preventDefault();
        const inputEl = document.getElementById('manualTicketInput');
        const val = inputEl.value.trim();
        if (!val) {
            Swal.fire('Peringatan', 'Masukkan NISN atau Nomor Tiket terlebih dahulu.', 'warning');
            return;
        }
        processCheckin(val, false);
        inputEl.value = '';
    }

    function handleNetworkError(fromCamera) {
        document.getElementById('resultBox').classList.add('hidden');
        Swal.fire('Koneksi Terputus!', 'Gagal menghubungi server.', 'warning').then(() => {
            if (fromCamera && html5QrCode && html5QrCode.getState() === 3) {
                html5QrCode.resume();
            }
            isProcessing = false;
        });
    }

    document.getElementById('startBtn').addEventListener('click', function () {
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');

        startBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyiapkan...';
        startBtn.disabled = true;

        html5QrCode = new Html5Qrcode("reader");
        const config = { fps: 10, qrbox: { width: 250, height: 250 } };

        html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess)
            .then(() => {
                startBtn.classList.add('hidden');
                stopBtn.classList.remove('hidden');
            })
            .catch(() => {
                html5QrCode.start({ facingMode: "user" }, config, onScanSuccess)
                    .then(() => {
                        startBtn.classList.add('hidden');
                        stopBtn.classList.remove('hidden');
                    })
                    .catch(() => {
                        Swal.fire('Kamera Tidak Ditemukan', 'Pastikan izin kamera di browser telah di-Allow.', 'error');
                        startBtn.innerHTML = '<i class="fa-solid fa-camera"></i> Coba Lagi';
                        startBtn.disabled = false;
                    });
            });
    });

    document.getElementById('stopBtn').addEventListener('click', function () {
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                document.getElementById('startBtn').classList.remove('hidden');
                document.getElementById('stopBtn').classList.add('hidden');
                document.getElementById('startBtn').innerHTML = '<i class="fa-solid fa-camera"></i> Mulai Kamera';
                document.getElementById('startBtn').disabled = false;
            }).catch(err => { console.error("Gagal mematikan", err); });
        }
    });

    @if(session('error')) Swal.fire('Error', '{{ session('error') }}', 'error'); @endif

    function openGoShowModal() {
        if (html5QrCode && html5QrCode.getState() === 2) { html5QrCode.pause(true); }
        document.getElementById('goShowModal').classList.remove('hidden');
    }

    function closeGoShowModal() {
        document.getElementById('goShowModal').classList.add('hidden');
        document.getElementById('goShowForm').reset();
        if (html5QrCode && html5QrCode.getState() === 3) { html5QrCode.resume(); }
    }

    function submitGoShow(e) {
        e.preventDefault();

        let form = document.getElementById('goShowForm');
        let formData = new FormData(form);
        let btnSubmit = document.getElementById('btnSubmitGoShow');

        let isKip = document.getElementById('cb_kip').checked;
        let isDtks = document.getElementById('cb_dtks').checked;
        let jalur = 'REGULER';

        if (isKip && isDtks) { jalur = 'KIP & DTKS'; }
        else if (isKip) { jalur = 'KIP'; }
        else if (isDtks) { jalur = 'DTKS'; }

        formData.append('jalur_pendaftaran', jalur);

        let originalText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
        btnSubmit.disabled = true;

        fetch("{{ route('admin.scanner.goshow') }}", {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(obj => {
            btnSubmit.innerHTML = originalText;
            btnSubmit.disabled = false;

            if (obj.status === 422) {
                let errors = obj.body.errors;
                let errorMsg = Object.values(errors).map(e => e.join('\n')).join('\n');
                Swal.fire('Validasi Gagal!', errorMsg, 'error');
                return;
            }

            if (obj.body.success) {
                playBeep(true);
                closeGoShowModal();
                Swal.fire({
                    title: 'TIKET OFFLINE BERHASIL!',
                    html: `
                        <p class="text-sm text-gray-500 mb-2">Siswa <b>${obj.body.nama_cpd}</b> telah masuk ke antrian.</p>
                        <p class="text-xs text-red-500 italic mb-4">*Silakan foto nomor di bawah ini sebagai bukti!</p>
                        <div class="bg-gradient-to-br from-orange-100 to-amber-50 p-6 rounded-2xl border-2 border-orange-400 border-dashed shadow-inner">
                            <span class="block text-xs uppercase font-black text-orange-400 tracking-widest mb-1">KODE GO-SHOW</span>
                            <span class="text-6xl font-black text-orange-600 tracking-tighter">${obj.body.ticket_number}</span>
                        </div>
                    `,
                    icon: 'success', confirmButtonColor: '#ea580c', confirmButtonText: 'Selesai & Lanjut Jaga'
                });
            } else {
                Swal.fire('Gagal!', obj.body.message, 'error');
            }
        })
        .catch(() => {
            btnSubmit.innerHTML = originalText;
            btnSubmit.disabled = false;
            Swal.fire('Error Server!', 'Gagal menghubungi server.', 'error');
        });
    }
</script>
@endpush
