#!/usr/bin/env bash
set -Eeuo pipefail
source "$(dirname "$0")/common.sh"
need_env
cd "$ROOT_DIR"

read -rp "Nama sekolah: " SCHOOL_NAME
read -rp "Kode sekolah (contoh SMKN1-ABC): " SCHOOL_CODE
read -rp "NPSN (boleh kosong): " SCHOOL_NPSN
read -rp "Nama administrator: " ADMIN_NAME
read -rp "Username administrator: " ADMIN_USERNAME
read -rp "Email administrator: " ADMIN_EMAIL
read -rsp "Password administrator: " ADMIN_PASSWORD
echo
read -rsp "Ulangi password: " ADMIN_PASSWORD_CONFIRM
echo

if [[ "$ADMIN_PASSWORD" != "$ADMIN_PASSWORD_CONFIRM" ]]; then
  echo "Password tidak sama."
  exit 1
fi
if [[ ${#ADMIN_PASSWORD} -lt 10 ]]; then
  echo "Gunakan password minimal 10 karakter."
  exit 1
fi

SLUG="$(printf '%s' "$SCHOOL_CODE" | tr '[:upper:]' '[:lower:]' | tr -cs 'a-z0-9' '-' | sed 's/^-//;s/-$//')"

compose run --rm \
  -e SCHOOL_NAME="$SCHOOL_NAME" \
  -e SCHOOL_CODE="$SCHOOL_CODE" \
  -e SCHOOL_NPSN="$SCHOOL_NPSN" \
  -e SCHOOL_SLUG="$SLUG" \
  -e ADMIN_NAME="$ADMIN_NAME" \
  -e ADMIN_USERNAME="$ADMIN_USERNAME" \
  -e ADMIN_EMAIL="$ADMIN_EMAIL" \
  -e ADMIN_PASSWORD="$ADMIN_PASSWORD" \
  backend php artisan tinker --execute='\
$school=\\App\\Models\\School::updateOrCreate(["code"=>getenv("SCHOOL_CODE")],["name"=>getenv("SCHOOL_NAME"),"npsn"=>getenv("SCHOOL_NPSN") ?: null,"slug"=>getenv("SCHOOL_SLUG"),"timezone"=>"Asia/Jakarta","is_active"=>true]); \
\\App\\Models\\User::updateOrCreate(["username"=>getenv("ADMIN_USERNAME")],["school_id"=>$school->id,"name"=>getenv("ADMIN_NAME"),"email"=>getenv("ADMIN_EMAIL"),"password"=>getenv("ADMIN_PASSWORD"),"role"=>"admin","status"=>"active","subtitle"=>"Administrator Sekolah","email_verified_at"=>now()]);'

echo "Administrator EduNova berhasil dibuat/diperbarui."
