#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# di-SAPA - Script Tunggal Deploy, Update Git, Backup & Restore (Ubuntu Docker)
# Cukup 1 script untuk semua kebutuhan di server Ubuntu:
#   ./deploy-ubuntu.sh                  -> Auto git pull + build + migrate + backup
#   ./deploy-ubuntu.sh backup           -> Backup database (.sql.gz) ke backup_db/
#   ./deploy-ubuntu.sh restore [file]   -> Restore database dari file backup
#   ./deploy-ubuntu.sh --no-pull        -> Deploy tanpa git pull
#
# Opsi non-interaktif instalasi awal:
#   SERVER_IP=202.65.116.9 APP_PORT=7030 ./deploy-ubuntu.sh
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

# Elevasi ke root otomatis agar aman dijalankan di /var/www maupun cron
if [ "$(id -u)" -ne 0 ] && command -v sudo >/dev/null 2>&1; then
    exec sudo -E bash "$0" "$@"
fi

if [ "$(id -u)" -ne 0 ]; then
    error "Script ini membutuhkan akses root atau sudo."
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
    [ -f "$file" ] || return 0
    grep -E "^${key}=" "$file" | head -n1 | cut -d'=' -f2- | sed -E 's/^["'\'']|["'\'']$//g' || true
}

install_docker_ubuntu() {
    info "Docker belum ditemukan. Memasang Docker Engine & Compose Plugin untuk Ubuntu..."
    apt-get update -y
    apt-get install -y ca-certificates curl gnupg lsb-release git

    install -m 0755 -d /etc/apt/keyrings
    if [ ! -f /etc/apt/keyrings/docker.asc ]; then
        curl -fsSL https://download.docker.com/linux/ubuntu/gpg | tee /etc/apt/keyrings/docker.asc >/dev/null
        chmod a+r /etc/apt/keyrings/docker.asc
    fi

    local arch codename
    arch="$(dpkg --print-architecture)"
    codename="$(. /etc/os-release && echo "${VERSION_CODENAME:-$UBUNTU_CODENAME}")"
    echo "deb [arch=${arch} signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu ${codename} stable" | \
        tee /etc/apt/sources.list.d/docker.list >/dev/null

    apt-get update -y
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    systemctl enable --now docker
    ok "Docker berhasil dipasang."
}

ensure_docker_ready() {
    if ! command -v docker >/dev/null 2>&1 || ! docker compose version >/dev/null 2>&1; then
        if [ -f /etc/os-release ] && grep -qiE "ubuntu|debian" /etc/os-release; then
            install_docker_ubuntu
        else
            error "Docker & Docker Compose v2 belum terpasang. Pasang Docker terlebih dahulu."
        fi
    fi
}

do_backup() {
    [ -f .env ] || error "File .env tidak ditemukan di $SCRIPT_DIR"

    local db_name db_pass keep backup_dir ts target tmp_file latest old_files size_human
    db_name="$(get_env DB_DATABASE)"
    db_name="${db_name:-disapa_db}"
    db_pass="$(get_env DB_ROOT_PASSWORD)"
    [ -n "$db_pass" ] || db_pass="$(get_env DB_PASSWORD)"
    keep="${BACKUP_KEEP:-30}"

    [ -n "$db_pass" ] || error "DB_ROOT_PASSWORD / DB_PASSWORD kosong di .env"
    docker ps --format '{{.Names}}' | grep -qx 'disapa_db' || error "Container disapa_db tidak sedang berjalan."

    backup_dir="$SCRIPT_DIR/backup_db"
    mkdir -p "$backup_dir"

    ts="$(date '+%Y-%m-%d_%H-%M-%S')"
    target="$backup_dir/${db_name}_${ts}.sql.gz"
    tmp_file="${target}.tmp"
    latest="$backup_dir/latest.sql.gz"

    docker exec -e MYSQL_PWD="$db_pass" disapa_db \
        mysqldump -uroot --single-transaction --routines --triggers --quick --default-character-set=utf8mb4 "$db_name" \
        | gzip -c > "$tmp_file"

    if [ ! -s "$tmp_file" ] || ! gzip -t "$tmp_file"; then
        rm -f "$tmp_file"
        error "Backup gagal atau file gzip korup."
    fi

    chmod 600 "$tmp_file"
    mv "$tmp_file" "$target"
    cp -f "$target" "$latest"
    chmod 600 "$latest"

    if [[ "$keep" =~ ^[0-9]+$ ]] && [ "$keep" -gt 0 ]; then
        old_files="$(ls -1t "$backup_dir"/${db_name}_*.sql.gz 2>/dev/null | tail -n +"$((keep + 1))" || true)"
        if [ -n "$old_files" ]; then
            echo "$old_files" | xargs rm -f
        fi
    fi

    size_human="$(du -h "$target" | awk '{print $1}')"
    ok "Backup tersimpan: ${target} (${size_human})"
    ok "Salinan terbaru : ${latest}"
}

