# CMS Prima Diklat

Website informasi dan pendaftaran Bimtek & Diklat, dibangun dengan **Laravel 12**, **Filament 4** (panel admin, tema custom minimalis), **Tailwind CSS 4**, dan **Alpine.js**.

## Fitur

**Website publik**
- Beranda: hero dengan pencarian, statistik, kegiatan terdekat, kategori, alur pendaftaran, testimoni, mitra, berita, dan ajakan in-house training
- Jadwal kegiatan dengan filter (kata kunci, kategori, bulan, jenis, kota, metode) serta arsip
- Kalender bulanan
- Detail kegiatan: jadwal, lokasi, kuota, paket biaya, fasilitas, materi, tombol bagikan, dan schema.org Event untuk SEO
- **Surat undangan PDF otomatis** per kegiatan, untuk keperluan SPPD
- **Pendaftaran online**: kode pendaftaran, email konfirmasi ke peserta, dan notifikasi ke admin
- **Cek & unduh sertifikat** berdasarkan kode pendaftaran atau nomor sertifikat
- Form **in-house training** dan form kontak
- Berita/artikel, galeri (dengan lightbox), FAQ, halaman statis, dan pencarian
- Tombol WhatsApp melayang, meta SEO/Open Graph, `sitemap.xml`, dan `robots.txt`

**Panel admin** (`/admin`)
- Dasbor: statistik, grafik pendaftar per bulan, kegiatan terdekat, dan pendaftar terbaru
- Kegiatan: paket biaya, fasilitas, brosur PDF, tombol duplikat, serta daftar peserta per kegiatan
- Pendaftar: tab per status, ubah status massal, tandai lunas, tombol WhatsApp ke peserta, **ekspor Excel**, dan unggah sertifikat
- Kategori, Berita, Galeri, Testimoni, Mitra, FAQ, Halaman Statis
- Pesan masuk: permintaan in-house dan pesan kontak
- Pengaturan situs: identitas, beranda, kontak, media sosial, rekening, dan penanda tangan undangan
- Hak akses: **Super Admin**, **Editor Konten**, **Admin Pendaftaran**

## Instalasi lokal

Kebutuhan: PHP 8.2+ (ekstensi intl, zip, gd, pdo_mysql), Composer, Node 20+, dan MySQL/MariaDB.

```bash
composer install
npm install
cp .env.example .env   # lalu isi DB_DATABASE, DB_USERNAME, DB_PASSWORD
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

- Website: http://127.0.0.1:8000
- Admin: http://127.0.0.1:8000/admin
- Akun awal dari seeder: `admin@primadiklat.test` / `password`. **Ganti segera** sebelum dipakai sungguhan.

Seeder mengisi data contoh (kategori, 12 kegiatan, pendaftar, berita, testimoni, FAQ, halaman). Kosongkan data contoh tersebut sebelum website online.

## Konfigurasi penting

| Kebutuhan | Tempat |
|---|---|
| Nomor WhatsApp, kontak, rekening, dan penanda tangan undangan | Admin → Pengaturan Situs |
| Pengiriman email (SMTP) | `.env`: `MAIL_MAILER`, `MAIL_HOST`, dst. Bawaan `log` hanya menulis ke `storage/logs` |
| Zona waktu | `.env`: `APP_TIMEZONE` (bawaan `Asia/Jakarta`) |

## Pengujian

```bash
php artisan test
```

Tes memakai SQLite in-memory, sehingga database MySQL tidak tersentuh.
