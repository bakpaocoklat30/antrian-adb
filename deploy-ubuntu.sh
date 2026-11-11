#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# di-SAPA - One-Shot Deployment Script untuk Server Ubuntu (Docker, IP + Port)
# Jalankan sekali di server Ubuntu:
#   chmod +x deploy-ubuntu.sh && ./deploy-ubuntu.sh
#
# Opsi non-interaktif (opsional via environment variable):
#   SERVER_IP=103.123.45.67 APP_PORT=8000 ./deploy-ubuntu.sh
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

info()  { echo -e "${BLUE}[INFO]${NC} $*"; }
ok()    { echo -e "${GREEN}[OK]${NC} $*"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $*"; }
error() { echo -e "${RED}[ERROR]${NC} $*" >&2; exit 1; }

SUDO=""
if [ "$(id -u)" -ne 0 ]; then
    if command -v sudo >/dev/null 2>&1; then
        SUDO="sudo"
    else
        error "Script ini membutuhkan akses root atau sudo."
    fi
fi

set_env() {
    local key="$1"
    local val="$2"
    local file=".env"
    if grep -q "^${key}=" "$file"; then
        awk -v k="$key" -v v="$val" '
            BEGIN { FS="=" }
            $1 == k { print k "=" v; next }
            { print }
        ' "$file" > "${file}.tmp" && mv "${file}.tmp" "$file"
    else
        printf '%s=%s\n' "$key" "$val" >> "$file"
    fi
}

get_env() {
    local key="$1"
    local file=".env"
    grep -E "^${key}=" "$file" | head -n1 | cut -d'=' -f2- | sed -E 's/^["'\'']|["'\'']$//g' || true
}

# ── 1. Pastikan Docker & Docker Compose terpasang ──────────────────────────────
install_docker_ubuntu() {
    info "Docker belum ditemukan. Memasang Docker Engine & Compose Plugin untuk Ubuntu..."
    $SUDO apt-get update -y
    $SUDO apt-get install -y ca-certificates curl gnupg lsb-release

    $SUDO install -m 0755 -d /etc/apt/keyrings
    if [ ! -f /etc/apt/keyrings/docker.asc ]; then
        curl -fsSL https://download.docker.com/linux/ubuntu/gpg | $SUDO tee /etc/apt/keyrings/docker.asc >/dev/null
        $SUDO chmod a+r /etc/apt/keyrings/docker.asc
    fi

    local arch codename
    arch="$(dpkg --print-architecture)"
    codename="$(. /etc/os-release && echo "${VERSION_CODENAME:-$UBUNTU_CODENAME}")"
    echo "deb [arch=${arch} signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu ${codename} stable" | \
        $SUDO tee /etc/apt/sources.list.d/docker.list >/dev/null

    $SUDO apt-get update -y
    $SUDO apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    $SUDO systemctl enable --now docker

    if [ -n "${SUDO}" ] && [ -n "${USER:-}" ]; then
        $SUDO usermod -aG docker "$USER" || true
        warn "User '$USER' ditambahkan ke grup docker (efektif penuh setelah login ulang)."
    fi
    ok "Docker berhasil dipasang."
}

if ! command -v docker >/dev/null 2>&1 || ! docker compose version >/dev/null 2>&1; then
    if [ -f /etc/os-release ] && grep -qiE "ubuntu|debian" /etc/os-release; then
        install_docker_ubuntu
    else
        error "Docker & Docker Compose v2 belum terpasang. Pasang Docker terlebih dahulu."
    fi
fi

DOCKER="docker"
if ! docker ps >/dev/null 2>&1; then
    DOCKER="$SUDO docker"
fi
ok "Menggunakan Docker: $($DOCKER --version)"

# ── 2. Kumpulkan IP Publik & Port Aplikasi ─────────────────────────────────────
DETECTED_IP="$(curl -4 -fsSL --max-time 3 https://api.ipify.org 2>/dev/null || hostname -I 2>/dev/null | awk '{print $1}' || true)"
DEFAULT_IP="${DETECTED_IP:-127.0.0.1}"

if [ -z "${SERVER_IP:-}" ]; then
    if [ -t 0 ]; then
        echo ""
        read -rp "Masukkan IP Publik Server [${DEFAULT_IP}]: " INPUT_IP
        SERVER_IP="${INPUT_IP:-$DEFAULT_IP}"
    else
        SERVER_IP="$DEFAULT_IP"
    fi
fi

if [ -z "${APP_PORT:-}" ]; then
    if [ -t 0 ]; then
        read -rp "Masukkan Port Aplikasi yang dibuka (misal 8000 atau 80) [8000]: " INPUT_PORT
        APP_PORT="${INPUT_PORT:-8000}"
    else
        APP_PORT="8000"
    fi
fi

if ! [[ "$APP_PORT" =~ ^[0-9]+$ ]] || [ "$APP_PORT" -lt 1 ] || [ "$APP_PORT" -gt 65535 ]; then
    error "Port tidak valid: '${APP_PORT}'. Gunakan angka 1-65535."
fi

# ── 3. Siapkan file .env Produksi ──────────────────────────────────────────────
info "Menyiapkan konfigurasi .env produksi..."
if [ ! -f .env ]; then
    cp .env.example .env
fi

CURRENT_DB_PASS="$(get_env DB_ROOT_PASSWORD)"
if [ -z "$CURRENT_DB_PASS" ] || [ "$CURRENT_DB_PASS" = "disapa_secret" ]; then
    if command -v openssl >/dev/null 2>&1; then
        NEW_DB_PASS="$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 24)"
    else
        NEW_DB_PASS="$(tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 24)"
    fi
    set_env "DB_ROOT_PASSWORD" "$NEW_DB_PASS"
    set_env "DB_PASSWORD" "$NEW_DB_PASS"
    ok "Password database acak yang kuat berhasil dibuat di .env."
else
    set_env "DB_PASSWORD" "$CURRENT_DB_PASS"
fi

if [ "$APP_PORT" -eq 80 ]; then
    APP_URL="http://${SERVER_IP}"
else
    APP_URL="http://${SERVER_IP}:${APP_PORT}"
fi

PMA_PORT="8081"
if [ "$APP_PORT" -eq 8081 ]; then
    PMA_PORT="8082"
fi

set_env "APP_ENV" "production"
set_env "APP_DEBUG" "false"
set_env "APP_PORT" "$APP_PORT"
set_env "APP_URL" "$APP_URL"
set_env "PMA_PORT" "$PMA_PORT"
set_env "DB_CONNECTION" "mysql"
set_env "DB_HOST" "db"
set_env "DB_PORT" "3306"
set_env "DB_DATABASE" "disapa_db"
set_env "DB_USERNAME" "root"
set_env "REDIS_HOST" "redis"

# ── 4. Build & Jalankan Stack Docker Produksi ──────────────────────────────────
COMPOSE_CMD="$DOCKER compose -f docker-compose.yml -f docker-compose.prod.yml"
info "Membangun dan menjalankan container Docker pada ${APP_URL}..."
$COMPOSE_CMD up -d --build

# ── 5. Setup Aplikasi Laravel di dalam Container ───────────────────────────────
info "Mengatur izin folder storage & bootstrap/cache..."
$DOCKER exec -u root disapa_app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
$DOCKER exec -u root disapa_app chmod -R ug+rwx /var/www/storage /var/www/bootstrap/cache

info "Menginstal dependensi PHP produksi (Composer)..."
$DOCKER exec disapa_app composer install --no-dev --optimize-autoloader --no-interaction

CURRENT_APP_KEY="$(get_env APP_KEY)"
if [ -z "$CURRENT_APP_KEY" ]; then
    info "Membuat APP_KEY baru..."
    $DOCKER exec disapa_app php artisan key:generate --force --no-interaction
fi

info "Membersihkan cache lama sebelum migrasi..."
$DOCKER exec disapa_app php artisan optimize:clear

chmod +x "$SCRIPT_DIR/backup-db.sh" "$SCRIPT_DIR/restore-db.sh"

USER_COUNT="$($DOCKER exec disapa_app php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); try { echo App\Models\User::count(); } catch (Throwable \$e) { echo 0; }" 2>/dev/null || echo 0)"
if [ "${USER_COUNT:-0}" -gt 0 ]; then
    info "Data database terdeteksi (${USER_COUNT} user). Membuat backup pengaman sebelum migrasi..."
    "$SCRIPT_DIR/backup-db.sh" || warn "Backup pra-migrasi dilewati."
