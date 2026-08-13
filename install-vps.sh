#!/usr/bin/env bash
set -Eeuo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$ROOT_DIR"

if [[ "$(id -u)" -eq 0 ]]; then
  SUDO=""
else
  SUDO="sudo"
fi

if ! command -v apt-get >/dev/null 2>&1; then
  echo "Script otomatis ini ditujukan untuk Ubuntu/Debian berbasis apt."
  exit 1
fi

DOMAIN="${1:-}"
EMAIL="${2:-}"

if [[ -z "$DOMAIN" ]]; then
  read -rp "Domain EduNova (contoh: lms.sekolah.sch.id): " DOMAIN
fi
if [[ -z "$EMAIL" ]]; then
  read -rp "Email administrator/Let's Encrypt: " EMAIL
fi

if [[ -z "$DOMAIN" || -z "$EMAIL" ]]; then
  echo "Domain dan email wajib diisi."
  exit 1
fi

APP_PORT="8080"

set_env() {
  local key="$1" value="$2" file="$3"
  if grep -qE "^${key}=" "$file"; then
    sed -i "s|^${key}=.*|${key}=${value}|" "$file"
  else
    printf '%s=%s\n' "$key" "$value" >> "$file"
  fi
}

echo "==> [1/10] Menyiapkan paket sistem"
$SUDO apt-get update
$SUDO apt-get install -y ca-certificates curl gnupg nginx openssl snapd
$SUDO systemctl enable --now snapd.socket >/dev/null 2>&1 || true

if ! command -v docker >/dev/null 2>&1 || ! docker compose version >/dev/null 2>&1; then
  echo "==> [2/10] Menginstal Docker Engine dari repository resmi Docker"
  $SUDO install -m 0755 -d /etc/apt/keyrings
  $SUDO curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
  $SUDO chmod a+r /etc/apt/keyrings/docker.asc
  . /etc/os-release
  CODENAME="${UBUNTU_CODENAME:-${VERSION_CODENAME:-}}"
  if [[ -z "$CODENAME" ]]; then
    echo "Tidak dapat menentukan codename Ubuntu. Instal Docker manual lalu jalankan script lagi."
    exit 1
  fi
  echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $CODENAME stable" | $SUDO tee /etc/apt/sources.list.d/docker.list >/dev/null
  $SUDO apt-get update
  $SUDO apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
else
  echo "==> [2/10] Docker sudah tersedia"
fi

$SUDO systemctl enable --now docker

if [[ ! -f .env.production ]]; then
  cp .env.production.example .env.production
fi
chmod 600 .env.production

DB_PASS="$(openssl rand -hex 24)"
REDIS_PASS="$(openssl rand -hex 24)"
APP_KEY="base64:$(openssl rand -base64 32 | tr -d '\n')"

# Jika password sudah pernah dikustomisasi, jangan timpa pada rerun.
CURRENT_DB_PASS="$(grep '^DB_PASSWORD=' .env.production | cut -d= -f2- || true)"
CURRENT_REDIS_PASS="$(grep '^REDIS_PASSWORD=' .env.production | cut -d= -f2- || true)"
CURRENT_APP_KEY="$(grep '^APP_KEY=' .env.production | cut -d= -f2- || true)"
[[ -n "$CURRENT_DB_PASS" && "$CURRENT_DB_PASS" != CHANGE_ME_DB_PASSWORD ]] || set_env DB_PASSWORD "$DB_PASS" .env.production
[[ -n "$CURRENT_REDIS_PASS" && "$CURRENT_REDIS_PASS" != CHANGE_ME_REDIS_PASSWORD ]] || set_env REDIS_PASSWORD "$REDIS_PASS" .env.production
[[ -n "$CURRENT_APP_KEY" ]] || set_env APP_KEY "$APP_KEY" .env.production

set_env APP_URL "https://$DOMAIN" .env.production
set_env APP_PORT "$APP_PORT" .env.production
set_env SANCTUM_STATEFUL_DOMAINS "$DOMAIN" .env.production
set_env CORS_ALLOWED_ORIGINS "https://$DOMAIN" .env.production
set_env EDUNOVA_DOMAIN "$DOMAIN" .env.production
set_env LETSENCRYPT_EMAIL "$EMAIL" .env.production
set_env APP_ENV production .env.production
set_env APP_DEBUG false .env.production
set_env SESSION_SECURE_COOKIE true .env.production

