# EduNova V1.0 Stable — Go-Live Checklist

## Sebelum Instalasi
- [ ] VPS/server Ubuntu tersedia dan bisa SSH.
- [ ] Domain/subdomain sudah dibuat.
- [ ] A record domain mengarah ke IP server.
- [ ] Port 80 dan 443 terbuka.
- [ ] Backup server lama tersedia jika ini migrasi.

## Instalasi
- [ ] `.env.production` dibuat dan permission `600`.
- [ ] `docker compose ... config` valid.
- [ ] PostgreSQL healthy.
- [ ] Redis healthy.
- [ ] Backend healthy.
- [ ] Frontend berjalan di localhost port 8080.
- [ ] Migration selesai tanpa error.
- [ ] Administrator pertama dibuat.

## Web & HTTPS
- [ ] Nginx `nginx -t` valid.
- [ ] HTTP domain membuka EduNova.
- [ ] Sertifikat HTTPS aktif.
- [ ] HTTP redirect ke HTTPS.
- [ ] `certbot renew --dry-run` berhasil.

## Uji Fitur
- [ ] Login Admin.
- [ ] Master akademik.
- [ ] Login Guru.
- [ ] Kelas Digital.
- [ ] Materi.
- [ ] Tugas dan submission.
- [ ] Quiz dan ujian.
- [ ] Gradebook.
- [ ] Komunikasi.
- [ ] Dynamic QR attendance.
- [ ] Login Siswa.
- [ ] Scan QR kamera via HTTPS.
- [ ] GPS/selfie (jika digunakan).
- [ ] PWA dapat di-install.

## Operasional
- [ ] `scripts/backup.sh` berhasil.
- [ ] Backup disalin ke media kedua.
- [ ] Uji restore dilakukan di server staging/clone.
- [ ] Administrator server mengetahui `scripts/status.sh` dan `scripts/logs.sh`.
- [ ] Manual Book diberikan ke operator/guru.