fi

info "Menjalankan migrasi database..."
$DOCKER exec disapa_app php artisan migrate --force --no-interaction

USER_COUNT="$($DOCKER exec disapa_app php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo App\Models\User::count();")"
if [ "${USER_COUNT:-0}" = "0" ]; then
    info "Tabel users kosong, menjalankan seeder akun awal..."
    $DOCKER exec disapa_app php artisan db:seed --force --no-interaction
    "$SCRIPT_DIR/backup-db.sh" || warn "Backup awal gagal dibuat."
else
    ok "Tabel users sudah berisi ${USER_COUNT} akun, melewati seeder awal."
fi

if [ -d /etc/cron.d ]; then
    info "Menjadwalkan backup database otomatis setiap hari pukul 02:00 WIB..."
    printf "0 2 * * * root %s/backup-db.sh >> %s/backup_db/cron.log 2>&1\n" "$SCRIPT_DIR" "$SCRIPT_DIR" | $SUDO tee /etc/cron.d/disapa-db-backup >/dev/null
    $SUDO chmod 644 /etc/cron.d/disapa-db-backup
    ok "Cron backup harian aktif di /etc/cron.d/disapa-db-backup."
fi

$DOCKER exec disapa_app php artisan storage:link --no-interaction >/dev/null 2>&1 || true