# Validate Compose before making changes.
echo "==> [3/10] Memvalidasi Docker Compose"
docker compose --env-file .env.production -f docker-compose.production.yml config >/dev/null

echo "==> [4/10] Membangun image EduNova production"
$SUDO docker compose --env-file .env.production -f docker-compose.production.yml build --pull

echo "==> [5/10] Menyalakan PostgreSQL dan Redis"
$SUDO docker compose --env-file .env.production -f docker-compose.production.yml up -d db redis

# Wait until database is healthy.
for i in $(seq 1 60); do
  STATUS="$($SUDO docker compose --env-file .env.production -f docker-compose.production.yml ps --format json db 2>/dev/null | grep -o '"Health":"[^"]*"' | head -1 | cut -d'"' -f4 || true)"
  [[ "$STATUS" == "healthy" ]] && break
  sleep 2
done

echo "==> [6/10] Menjalankan migration database"
$SUDO docker compose --env-file .env.production -f docker-compose.production.yml run --rm backend php artisan migrate --force

INSTALL_DEMO="$(grep '^INSTALL_DEMO_DATA=' .env.production | cut -d= -f2- || true)"
if [[ "$INSTALL_DEMO" == "true" ]]; then
  echo "==> Memasang demo seeder (INSTALL_DEMO_DATA=true)"
  $SUDO docker compose --env-file .env.production -f docker-compose.production.yml run --rm backend php artisan db:seed --force
fi

echo "==> [7/10] Menyalakan aplikasi"
$SUDO docker compose --env-file .env.production -f docker-compose.production.yml up -d

# Host Nginx points to container frontend bound on localhost only.
echo "==> [8/10] Menyiapkan Nginx host"
NGINX_SITE="/etc/nginx/sites-available/edunova"
sed -e "s/__DOMAIN__/$DOMAIN/g" -e "s/__APP_PORT__/$APP_PORT/g" docker/nginx-host.conf.template | $SUDO tee "$NGINX_SITE" >/dev/null
$SUDO ln -sfn "$NGINX_SITE" /etc/nginx/sites-enabled/edunova
$SUDO rm -f /etc/nginx/sites-enabled/default
$SUDO nginx -t
$SUDO systemctl enable --now nginx
$SUDO systemctl reload nginx

# Firewall only when UFW exists. Never close SSH.
if command -v ufw >/dev/null 2>&1; then
  $SUDO ufw allow OpenSSH >/dev/null || true
  $SUDO ufw allow 'Nginx Full' >/dev/null || true
fi

echo "==> [9/10] Menyiapkan HTTPS Certbot"
INSTALL_SSL="$(grep '^INSTALL_SSL=' .env.production | cut -d= -f2- || true)"
if [[ "$INSTALL_SSL" == "true" ]]; then
  if ! command -v certbot >/dev/null 2>&1; then
    $SUDO snap install --classic certbot
    $SUDO ln -sfn /snap/bin/certbot /usr/local/bin/certbot
  fi
  echo "Pastikan DNS $DOMAIN sudah mengarah ke IP server ini sebelum proses sertifikat."
  if curl -fsS --max-time 10 "http://$DOMAIN" >/dev/null 2>&1; then
    $SUDO certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" --redirect || {
      echo "WARNING: SSL belum berhasil. Aplikasi HTTP tetap aktif; periksa DNS lalu jalankan: sudo certbot --nginx -d $DOMAIN"
    }
  else
    echo "WARNING: Domain belum dapat diakses melalui HTTP. SSL dilewati sementara."
    echo "Setelah DNS aktif jalankan: sudo certbot --nginx -d $DOMAIN"
  fi
fi

echo "==> [10/10] Pemeriksaan akhir"
$SUDO docker compose --env-file .env.production -f docker-compose.production.yml ps
curl -fsS "http://127.0.0.1:$APP_PORT/api/v1/health" || true

echo
echo "============================================================"
echo " EduNova V1.0 Stable berhasil dipasang"
echo "============================================================"
echo "Domain : https://$DOMAIN"
echo "Folder : $ROOT_DIR"
echo
echo "LANGKAH WAJIB BERIKUTNYA:"
echo "  ./scripts/create-admin.sh"
echo
echo "Backup manual:"
echo "  ./scripts/backup.sh"
echo
echo "Status:"
echo "  docker compose --env-file .env.production -f docker-compose.production.yml ps"
