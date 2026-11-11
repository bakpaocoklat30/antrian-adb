// 1. Inisialisasi Antrian
let audioQueue = [];
let isSpeaking = false;

// 2. Fungsi untuk memasukkan permintaan ke antrian
function addToQueue(text) {
    audioQueue.push(text);
    processQueue();
}

// 3. Fungsi untuk memproses antrian satu per satu
function processQueue() {
    // Jika sedang ada suara atau antrian kosong, berhenti
    if (isSpeaking || audioQueue.length === 0) return;

    // Ambil teks pertama dari antrian
    let textToSpeak = audioQueue.shift();
    playVoice(textToSpeak);
}

// 4. Fungsi eksekusi suara (Web Speech API)
function playVoice(text) {
    isSpeaking = true;

    let utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'id-ID';
    utterance.rate = 0.9; // Sedikit diperlambat agar jelas
    utterance.pitch = 1;

    // 🔥 KUNCI UTAMA: Event saat suara selesai 🔥
    utterance.onend = function() {
        console.log("Panggilan selesai, menjeda 2 detik sebelum panggilan berikutnya...");
        
        // Beri jeda antar panggilan agar tidak terlalu rapat (misal 2 detik)
        setTimeout(() => {
            isSpeaking = false;
            processQueue(); // Jalankan antrian berikutnya
        }, 5000); 
    };

    window.speechSynthesis.speak(utterance);
}

// 5. Update Listener Websocket Anda
Echo.channel('panggilan-channel')
    .listen('TiketDipanggil', (e) => {
        // Ganti pemanggilan langsung menjadi masuk ke Antrian
        let pesan = `Nomor antrian... ${e.nomor}... silakan menuju ke... loket... ${e.loket}`;
        addToQueue(pesan);
    });