info "Mengoptimalkan cache Laravel untuk produksi..."
$DOCKER exec disapa_app php artisan config:cache
$DOCKER exec disapa_app php artisan route:cache
$DOCKER exec disapa_app php artisan view:cache

# ── 6. Buka port aplikasi di UFW jika firewall aktif ──────────────────────────
if command -v ufw >/dev/null 2>&1 && $SUDO ufw status 2>/dev/null | grep -q "Status: active"; then
    info "UFW aktif terdeteksi, membuka port ${APP_PORT}/tcp..."
    $SUDO ufw allow "${APP_PORT}/tcp" >/dev/null || true
fi

echo ""
echo "=============================================================================="
ok "DEPLOYMENT di-SAPA DI SERVER UBUNTU SELESAI!"
echo "=============================================================================="
echo " URL Publik       : ${APP_URL}"
echo " Layar Monitor    : ${APP_URL}/layar"
echo " Login Petugas    : ${APP_URL}/login"
echo ""
echo " Akun Bawaan Seeder (segera ganti password setelah login!):"
echo " - Super Admin    : admin@smkn1adw.sch.id | password"
echo " - Scanner OSIS   : osis1@smkn1adw.sch.id | password"
echo " - Verifikator    : loket1@smkn1adw.sch.id .. loket7@smkn1adw.sch.id | password"
echo ""
echo " Keamanan Server:"
echo " - Hanya port ${APP_PORT} yang dibuka untuk akses publik."
echo " - Port MySQL (3306), phpMyAdmin (${PMA_PORT}), & Redis (6379) dikunci ke 127.0.0.1."
echo " - Untuk akses phpMyAdmin dari laptop, gunakan SSH tunnel:"
echo "     ssh -L ${PMA_PORT}:127.0.0.1:${PMA_PORT} user@${SERVER_IP}"
echo "   lalu buka http://localhost:${PMA_PORT} di browser Anda."
echo " Backup & Restore Database:"
echo " - Backup manual  : ./backup-db.sh (hasil tersimpan di ./backup_db/*.sql.gz)"
echo " - Restore manual : ./restore-db.sh backup_db/latest.sql.gz"
echo " - Download cepat : Portal Super Admin -> Backup Database (.sql)"
echo "=============================================================================="