do_restore() {
    local file_arg="${1:-backup_db/latest.sql.gz}"
    local auto_yes="${2:-}"
    [ -f "$file_arg" ] || error "File backup tidak ditemukan: $file_arg"
    [ -f .env ] || error "File .env tidak ditemukan di $SCRIPT_DIR"

    local db_name db_pass confirm
    db_name="$(get_env DB_DATABASE)"
    db_name="${db_name:-disapa_db}"
    db_pass="$(get_env DB_ROOT_PASSWORD)"
    [ -n "$db_pass" ] || db_pass="$(get_env DB_PASSWORD)"
    [ -n "$db_pass" ] || error "DB_ROOT_PASSWORD / DB_PASSWORD kosong di .env"

    if [[ "$file_arg" == *.gz ]] && ! gzip -t "$file_arg"; then
        error "File gzip backup rusak/korup: $file_arg"
    fi

    if [ "$auto_yes" != "--yes" ]; then
        read -rp "Restore '$file_arg' ke database '$db_name'? Data saat ini akan ditimpa [y/N]: " confirm
        if [[ ! "$confirm" =~ ^[Yy]$ ]]; then
            echo "Restore dibatalkan."
            exit 0
        fi
    fi

    if [[ "$file_arg" == *.gz ]]; then
        gunzip -c "$file_arg" | docker exec -i -e MYSQL_PWD="$db_pass" disapa_db mysql -uroot --default-character-set=utf8mb4 "$db_name"
    else
        cat "$file_arg" | docker exec -i -e MYSQL_PWD="$db_pass" disapa_db mysql -uroot --default-character-set=utf8mb4 "$db_name"
    fi

    docker exec -u root disapa_app php artisan optimize:clear >/dev/null 2>&1 || true
    ok "Database '$db_name' berhasil direstore dari: $file_arg"
}

pull_latest_git() {
    if [ ! -d "$SCRIPT_DIR/.git" ]; then
        warn "Folder ini bukan repository Git (.git tidak ada). Melewati git pull otomatis."
        return 0
    fi

    if ! command -v git >/dev/null 2>&1; then
        warn "Git belum terpasang, melewati git pull otomatis."
        return 0
    fi

    if ! git config --global --get-all safe.directory 2>/dev/null | grep -Fxq "$SCRIPT_DIR"; then
        git config --global --add safe.directory "$SCRIPT_DIR"
    fi

    local branch before_rev after_rev
    branch="$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo main)"
    before_rev="$(git rev-parse HEAD 2>/dev/null || true)"

    info "Menarik update terbaru dari Git (branch ${branch})..."
    if git -c core.fileMode=false pull --ff-only origin "$branch"; then
        after_rev="$(git rev-parse HEAD 2>/dev/null || true)"
        chmod +x "$SCRIPT_DIR/deploy-ubuntu.sh" || true
        if [ -n "$before_rev" ] && [ -n "$after_rev" ] && [ "$before_rev" != "$after_rev" ]; then
            ok "Update Git berhasil ditarik (${before_rev:0:7} -> ${after_rev:0:7}). Menjalankan ulang script versi terbaru..."
            exec bash "$0" --no-pull "$@"
        else
            ok "Kode sudah versi terbaru."
        fi
    else
        warn "git pull tidak dapat fast-forward otomatis; melanjutkan deploy dengan kode yang ada di server."
    fi
}

