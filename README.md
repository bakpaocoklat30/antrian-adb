# 🏛️ di-SAPA (Sistem Antrian Pelayanan ADB) 
**Sistem Manajemen Antrian, Verifikasi, & Generator Dokumen SPMB - SMK Negeri 1 Adiwerna**

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

**di-SAPA** bukanlah sekadar aplikasi antrian biasa. Ini adalah sebuah Mahakarya *Enterprise-Level* terintegrasi yang dirancang khusus untuk menangani *traffic* pendaftaran Calon Peserta Didik (CPD) dengan presisi tinggi, UI/UX berstandar perbankan, dan dilengkapi proteksi *Human-Error Failsafe*.

---

## 🔥 FITUR UNGGULAN (Enterprise Features)

### 📺 1. Smart Live Radar TV & Audio Announcer (V3 Engine)
Sistem layar aula (TV) yang berjalan secara *real-time* dengan teknologi **AJAX Polling** tanpa *refresh*.
* **Smart Audio Ducking:** Volume video hiburan (YouTube/Lokal) akan otomatis mengecil (*fade out*) secara *smooth* saat nomor antrian dipanggil, dan kembali normal setelah panggilan selesai.
* **Auto-Scaling Layout:** Resolusi font nomor antrian akan membesar/mengecil otomatis beradaptasi dengan status layar.
* **Watchdog Failsafe:** Sistem anti-nyangkut yang akan me-reset *queue* audio jika terjadi gagal *load* suara dalam 35 detik.

### 🛡️ 2. "Tutup Loket" & Human Error Failsafe
Sistem memahami bahwa petugas adalah manusia. 
* **Dynamic Rest Mode:** Petugas dapat menutup loket dengan alasan kustom (misal: "ISTIRAHAT", "SHOLAT"). Layar TV akan mengubah nomor antrian Raksasa menjadi tulisan alasan tersebut dengan warna kuning peringatan.
* **Anti-Lupa (Auto-Open):** Jika petugas kembali dari istirahat dan *lupa* membuka loket namun langsung memencet tombol "Panggil", sistem akan secara cerdas menghapus status istirahat dan membuka loket secara otomatis.

### 📱 3. Scanner OSIS dengan "Expectation Management"
Petugas OSIS dilengkapi dengan *Web-based QR Scanner* menggunakan kamera HP.
* **Real-time Estimation:** Saat QR di-scan, sistem tidak hanya mengecek validitas tiket lintas sesi, tetapi juga menghitung algoritma antrian: *"Ada berapa orang di depan anak ini, dan berapa menit lagi estimasi dia dipanggil?"*. 
* Hasil ini ditampilkan dalam layar *SweetAlert* interaktif agar OSIS bisa menginformasikannya langsung kepada CPD. (Standar pelayanan VIP!).

### 🔀 4. Algoritma Continuous Numbering & Force Override Mutasi
Sistem kuota harian yang ketat berpadu dengan fleksibilitas Panitia.
* **Lintas Sesi:** Nomor antrian tidak akan *reset* (misal: P050 langsung bersambung ke S051), mencegah kebingungan data ganda lintas sesi.
* **Force Override:** Jika siswa telat/datang di luar hari jadwalnya, panitia dapat memutasinya. Sistem secara cerdas akan mencabut kuota di hari lama, dan menjejalkannya ke hari ini (walaupun kuota hari ini sudah 50/50, akan ditembus menjadi 51/50 demi SOP pelayanan tanpa merusak validitas *database*).

### 🖨️ 5. Pusat Layanan Dokumen SPMB Mandiri (Auto-Generate PDF)
Inovasi *E-Government* untuk memudahkan CPD. Siswa tidak perlu lagi mengetik atau memformat surat secara manual. Sistem menyediakan portal khusus untuk men- *generate* dokumen resmi menjadi PDF yang siap dicetak dan ditandatangani, meliputi:
* Surat Pernyataan Kebenaran Dokumen
* Surat Pernyataan Sehat
* Surat Pernyataan Daftar Ulang
* Surat Pernyataan ATS (Anak Tidak Sekolah)

### 🎫 6. On-The-Fly Vector e-Ticket (PDF)
Meninggalkan metode *generate* QR jadul. Aplikasi ini menggunakan injeksi kode base64 **SVG On-The-Fly** di dalam mesin DomPDF. Hasilnya? QR Code pada tiket PDF dan dokumen tidak akan pernah pecah meskipun di-zoom hingga 10.000x!

---

## 🛠️ TECH STACK
* **Framework:** Laravel 10+
* **Frontend:** Blade Templating, Tailwind CSS, FontAwesome 6
* **Database:** MySQL
* **Libraries:**
  * `simplesoftwareio/simple-qrcode` (Vector QR Generation)
  * `barryvdh/laravel-dompdf` (PDF Generation & Document Builder)
  * `html5-qrcode` (Web Camera Scanner)
  * `SweetAlert2` (Interactive Popups)

---

## 📸 SCREENSHOTS

<p align="center">
  <img src="link-gambar-tv-aula" width="45%" alt="Dashboard TV Aula">
  <img src="link-gambar-scanner-osis" width="45%" alt="Scanner OSIS dengan Estimasi">
</p>
<p align="center">
  <img src="link-gambar-dashboard-verifikator" width="45%" alt="Dashboard Loket Verifikator">
  <img src="link-gambar-pusat-dokumen" width="45%" alt="Pusat Layanan Dokumen SPMB">
</p>

---

## ⚙️ CARA INSTALASI

1. Clone repositori ini:
   ```bash
   git clone https://github.com/bakpaocoklat30/antrian-adb.git
   ```
2. Salin `.env.example` menjadi `.env`, lalu isi `DB_ROOT_PASSWORD` dengan password yang kuat sebelum menjalankan Docker Compose.