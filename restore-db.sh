#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# di-SAPA - Script Restore Database MySQL (dari file .sql atau .sql.gz)
# Penggunaan:
#   ./restore-db.sh backup_db/latest.sql.gz
#   ./restore-db.sh backup_db/disapa_db_2026-11-11_19-30-00.sql.gz --yes
# ==============================================================================

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

FILE_ARG="${1:-backup_db/latest.sql.gz}"
AUTO_YES="${2:-}"

if [ ! -f "$FILE_ARG" ]; then
    echo "[ERROR] File backup tidak ditemukan: $FILE_ARG" >&2
    exit 1
fi

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

if [ -z "$DB_ROOT_PASSWORD" ]; then
    echo "[ERROR] DB_ROOT_PASSWORD / DB_PASSWORD kosong di .env" >&2
    exit 1
fi

if [[ "$FILE_ARG" == *.gz ]]; then
    if ! gzip -t "$FILE_ARG"; then
        echo "[ERROR] File gzip backup rusak/korup: $FILE_ARG" >&2
        exit 1
    fi
fi

if [ "$AUTO_YES" != "--yes" ]; then
    read -rp "Restore '$FILE_ARG' ke database '$DB_DATABASE'? Data saat ini akan ditimpa [y/N]: " CONFIRM
    if [[ ! "$CONFIRM" =~ ^[Yy]$ ]]; then
        echo "Restore dibatalkan."
        exit 0
    fi
fi

DOCKER="docker"
if ! docker ps >/dev/null 2>&1; then
    DOCKER="sudo docker"
fi

if [[ "$FILE_ARG" == *.gz ]]; then
    gunzip -c "$FILE_ARG" | $DOCKER exec -i -e MYSQL_PWD="$DB_ROOT_PASSWORD" disapa_db mysql -uroot --default-character-set=utf8mb4 "$DB_DATABASE"
else
    cat "$FILE_ARG" | $DOCKER exec -i -e MYSQL_PWD="$DB_ROOT_PASSWORD" disapa_db mysql -uroot --default-character-set=utf8mb4 "$DB_DATABASE"
fi

$DOCKER exec disapa_app php artisan optimize:clear >/dev/null 2>&1 || true
echo "[OK] Database '$DB_DATABASE' berhasil direstore dari: $FILE_ARG"
