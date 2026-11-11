<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Screening Buta Warna & Surat Pernyataan</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-adb.jpg') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .fade-in { animation: fadeIn 0.5s ease-in-out; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .no-select { user-select: none; -webkit-user-select: none; -moz-user-select: none; }
        input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen py-10 px-4 font-sans">

    <div class="container mx-auto max-w-3xl p-6 md:p-10 bg-white rounded-3xl shadow-2xl border border-gray-100 relative overflow-hidden">
        
        <div id="header-section">
            <a href="{{ route('dokumen.index') }}" class="inline-flex items-center text-red-600 hover:text-red-800 font-bold mb-6 transition relative z-10">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Menu
            </a>
            <div class="text-center mb-8 border-b pb-6 relative z-10">
                <h2 class="text-2xl md:text-3xl font-black text-gray-800">Screening & Surat Pernyataan<br><span class="text-red-600">Tidak Buta Warna</span></h2><br>
                <p class="text-gray-500 text-sm mt-2 font-medium text-justify">Tes ini digunakan sebagai pemeriksaan awal untuk membantu CMB mengetahui kemungkinan kondisi buta warna. Hasil dari tes ini tidak dapat dijadikan acuan pasti atau menjamin 100% kondisi penglihatan warna CMB.</p>
                <p class="text-gray-500 text-sm mt-2 font-medium text-justify">Untuk mendapatkan hasil yang lebih akurat dan valid, disarankan melakukan pemeriksaan tes buta warna di fasilitas kesehatan (faskes) atau klinik mata terdekat.</p>
            </div>
        </div>

        <div id="phase-1" class="fade-in relative z-10">
            <div class="bg-red-50/80 p-8 rounded-3xl border border-red-100 text-center shadow-inner">
                <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center text-3xl mx-auto mb-5 shadow-sm"><i class="fa-solid fa-id-card-clip"></i></div>
                <h3 class="text-2xl font-black text-red-800 mb-2 tracking-tight">Verifikasi Identitas</h3>
                <p class="text-gray-600 text-sm mb-8 font-medium max-w-md mx-auto">Masukkan NISN Anda untuk memulai. Kesempatan tes maksimal 2x.</p>
                
                <div class="max-w-sm mx-auto">
                    <input type="text" id="check_nisn" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" placeholder="Ketik 10 Digit NISN..." class="w-full text-center text-2xl font-black tracking-widest rounded-2xl border-gray-300 shadow-inner focus:border-red-500 focus:ring-red-500 p-5 border bg-white mb-5 outline-none">
                    <button type="button" onclick="verifyNisn()" id="btnVerify" class="w-full bg-red-600 text-white font-black py-5 px-4 rounded-2xl hover:bg-red-700 transition duration-300 shadow-lg shadow-red-200 flex items-center justify-center gap-3 text-lg">
                        <i class="fa-solid fa-play"></i> MULAI TES SEKARANG
                    </button>
                </div>
            </div>
        </div>

        <div id="phase-2" class="hidden fade-in no-select relative z-10">
            <div class="bg-slate-900 rounded-[2rem] overflow-hidden shadow-2xl border-[6px] border-slate-800 relative">
                <div class="bg-slate-800 px-6 py-4 flex justify-between items-center border-b-2 border-slate-700">
                    <div class="font-bold text-slate-300 flex items-center gap-2"><i class="fa-solid fa-eye text-red-400"></i> Tes Penglihatan Warna</div>
                    <div class="flex items-center gap-3">
                        <span id="attempt-badge" class="bg-red-500 text-white text-[10px] font-bold px-2 py-1 rounded hidden">PERCOBAAN 2</span>
                        <div class="bg-slate-900 px-5 py-1.5 rounded-full border border-slate-600 text-yellow-400 font-black tracking-widest text-sm shadow-inner" id="progress-text">1 / 10</div>
                    </div>
                </div>
                
                <div class="p-6 md:p-10 flex flex-col items-center justify-center min-h-[400px] relative">
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-slate-800"><div id="progress-bar" class="h-full bg-red-500 transition-all duration-300 shadow-[0_0_10px_rgba(239,68,68,0.8)]" style="width: 10%;"></div></div>
                    <h3 class="text-xl md:text-2xl font-bold text-white mb-8 text-center drop-shadow-md">Ketik angka yang terlihat di dalam lingkaran!</h3>
                    <div class="bg-white p-2 rounded-full shadow-[0_0_40px_rgba(0,0,0,0.6)] mb-8 transition-transform duration-300" id="img-container">
                        <img id="ishihara-img" src="" class="w-48 h-48 md:w-56 md:h-56 object-cover rounded-full pointer-events-none select-none">
                    </div>
                    <div class="w-full max-w-md" id="options-container">
                        </div>
                </div>
            </div>
        </div>

        <div id="phase-3" class="hidden fade-in relative z-10">
            <div class="bg-green-50 border-2 border-green-500 p-6 rounded-3xl mb-8 text-center shadow-lg shadow-green-100">
                <div class="w-20 h-20 bg-green-500 text-white rounded-full flex items-center justify-center text-4xl mx-auto mb-4 shadow-inner"><i class="fa-solid fa-check"></i></div>
                <h3 class="text-2xl font-black text-green-800 tracking-tight">Tes Selesai!</h3>
                <p class="text-green-700 font-medium text-base mt-1">Luar Biasa! Skor Anda Lulus. Silakan lengkapi data diri di bawah ini untuk menerbitkan dokumen.</p>
            </div>

            <form action="{{ route('dokumen.butawarna.download') }}" method="POST" id="finalForm" class="space-y-8">
                @csrf
                <input type="hidden" name="nisn" id="final_nisn">
                <input type="hidden" name="score" id="final_score">

                <div class="bg-blue-50/60 p-8 rounded-3xl border border-blue-100 shadow-sm">
                    <h3 class="text-xl font-black text-blue-900 mb-6 border-b-2 border-blue-200 pb-3 flex items-center"><i class="fa-solid fa-user-graduate mr-3 text-blue-600"></i>Data Calon Murid</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap Siswa</label>
                            <input type="text" name="siswa_nama" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-4 border uppercase font-bold text-gray-800 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-4 border font-bold text-gray-800 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-4 border font-bold text-gray-800 outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nomor KTP / NIK (16 Digit)</label>
                            <input type="text" name="siswa_nik" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,16)" minlength="16" maxlength="16" placeholder="Ketik 16 Digit NIK..." required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-4 border font-bold font-mono tracking-wider text-gray-800 outline-none">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Alamat Lengkap Sesuai KK</label>
                            <textarea name="siswa_alamat" rows="3" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 p-4 border font-bold text-gray-800 outline-none"></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-8 rounded-3xl border border-gray-200 shadow-sm">
                    <h3 class="text-xl font-black text-gray-800 mb-6 border-b-2 border-gray-200 pb-3 flex items-center"><i class="fa-solid fa-pen-nib mr-3 text-gray-600"></i>Penandatangan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Orang Tua / Wali</label>
                            <input type="text" name="ortu_nama" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 p-4 border uppercase font-bold text-gray-800 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Kota Tempat Cetak Surat</label>
                            <input type="text" name="lokasi_cetak" placeholder="Contoh: Tegal" required class="w-full rounded-xl border-gray-300 shadow-sm focus:border-gray-500 focus:ring-gray-500 p-4 border font-bold text-gray-800 outline-none">
                        </div>
                    </div>
                </div>

                <button type="submit" id="btnSubmitFinal" class="w-full bg-blue-600 text-white font-black py-6 px-4 rounded-2xl hover:bg-blue-700 transition duration-300 text-xl shadow-xl shadow-blue-200 flex items-center justify-center gap-3">
                    <i class="fa-solid fa-upload text-2xl"></i> SIMPAN & CETAK PDF
                </button>
            </form>
        </div>

        <div id="phase-4" class="hidden fade-in relative z-10 text-center py-8">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full h-full bg-green-50/50 blur-[80px] -z-10 rounded-full"></div>
            <div class="w-24 h-24 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-5xl mx-auto mb-6 shadow-lg border-4 border-white relative z-10"><i class="fa-solid fa-medal"></i></div>
            <h3 class="text-3xl md:text-4xl font-black text-gray-800 mb-4 tracking-tight relative z-10">Data Tersimpan!</h3>
            <p class="text-gray-600 text-lg mb-8 font-medium max-w-lg mx-auto leading-relaxed relative z-10">Dokumen Anda telah siap. Silakan unduh dan cetak untuk ditandatangani.</p>
            <div class="max-w-md mx-auto relative z-10 flex flex-col items-center">
                <a href="#" id="btnDownloadPdf" class="flex w-full bg-green-600 text-white font-black py-5 px-6 rounded-2xl hover:bg-green-700 transition duration-300 shadow-xl shadow-green-200 items-center justify-center gap-3 text-lg mb-6 group">
                    <i class="fa-solid fa-file-arrow-down text-2xl group-hover:-translate-y-1 transition-transform"></i> UNDUH SURAT PERNYATAAN
                </a>
                <a href="{{ route('dokumen.index') }}" class="inline-flex items-center text-gray-500 hover:text-blue-600 font-bold transition">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Menu Utama
                </a>
            </div>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const masterIshihara = [
            { id: 1, ans: 12 }, { id: 2, ans: 8 }, { id: 3, ans: 29 }, 
            { id: 4, ans: 5 }, { id: 5, ans: 3 }, { id: 6, ans: 15 }, 
            { id: 7, ans: 74 }, { id: 8, ans: 6 }, { id: 9, ans: 45 },
            { id: 10, ans: 5 }, { id: 11, ans: 7 }, { id: 12, ans: 16 },
            { id: 13, ans: 73 }, { id: 14, ans: 26 },{ id: 15, ans: 96 },
            { id: 16, ans: 15 }, { id: 17, ans: 26 }, { id: 18, ans: 42 }, 
            { id: 19, ans: 35 }, { id: 20, ans: 57 }, { id: 21, ans: 5 }, 
            { id: 22, ans: 29 }, { id: 23, ans: 74 }, { id: 24, ans: 12 }, 
            { id: 25, ans: 8 }, { id: 26, ans: 6 }, { id: 27, ans: 42 },
            { id: 28, ans: 97 }, { id: 29, ans: 6 }, { id: 30, ans: 2 }, 
            { id: 31, ans: 7 }, { id: 32, ans: 5 }, { id: 33, ans: 45 }, 
            { id: 34, ans: 73 }, { id: 35, ans: 16 }, { id: 36, ans:3 },
        ];

        let currentNisn = '';
        let currentScore = 0;
        let currentQuestion = 0;
        let attemptCount = 1;
        const totalQuestions = 10;
        let selectedPlates = [];

        function verifyNisn() {
            let inputNisn = document.getElementById('check_nisn').value;
            if(!inputNisn || inputNisn.length < 10) {
                Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Masukkan 10 digit NISN yang valid!' });
                return;
            }

            let btn = document.getElementById('btnVerify');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xl"></i> MEMERIKSA...';
            btn.disabled = true;

            fetch("{{ route('dokumen.butawarna.check_nisn') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ nisn: inputNisn })
            })
            .then(res => res.json())
            .then(data => {
                btn.innerHTML = '<i class="fa-solid fa-play"></i> MULAI TES SEKARANG';
                btn.disabled = false;

                if(data.success) {
                    currentNisn = inputNisn;
                    
                    if(data.action === 'already_passed') {
                        // FITUR REPRINT LANGSUNG KE FASE 4
                        document.getElementById('header-section').classList.add('hidden');
                        document.getElementById('phase-1').classList.add('hidden');
                        document.getElementById('phase-4').classList.remove('hidden');
                        document.getElementById('btnDownloadPdf').href = `/generator-dokumen/pernyataan-butawarna/reprint/${currentNisn}`;
                    } else {
                        attemptCount = 1;
                        startGame();
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Akses Ditolak!', text: data.message });
                }
            })
            .catch(err => {
                btn.innerHTML = '<i class="fa-solid fa-play"></i> MULAI TES SEKARANG';
                btn.disabled = false;
                Swal.fire({ icon: 'error', title: 'Koneksi Gagal', text: 'Gagal menghubungi server.' });
            });
        }

        function startGame() {
            document.getElementById('phase-1').classList.add('hidden');
            document.getElementById('phase-3').classList.add('hidden'); 
            document.getElementById('phase-2').classList.remove('hidden');
            
            if (attemptCount === 2) {
                document.getElementById('attempt-badge').classList.remove('hidden');
            }
            
            selectedPlates = [...masterIshihara].sort(() => 0.5 - Math.random()).slice(0, 10);
            currentScore = 0;
            currentQuestion = 0;
            loadQuestion();
        }

        function loadQuestion() {
            if (currentQuestion >= totalQuestions) {
                finishGame();
                return;
            }

            let qData = selectedPlates[currentQuestion];
            let imgContainer = document.getElementById('img-container');
            imgContainer.classList.add('scale-95', 'opacity-50');
            
            setTimeout(() => {
                document.getElementById('progress-text').innerText = (currentQuestion + 1) + " / 10";
                document.getElementById('progress-bar').style.width = ((currentQuestion + 1) * 10) + "%";
                document.getElementById('ishihara-img').src = "{{ asset('images/ishihara/plate') }}" + qData.id + ".jpg"; 

                // 🔥 ANTI-RELOAD FIX: Tambahkan type="button" agar Enter tidak mereload halaman 🔥
                let container = document.getElementById('options-container');
                container.innerHTML = `
                    <div class="flex flex-col gap-4 w-full px-2">
                        <input type="text" id="answer_input" oninput="this.value=this.value.replace(/[^0-9]/g,'')" placeholder="Ketik angka di sini..." class="w-full text-center text-3xl font-black rounded-2xl border-gray-400 shadow-inner focus:border-red-500 focus:ring-red-500 p-5 border bg-white outline-none" autocomplete="off">
                        
                        <div class="flex flex-col md:flex-row gap-3 mt-1">
                            <button type="button" onclick="submitAnswer(false)" class="flex-1 bg-slate-700 text-white font-black py-4 px-4 rounded-xl hover:bg-slate-800 transition shadow-md">TIDAK TERBACA</button>
                            <button type="button" onclick="submitAnswer(true)" class="flex-1 bg-red-600 text-white font-black py-4 px-4 rounded-xl hover:bg-red-700 transition shadow-md shadow-red-200">JAWAB & LANJUT <i class="fa-solid fa-chevron-right ml-1"></i></button>
                        </div>
                    </div>
                `;

                setTimeout(() => document.getElementById('answer_input').focus(), 50);

                // 🔥 ANTI-RELOAD FIX: Mencegah Form Submit Bawaan Browser saat tekan Enter 🔥
                document.getElementById('answer_input').addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault(); 
                        submitAnswer(true);
                    }
                });
                
                imgContainer.classList.remove('scale-95', 'opacity-50');
            }, 200);
        }

        function submitAnswer(hasNumber) {
            let qData = selectedPlates[currentQuestion];
            let typedVal = '';
            
            if (hasNumber) {
                let inputEl = document.getElementById('answer_input');
                typedVal = inputEl.value.trim();
                if (typedVal === '') {
                    Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Ketik angkanya dulu atau pilih "Tidak Terbaca".' });
                    return;
                }
            }

            if (hasNumber && parseInt(typedVal) === qData.ans) {
                currentScore++;
            }
            
            currentQuestion++;
            loadQuestion();
        }

        // 🔥 LOGIC KOREKSI & KIRIM DATA KE DATABASE TANPA BIODATA 🔥
        function finishGame() {
            if (currentScore >= 9) {
                // JIKA LOLOS -> Buka Fase 3 (Biodata)
                document.getElementById('phase-2').classList.add('hidden');
                document.getElementById('phase-3').classList.remove('hidden');
                document.getElementById('final_nisn').value = currentNisn;
                document.getElementById('final_score').value = currentScore;
            } else {
                // JIKA GAGAL -> Fetch ke Backend (Siswa TIDAK disuruh isi biodata)
                Swal.fire({
                    title: 'Memverifikasi Hasil...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                let formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('nisn', currentNisn);
                formData.append('score', currentScore);
                formData.append('is_fail_report', 'yes'); // Parameter sakti lapor gagal

                fetch("{{ route('dokumen.butawarna.download') }}", {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.action === 'retry') {
                        // Gagal 1x: Langsung Ulang (Tanpa kembali ke depan)
                        Swal.fire({
                            title: 'Skor Belum Memenuhi',
                            html: data.message,
                            icon: 'warning',
                            confirmButtonText: '<i class="fa-solid fa-rotate-right"></i> Mulai Tes Ke-2',
                            confirmButtonColor: '#d33',
                            allowOutsideClick: false
                        }).then(() => {
                            attemptCount++;
                            startGame(); // Melesat langsung ke Fase 2!
                        });
                    } else if (data.action === 'final_fail') {
                        // Gagal 2x: Tendang ke beranda
                        Swal.fire({
                            title: 'Tindakan Lanjutan Diperlukan',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'Kembali ke Menu',
                            confirmButtonColor: '#e11d48',
                            allowOutsideClick: false
                        }).then(() => { window.location.href = "{{ route('dokumen.index') }}"; });
                    }
                })
                .catch(err => {
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                });
            }
        }

        // --- SUBMIT FORM BIODATA & CETAK PDF ---
        document.getElementById('finalForm').addEventListener('submit', async function(e) {
            e.preventDefault(); 
            
            let nikInput = document.querySelector('input[name="siswa_nik"]').value;
            if (nikInput.length !== 16) {
                Swal.fire({ icon: 'warning', title: 'Data Tidak Valid', text: 'Nomor KTP / NIK wajib berjumlah tepat 16 Digit angka!' });
                return;
            }

            let form = this;
            let btn = document.getElementById('btnSubmitFinal');
            let originalText = btn.innerHTML;
            
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-2xl"></i> MEMVALIDASI DATA...';
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            btn.disabled = true;

            try {
                let formData = new FormData(form);
                let response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) {
                    let resData = await response.json();
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Ditolak',
                        text: resData.message || 'NIK sudah terdaftar atau data tidak valid.',
                        confirmButtonColor: '#e11d48'
                    });
                    btn.innerHTML = originalText;
                    btn.classList.remove('opacity-80', 'cursor-not-allowed');
                    btn.disabled = false;
                    return;
                }

                let blob = await response.blob();
                let downloadUrl = window.URL.createObjectURL(blob);

                document.getElementById('header-section').classList.add('hidden');
                document.getElementById('phase-3').classList.add('hidden');
                document.getElementById('phase-4').classList.remove('hidden');

                let dlBtn = document.getElementById('btnDownloadPdf');
                dlBtn.href = downloadUrl;
                dlBtn.download = 'Surat_Pernyataan_Tidak_Buta_Warna_' + currentNisn + '.pdf';
                
            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Terjadi kesalahan teknis server.', 'error');
                btn.innerHTML = originalText;
                btn.classList.remove('opacity-80', 'cursor-not-allowed');
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>