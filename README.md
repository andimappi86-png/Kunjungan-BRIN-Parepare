# BRIN Visit — Sistem Informasi Kunjungan Kantor BRIN

Aplikasi web untuk mengelola permohonan kunjungan, data pengunjung, jadwal kunjungan, verifikasi administrator, serta konfirmasi kepada pengunjung di lingkungan Kantor BRIN.

## 1. Spesifikasi Teknis & Lingkungan
- **Framework:** Laravel v13.37.0
- **PHP:** v8.4.15
- **Node.js:** v26.9.0
- **NPM:** v11.19.1
- **Database:** SQLite (default development) / MySQL / MariaDB compatible
- **Template Engine:** Blade & Tailwind CSS (via Vite)

## 2. Cara Instalasi & Menjalankan Project
1. Masuk ke direktori project:
   ```bash
   cd brin-visit
   ```
2. Salin dan sesuaikan konfigurasi environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Jalankan migrasi database:
   ```bash
   php artisan migrate
   ```
4. Instal dan build aset frontend:
   ```bash
   npm install && npm run build
   ```
5. Jalankan server pengembangan:
   ```bash
   php artisan serve
   ```
   Akses aplikasi melalui `http://127.0.0.1:8000`.

## 3. Konfigurasi Environment & Database
Project dikonfigurasi menggunakan SQLite (`database/database.sqlite`) untuk kemudahan pengembangan awal tanpa mengorbankan kompatibilitas MySQL/MariaDB. Variabel environment dapat disesuaikan pada file `.env`.

## 4. Pengujian
Jalankan test bawaan Laravel untuk memastikan integritas sistem:
```bash
php artisan test
```

## 5. Status Project
- Selesai: Penyiapan fondasi Laravel, struktur direktori standar, routing awal, halaman placeholder verifikasi, konfigurasi environment, dan Vite frontend setup.
- Belum Diimplementasikan: Desain antarmuka dashboard, skema database pengunjung lengkap, fitur alur permohonan kunjungan, dan sistem autentikasi (direncanakan pada tahap implementasi berikutnya setelah audit desain).

## 6. Lokasi Direktori
`/media/Data D/kunjungan brin/brin-visit`