do_deploy() {
    local do_pull=1
    if [ "${1:-}" = "--no-pull" ]; then
        do_pull=0
    fi

    if [ "$do_pull" -eq 1 ]; then
        pull_latest_git "$@"
    fi

    ensure_docker_ready
    ok "Menggunakan Docker: $(docker --version)"

    # Gunakan IP & Port tersimpan dari .env saat update ulang, kecuali ditanya di instalasi awal atau di-override env var
    local existing_env existing_url existing_port detected_ip default_ip pma_port app_url compose_cmd current_db_pass new_db_pass current_app_key user_count
    existing_env="$(get_env APP_ENV)"
    existing_url="$(get_env APP_URL)"
    existing_port="$(get_env APP_PORT)"

    if [ -z "${SERVER_IP:-}" ]; then
        if [ "$existing_env" = "production" ] && [ -n "$existing_url" ] && [[ "$existing_url" != *"localhost"* ]]; then
            SERVER_IP="$(echo "$existing_url" | sed -E 's|^https?://([^:/]+).*|\1|')"
        else
            detected_ip="$(curl -4 -fsSL --max-time 3 https://api.ipify.org 2>/dev/null || hostname -I 2>/dev/null | awk '{print $1}' || true)"
            default_ip="${detected_ip:-127.0.0.1}"
            if [ -t 0 ]; then
                echo ""
                read -rp "Masukkan IP Publik Server [${default_ip}]: " INPUT_IP
                SERVER_IP="${INPUT_IP:-$default_ip}"
            else
                SERVER_IP="$default_ip"
            fi
        fi
    fi

    if [ -z "${APP_PORT:-}" ]; then
        if [ "$existing_env" = "production" ] && [ -n "$existing_port" ]; then
            APP_PORT="$existing_port"
        elif [ -t 0 ]; then
            read -rp "Masukkan Port Aplikasi yang dibuka (misal 7030, 8000, atau 80) [8000]: " INPUT_PORT
            APP_PORT="${INPUT_PORT:-8000}"
        else
            APP_PORT="8000"
        fi
    fi

    if ! [[ "$APP_PORT" =~ ^[0-9]+$ ]] || [ "$APP_PORT" -lt 1 ] || [ "$APP_PORT" -gt 65535 ]; then
        error "Port tidak valid: '${APP_PORT}'. Gunakan angka 1-65535."
    fi

    info "Menyiapkan konfigurasi .env produksi (${SERVER_IP}:${APP_PORT})..."
    if [ ! -f .env ]; then
        cp .env.example .env
    fi

    current_db_pass="$(get_env DB_ROOT_PASSWORD)"
    if [ -z "$current_db_pass" ] || [ "$current_db_pass" = "disapa_secret" ]; then
        if command -v openssl >/dev/null 2>&1; then
            new_db_pass="$(openssl rand -base64 32 | tr -dc 'A-Za-z0-9' | head -c 24)"
        else
            new_db_pass="$(tr -dc 'A-Za-z0-9' < /dev/urandom | head -c 24)"
        fi
        set_env "DB_ROOT_PASSWORD" "$new_db_pass"
        set_env "DB_PASSWORD" "$new_db_pass"
        ok "Password database acak yang kuat berhasil dibuat di .env."
    else
        set_env "DB_PASSWORD" "$current_db_pass"
    fi

    if [ "$APP_PORT" -eq 80 ]; then
        app_url="http://${SERVER_IP}"
    else
        app_url="http://${SERVER_IP}:${APP_PORT}"
    fi

    pma_port="8081"
    if [ "$APP_PORT" -eq 8081 ]; then
        pma_port="8082"
    fi

    set_env "APP_ENV" "production"
    set_env "APP_DEBUG" "false"
    set_env "APP_PORT" "$APP_PORT"
    set_env "APP_URL" "$app_url"
    set_env "PMA_PORT" "$pma_port"
    set_env "DB_CONNECTION" "mysql"
    set_env "DB_HOST" "db"
    set_env "DB_PORT" "3306"
    set_env "DB_DATABASE" "disapa_db"
    set_env "DB_USERNAME" "root"
    set_env "REDIS_HOST" "redis"

    compose_cmd="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
    info "Membangun dan menyalakan container Docker pada ${app_url}..."
    $compose_cmd up -d --build

    info "Mengatur izin folder storage & bootstrap/cache..."
    docker exec -u root disapa_app sh -c "mkdir -p /var/www/storage/app/tts-cache /var/www/storage/framework/views /var/www/storage/framework/cache /var/www/storage/framework/sessions /var/www/storage/logs && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R ug+rwx /var/www/storage /var/www/bootstrap/cache"

    info "Menginstal dependensi PHP produksi (Composer)..."
    docker exec -u root -e COMPOSER_HOME=/tmp/composer disapa_app composer install --no-dev --optimize-autoloader --no-interaction
    docker exec -u root disapa_app chmod -R a+rX /var/www/vendor

    current_app_key="$(get_env APP_KEY)"
    if [ -z "$current_app_key" ]; then
        info "Membuat APP_KEY baru..."
        docker exec -u root disapa_app php artisan key:generate --force --no-interaction
    fi
    docker exec -u root disapa_app sh -c "chgrp 33 /var/www/.env && chmod 640 /var/www/.env"

    info "Membersihkan cache file konfigurasi sebelum migrasi..."
    docker exec -u root disapa_app sh -c "php artisan config:clear && php artisan route:clear && php artisan view:clear"

    user_count="$(docker exec -u root disapa_app php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); try { echo App\Models\User::count(); } catch (Throwable \$e) { echo 0; }" 2>/dev/null || echo 0)"
    if [ "${user_count:-0}" -gt 0 ]; then
        info "Data database terdeteksi (${user_count} user). Membuat backup pengaman sebelum migrasi..."
        do_backup || warn "Backup pra-migrasi dilewati."
    fi

    info "Menjalankan migrasi database..."
    docker exec -u root disapa_app php artisan migrate --force --no-interaction

    info "Membersihkan cache database & aplikasi..."
    docker exec -u root disapa_app php artisan optimize:clear

    user_count="$(docker exec -u root disapa_app php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo App\Models\User::count();")"
    if [ "${user_count:-0}" = "0" ]; then
        info "Tabel users kosong, menjalankan seeder akun awal..."
        docker exec -u root disapa_app php artisan db:seed --force --no-interaction
        do_backup || warn "Backup awal gagal dibuat."
    else
        ok "Tabel users sudah berisi ${user_count} akun, melewati seeder awal."
    fi

    if [ -d /etc/cron.d ]; then
        info "Menjadwalkan backup database otomatis setiap hari pukul 02:00 WIB..."
        mkdir -p "$SCRIPT_DIR/backup_db"
        printf "PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin\n0 2 * * * root %s/deploy-ubuntu.sh backup >> %s/backup_db/cron.log 2>&1\n" "$SCRIPT_DIR" "$SCRIPT_DIR" | tee /etc/cron.d/disapa-db-backup >/dev/null
        chmod 644 /etc/cron.d/disapa-db-backup
        ok "Cron backup harian aktif di /etc/cron.d/disapa-db-backup."
    fi

    docker exec -u root disapa_app php artisan storage:link --no-interaction >/dev/null 2>&1 || true

    info "Mengoptimalkan cache Laravel untuk produksi..."
    docker exec -u root disapa_app php artisan config:cache
    docker exec -u root disapa_app php artisan route:cache
    docker exec -u root disapa_app php artisan view:cache
    docker exec -u root disapa_app sh -c "chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R ug+rwx /var/www/storage /var/www/bootstrap/cache"

    if command -v ufw >/dev/null 2>&1 && ufw status 2>/dev/null | grep -q "Status: active"; then
        info "UFW aktif terdeteksi, membuka port ${APP_PORT}/tcp..."
        ufw allow "${APP_PORT}/tcp" >/dev/null || true
    fi

    echo ""
    echo "=============================================================================="
    ok "DEPLOYMENT di-SAPA DI SERVER UBUNTU SELESAI!"
    echo "=============================================================================="
    echo " URL Publik       : ${app_url}"
    echo " Layar Monitor    : ${app_url}/layar"
    echo " Login Petugas    : ${app_url}/login"
    echo ""
    echo " Update Kode di Masa Depan (cukup 1 perintah):"
    echo "   ./deploy-ubuntu.sh"
    echo ""
    echo " Backup & Restore Database:"
    echo " - Backup manual  : ./deploy-ubuntu.sh backup"
    echo " - Restore manual : ./deploy-ubuntu.sh restore backup_db/latest.sql.gz"
    echo " - Download cepat : Portal Super Admin -> Backup Database (.sql)"
    echo ""
    echo " Akun Bawaan Seeder (segera ganti password setelah login!):"
    echo " - Super Admin    : admin@smkn1adw.sch.id | password"
    echo " - Scanner OSIS   : osis1@smkn1adw.sch.id | password"
    echo " - Verifikator    : loket1@smkn1adw.sch.id .. loket7@smkn1adw.sch.id | password"
    echo ""
    echo " Keamanan Server:"
    echo " - Hanya port ${APP_PORT} yang dibuka untuk akses publik."
    echo " - Port MySQL (3306), phpMyAdmin (${pma_port}), & Redis (6379) dikunci ke 127.0.0.1."
    echo " - Akses phpMyAdmin via SSH tunnel:"
    echo "     ssh -L ${pma_port}:127.0.0.1:${pma_port} user@${SERVER_IP}"
    echo "=============================================================================="
}

case "${1:-deploy}" in
    backup)
        do_backup
        ;;
    restore)
        do_restore "${2:-}" "${3:-}"
        ;;
    --no-pull)
        do_deploy "--no-pull"
        ;;
    deploy|"")
        do_deploy
        ;;
    *)
        echo "Penggunaan: $0 [deploy | --no-pull | backup | restore <file> [--yes]]"
        exit 1
        ;;
esac
