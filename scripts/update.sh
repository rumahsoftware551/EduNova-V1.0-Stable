#!/usr/bin/env bash
set -Eeuo pipefail
source "$(dirname "$0")/common.sh"
need_env
cd "$ROOT_DIR"

./scripts/backup.sh
compose exec -T backend php artisan down --retry=60 || true
compose build --pull backend frontend
compose run --rm backend php artisan migrate --force
compose up -d
compose exec -T backend php artisan config:cache || true
compose exec -T backend php artisan view:cache || true
compose exec -T backend php artisan up || true
compose ps

echo "Update EduNova selesai."
