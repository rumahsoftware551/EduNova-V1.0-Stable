#!/usr/bin/env bash
set -Eeuo pipefail
source "$(dirname "$0")/common.sh"
need_env
cd "$ROOT_DIR"

STAMP="$(date +%Y%m%d-%H%M%S)"
DEST="$ROOT_DIR/backups/$STAMP"
mkdir -p "$DEST"
chmod 700 "$ROOT_DIR/backups" "$DEST"

DB_USER="$(grep '^DB_USERNAME=' "$ENV_FILE" | cut -d= -f2-)"
DB_NAME="$(grep '^DB_DATABASE=' "$ENV_FILE" | cut -d= -f2-)"

echo "==> Backup PostgreSQL"
compose exec -T db pg_dump -U "$DB_USER" -d "$DB_NAME" --clean --if-exists | gzip -9 > "$DEST/database.sql.gz"

echo "==> Backup storage upload"
BACKEND_ID="$(compose ps -q backend)"
docker_cmd exec "$BACKEND_ID" sh -lc 'tar -czf /tmp/edunova-storage.tar.gz -C /var/www/html/storage/app public'
docker_cmd cp "$BACKEND_ID:/tmp/edunova-storage.tar.gz" "$DEST/storage.tar.gz" >/dev/null
docker_cmd exec "$BACKEND_ID" rm -f /tmp/edunova-storage.tar.gz

# Environment backup contains secrets: keep private permissions.
cp "$ENV_FILE" "$DEST/env.production"
chmod 600 "$DEST/env.production"

sha256sum "$DEST"/* > "$DEST/SHA256SUMS"

echo "Backup selesai: $DEST"
