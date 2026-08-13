#!/usr/bin/env bash
set -Eeuo pipefail
source "$(dirname "$0")/common.sh"
need_env
cd "$ROOT_DIR"

BACKUP_DIR="${1:-}"
if [[ -z "$BACKUP_DIR" ]]; then
  echo "Penggunaan: ./scripts/restore.sh backups/YYYYMMDD-HHMMSS"
  exit 1
fi
BACKUP_DIR="$(cd "$BACKUP_DIR" && pwd)"

[[ -f "$BACKUP_DIR/database.sql.gz" ]] || { echo "database.sql.gz tidak ditemukan"; exit 1; }

if [[ -f "$BACKUP_DIR/SHA256SUMS" ]]; then
  (cd "$BACKUP_DIR" && sha256sum -c SHA256SUMS)
fi

read -rp "Restore akan MENIMPA database saat ini. Ketik RESTORE untuk lanjut: " CONFIRM
[[ "$CONFIRM" == "RESTORE" ]] || { echo "Dibatalkan."; exit 1; }

DB_USER="$(grep '^DB_USERNAME=' "$ENV_FILE" | cut -d= -f2-)"
DB_NAME="$(grep '^DB_DATABASE=' "$ENV_FILE" | cut -d= -f2-)"

compose exec -T backend php artisan down --retry=60 || true

echo "==> Restore PostgreSQL"
gunzip -c "$BACKUP_DIR/database.sql.gz" | compose exec -T db psql -v ON_ERROR_STOP=1 -U "$DB_USER" -d "$DB_NAME"

if [[ -f "$BACKUP_DIR/storage.tar.gz" ]]; then
  echo "==> Restore storage"
  BACKEND_ID="$(compose ps -q backend)"
  docker_cmd cp "$BACKUP_DIR/storage.tar.gz" "$BACKEND_ID:/tmp/edunova-storage.tar.gz" >/dev/null
  docker_cmd exec "$BACKEND_ID" sh -lc 'rm -rf /var/www/html/storage/app/public/* && tar -xzf /tmp/edunova-storage.tar.gz -C /var/www/html/storage/app && chown -R www-data:www-data /var/www/html/storage/app/public && rm -f /tmp/edunova-storage.tar.gz'
fi

compose exec -T backend php artisan migrate --force
compose exec -T backend php artisan config:cache || true
compose exec -T backend php artisan view:cache || true
compose exec -T backend php artisan up || true

echo "Restore selesai."
