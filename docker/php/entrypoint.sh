#!/bin/sh

set -e


echo "======================================================"
echo " EduNova V1.0 Stable"
echo " Backend Production Container"
echo "======================================================"


cd /var/www/html


# ============================================================
# VERIFY ARTISAN
# ============================================================

if [ ! -f "/var/www/html/artisan" ]; then
    echo ""
    echo "ERROR: Laravel artisan file tidak ditemukan."
    echo "Expected path:"
    echo "/var/www/html/artisan"
    echo ""
    echo "Isi /var/www/html:"
    ls -la /var/www/html
    exit 1
fi


# ============================================================
# REQUIRED DIRECTORIES
# ============================================================

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache


chown -R www-data:www-data \
    storage \
    bootstrap/cache

chmod -R 775 \
    storage \
    bootstrap/cache


# ============================================================
# CHECK APP KEY
# ============================================================

if [ -z "${APP_KEY:-}" ]; then
    echo ""
    echo "ERROR: APP_KEY belum diisi pada .env.production"
    echo ""
    echo "Buat APP_KEY terlebih dahulu."
    exit 1
fi


# ============================================================
# WAIT FOR POSTGRESQL
# ============================================================

echo ""
echo "Waiting for PostgreSQL..."

MAX_TRIES=60
TRY=1

until php -r '
$host = getenv("DB_HOST") ?: "db";
$port = getenv("DB_PORT") ?: "5432";
$db   = getenv("DB_DATABASE");
$user = getenv("DB_USERNAME");
$pass = getenv("DB_PASSWORD");

try {
    new PDO(
        "pgsql:host={$host};port={$port};dbname={$db}",
        $user,
        $pass,
        [
            PDO::ATTR_TIMEOUT => 3,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
    exit(0);
} catch (Throwable $e) {
    exit(1);
}
'
do

    if [ "$TRY" -ge "$MAX_TRIES" ]; then
        echo "ERROR: PostgreSQL tidak dapat dihubungi."
        exit 1
    fi

    echo "PostgreSQL belum siap... ($TRY/$MAX_TRIES)"

    TRY=$((TRY + 1))

    sleep 2

done


echo "PostgreSQL ready."


# ============================================================
# CLEAR OLD CACHE
# ============================================================

echo ""
echo "Clearing Laravel cache..."

php artisan optimize:clear || true


# ============================================================
# DATABASE MIGRATION
# ============================================================

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then

    echo ""
    echo "Running database migrations..."

    php artisan migrate --force

fi


# ============================================================
# STORAGE LINK
# ============================================================

echo ""
echo "Creating storage link..."

php artisan storage:link || true


# ============================================================
# PRODUCTION CACHE
# ============================================================

echo ""
echo "Optimizing Laravel..."

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true


# ============================================================
# PERMISSIONS AGAIN AFTER ARTISAN
# ============================================================

chown -R www-data:www-data \
    storage \
    bootstrap/cache

chmod -R 775 \
    storage \
    bootstrap/cache


# ============================================================
# START APACHE
# ============================================================

echo ""
echo "EduNova backend ready."
echo "Starting Apache..."
echo ""


exec docker-php-entrypoint "$@"