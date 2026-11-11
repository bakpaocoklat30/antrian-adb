# 🏛️ di-SAPA (Sistem Antrian Pelayanan ADB)
**Sistem Manajemen Antrian, Loket Verifikator, & Layar Monitor Aula - SMK Negeri 1 Adiwerna**

![Laravel](https://img.shields.io/badge/Laravel_13-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)

**di-SAPA** berfokus pada 3 alur inti pelayanan antrian verifikasi berkas:
1. **Sistem Antrian & Check-In**: Pengambilan tiket online, e-Tiket PDF + QR Code SVG, cetak ulang via NISN, live status monitor, serta Pos Scanner Check-In (termasuk input manual & tiket Go-Show offline).
2. **Loket Verifikator**: Live radar antrian hadir, pemanggilan hingga 3x, status menghadap/lewati/selesai (dengan catatan opsional), mode Tutup/Buka Loket sementara, serta mutasi jadwal siswa.
3. **Layar Monitor Aula (`/layar`)**: Tampilan TV real-time (polling 2 detik), waiting list, status seluruh loket verifikator aktif, video latar (YouTube/MP4 lokal) dengan audio ducking, running text, dan suara panggilan Text-to-Speech (TTS).

---

## 🛠️ TECH STACK
* **Framework:** Laravel 13 (PHP 8.3+ / Docker PHP 8.4-FPM)
* **Frontend:** Blade Templating, Tailwind CSS, FontAwesome 6, SweetAlert2, `html5-qrcode`
* **Database & Cache:** MySQL 8.0 + Redis
* **Libraries:** `simplesoftwareio/simple-qrcode`, `barryvdh/laravel-dompdf`

---

## 🐳 CARA MENJALANKAN VIA DOCKER

### 1. Lingkungan Lokal (Development - HTTP `localhost:8000`)
1. Salin `.env.example` menjadi `.env` dan sesuaikan `DB_ROOT_PASSWORD` bila perlu:
   ```bash
   cp .env.example .env
   ```
2. Build & jalankan seluruh container (`app`, `webserver`, `db`, `phpmyadmin`, `redis`):
   ```bash
   docker compose up -d --build
   ```
3. Instal dependensi PHP, generate key, migrasi + seeder, dan buat storage symlink:
   ```bash
   docker exec -it disapa_app composer install
   docker exec -it disapa_app php artisan key:generate
   docker exec -it disapa_app php artisan migrate --seed
   docker exec -it disapa_app php artisan storage:link
   ```
4. Akses aplikasi lokal:
   * **Ambil Antrian Publik:** `http://localhost:8000`
   * **Layar Monitor Aula:** `http://localhost:8000/layar`
   * **Login Petugas / Admin:** `http://localhost:8000/login`
   * **phpMyAdmin:** `http://localhost:8080`

### 2. Lingkungan Server Ubuntu (1 Script untuk Install, Update Git, Backup & Restore)
Untuk instalasi pertama kali maupun saat menarik update perbaikan dari Git di server Ubuntu, cukup gunakan **1 script**:
```bash
git clone https://github.com/bakpaocoklat30/antrian-adb.git
cd antrian-adb
./deploy-ubuntu.sh
```
Setelah instalasi awal, setiap ada update baru di Git Anda cukup menjalankan:
```bash
./deploy-ubuntu.sh
```
Script [deploy-ubuntu.sh](deploy-ubuntu.sh) otomatis akan:
* Meminta akses `sudo` otomatis bila dibutuhkan, menarik update terbaru dari Git (`git pull --ff-only`), dan langsung mem-build container bila perlu.
* Menyimpan IP Publik & Port dari `.env` (instalasi awal bisa juga langsung `SERVER_IP=202.65.116.9 APP_PORT=7030 ./deploy-ubuntu.sh`; saat update berikutnya tidak bertanya ulang).
* Menginstal dependensi Composer produksi, membuat backup database pengaman sebelum migrasi, menjalankan `php artisan migrate --force`, dan menyegarkan cache.
* Mengaktifkan cron backup database harian otomatis (`02:00 WIB`), serta mendukung perintah langsung:
  * `./deploy-ubuntu.sh backup` (backup ke `backup_db/*.sql.gz`)
  * `./deploy-ubuntu.sh restore backup_db/latest.sql.gz`

---

## 🔑 AKUN BAWAAN SEEDER
* **Super Admin:** `admin@smkn1adw.sch.id` / `password`
* **Scanner OSIS:** `osis1@smkn1adw.sch.id` / `password`
* **Loket Verifikator:** `loket1@smkn1adw.sch.id` .. `loket7@smkn1adw.sch.id` / `password`
