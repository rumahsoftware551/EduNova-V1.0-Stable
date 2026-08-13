# EduNova V1.0 Stable — Paket Final Go-Live

Build: 11 Agustus 2026  
Core roadmap: Phase 01–10 selesai.

## Isi Paket

- `source/backend/` — source Laravel 13 EduNova lengkap, tanpa `vendor`.
- `source/frontend/` — source React/Vite EduNova lengkap, tanpa `node_modules`.
- `docker-compose.production.yml` — PostgreSQL 18, Redis 8, backend PHP/Apache, frontend Nginx.
- `.env.production.example` — template konfigurasi production.
- `install-vps.sh` — instalasi otomatis Ubuntu + Docker + Nginx + HTTPS.
- `scripts/backup.sh` — backup PostgreSQL + upload + env.
- `scripts/restore.sh` — restore backup dengan konfirmasi.
- `scripts/create-admin.sh` — membuat sekolah dan administrator pertama.
- `scripts/update.sh` — backup, rebuild, migrate, start.
- `docs/` — Manual Book PDF dan DOCX.

## Arsitektur Production

```text
Internet
   |
HTTPS :443
   |
Nginx host + Certbot
   |
127.0.0.1:8080
   |
Frontend Nginx container
   |--------------------|
React/PWA static       /api, /sanctum, /storage
                         |
                   Backend PHP 8.4 + Apache
                         |
              -------------------------
              |                       |
         PostgreSQL 18             Redis 8
```

PostgreSQL dan Redis **tidak dipublish ke internet**. Frontend container hanya bind ke `127.0.0.1:8080`; domain publik masuk melalui Nginx host.

## Persyaratan Server

Rekomendasi minimum sekolah kecil/menengah:

- Ubuntu Server 24.04 LTS atau versi Ubuntu yang masih didukung Docker Engine.
- 2 vCPU.
- RAM 4 GB minimum; 8 GB lebih nyaman bila server juga menjalankan layanan lain.
- SSD 40 GB atau lebih, disesuaikan jumlah file materi/tugas/selfie.
- Domain/subdomain yang sudah diarahkan ke IP server.
- Port 80 dan 443 dapat diakses.
- SSH dengan akun sudo.

## Instalasi Otomatis

Upload/extract folder ini ke server, misalnya:

```bash
sudo mkdir -p /opt/edunova
sudo chown -R $USER:$USER /opt/edunova
cd /opt/edunova
```

Pastikan file paket berada di folder tersebut, lalu:

```bash
chmod +x install-vps.sh scripts/*.sh docker/php/entrypoint.sh
./install-vps.sh lms.sekolah.sch.id admin@sekolah.sch.id
```

Script akan:

1. memasang dependensi sistem,
2. memasang Docker Engine + Compose plugin bila belum ada,
3. membuat `.env.production` dengan password database/Redis dan APP_KEY acak,
4. build image frontend/backend,
5. menjalankan migration,
6. menjalankan container production,
7. membuat Nginx reverse proxy,
8. mencoba memasang HTTPS melalui Certbot jika DNS sudah aktif,
9. menampilkan status akhir.

## Administrator Pertama

Production tidak memasang akun demo secara default. Setelah instalasi:

```bash
./scripts/create-admin.sh
```

Masukkan nama sekolah, kode sekolah, NPSN (opsional), nama admin, username, email dan password.

## Backup

```bash
./scripts/backup.sh
```

Output:

```text
backups/YYYYMMDD-HHMMSS/
  database.sql.gz
  storage.tar.gz
  env.production
  SHA256SUMS
```

Folder backup mengandung data dan secret. Simpan di lokasi yang hanya dapat diakses administrator server.

## Restore

```bash
./scripts/restore.sh backups/20260811-120000
```

Restore meminta kata konfirmasi `RESTORE` sebelum menimpa database.

## Update Source

Setelah mengganti source dengan build EduNova yang lebih baru:

```bash
./scripts/update.sh
```

Script selalu menjalankan backup sebelum rebuild/migration.

## Status dan Log

```bash
./scripts/status.sh
./scripts/logs.sh backend
./scripts/logs.sh frontend
./scripts/logs.sh db
./scripts/logs.sh redis
```

## Health Check

Internal host:

```bash
curl http://127.0.0.1:8080/api/v1/health
```

Expected:

```json
{"status":"ok","service":"EduNova API","version":"1.0.0","phase":10}
```

## Catatan SSL

Sebelum `certbot --nginx`, domain harus sudah dapat membuka website melalui HTTP port 80. Jika instalasi otomatis melewati SSL karena DNS belum aktif, setelah DNS siap jalankan:

```bash
sudo certbot --nginx -d lms.sekolah.sch.id
sudo certbot renew --dry-run
```

## Keamanan Production

- Jangan upload `.env.production` ke repository publik.
- Jangan gunakan `INSTALL_DEMO_DATA=true` di server resmi.
- Ganti password administrator secara berkala sesuai kebijakan sekolah.
- Simpan backup di media/server kedua.
- Batasi SSH dan gunakan firewall server/provider.
- Lakukan update OS dan Docker secara berkala.

Lihat `docs/Manual_Book_EduNova_V1.0_Stable.pdf` untuk panduan penggunaan fitur dan instalasi yang lebih lengkap.
