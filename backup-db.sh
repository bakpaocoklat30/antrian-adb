#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# di-SAPA - Script Backup Database MySQL (Docker Container disapa_db)
# Menyimpan cadangan terkompresi (.sql.gz) ke folder backup_db/ + rotasi otomatis.
# Penggunaan:
#   ./backup-db.sh
#   BACKUP_KEEP=30 ./backup-db.sh
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

if [ ! -f .env ]; then
    echo "[ERROR] File .env tidak ditemukan di $SCRIPT_DIR" >&2
    exit 1
fi

get_env() {
    local key="$1"
    grep -E "^${key}=" .env | head -n1 | cut -d'=' -f2- | sed -E 's/^["'\'']|["'\'']$//g' || true
}

DB_DATABASE="$(get_env DB_DATABASE)"
DB_DATABASE="${DB_DATABASE:-disapa_db}"
DB_ROOT_PASSWORD="$(get_env DB_ROOT_PASSWORD)"
[ -n "$DB_ROOT_PASSWORD" ] || DB_ROOT_PASSWORD="$(get_env DB_PASSWORD)"
BACKUP_KEEP="${BACKUP_KEEP:-30}"

if [ -z "$DB_ROOT_PASSWORD" ]; then
    echo "[ERROR] DB_ROOT_PASSWORD / DB_PASSWORD kosong di .env" >&2
    exit 1
fi

DOCKER="docker"
if ! docker ps >/dev/null 2>&1; then
    if command -v sudo >/dev/null 2>&1; then
        DOCKER="sudo docker"
    else
        echo "[ERROR] Tidak memiliki izin menjalankan docker." >&2
        exit 1
    fi
fi

if ! $DOCKER ps --format '{{.Names}}' | grep -qx 'disapa_db'; then
    echo "[ERROR] Container disapa_db tidak sedang berjalan." >&2
    exit 1
fi

BACKUP_DIR="$SCRIPT_DIR/backup_db"
mkdir -p "$BACKUP_DIR"

TIMESTAMP="$(date '+%Y-%m-%d_%H-%M-%S')"
TARGET_FILE="$BACKUP_DIR/${DB_DATABASE}_${TIMESTAMP}.sql.gz"
TEMP_FILE="${TARGET_FILE}.tmp"
LATEST_FILE="$BACKUP_DIR/latest.sql.gz"

$DOCKER exec -e MYSQL_PWD="$DB_ROOT_PASSWORD" disapa_db \
    mysqldump -uroot --single-transaction --routines --triggers --quick --default-character-set=utf8mb4 "$DB_DATABASE" \
    | gzip -c > "$TEMP_FILE"

if [ ! -s "$TEMP_FILE" ] || ! gzip -t "$TEMP_FILE"; then
    rm -f "$TEMP_FILE"
    echo "[ERROR] Backup gagal atau file gzip korup." >&2
    exit 1
fi

chmod 600 "$TEMP_FILE"
mv "$TEMP_FILE" "$TARGET_FILE"
cp -f "$TARGET_FILE" "$LATEST_FILE"
chmod 600 "$LATEST_FILE"

# Rotasi backup lama agar disk tidak penuh (simpan BACKUP_KEEP file terbaru)
if [[ "$BACKUP_KEEP" =~ ^[0-9]+$ ]] && [ "$BACKUP_KEEP" -gt 0 ]; then
    OLD_FILES="$(ls -1t "$BACKUP_DIR"/${DB_DATABASE}_*.sql.gz 2>/dev/null | tail -n +"$((BACKUP_KEEP + 1))" || true)"
    if [ -n "$OLD_FILES" ]; then
        echo "$OLD_FILES" | xargs rm -f
    fi
fi

SIZE_HUMAN="$(du -h "$TARGET_FILE" | awk '{print $1}')"
echo "[OK] Backup tersimpan: ${TARGET_FILE} (${SIZE_HUMAN})"
echo "[OK] Salinan terbaru : ${LATEST_FILE}"
