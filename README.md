# CMS Prima Diklat

Website informasi dan pendaftaran Bimtek & Diklat, dibangun dengan **Laravel 12**, **Filament 4** (panel admin, tema custom minimalis), **Tailwind CSS 4**, dan **Alpine.js**.

## Fitur

**Model jadwal terbuka (Jadwal Bimtek tahunan)**
- Admin mengelola tiga data induk: **Tema Bimtek**, **Jadwal Tanggal** (slot 2 hari), dan **Kota & Hotel**. Seeder `Schedule2027Seeder` sudah berisi 55 slot dan 14 hotel di 13 kota sesuai PDF jadwal 2027
- Peserta bebas memilih kombinasi tema, tanggal, dan hotel di `/daftar`, atau mengusulkan "tema lain"
- Setiap kombinasi yang dipilih otomatis membentuk satu **kelas** (menu Kelas Kegiatan). Kelas dari tema usulan berstatus draf sampai dikonfirmasi admin
- **Tarif tidak ditampilkan** di website. Admin menyampaikan biaya lewat WhatsApp, lalu memakai aksi **Tetapkan biaya** di menu Tagihan. Setelah itu invoice terbit, email tagihan terkirim, dan peserta bisa mengunggah bukti transfer
- Pendaftaran tiap slot ditutup H-3 (bisa diubah di Pengaturan)

**Website publik**
- Beranda: pilihan cepat tema/tanggal/kota, jadwal terdekat, katalog tema, ringkasan jadwal setahun, kota & hotel, alur pendaftaran 5 langkah, testimoni, mitra, berita, dan ajakan in-house training
- `/jadwal`: jadwal Januari–Desember per bulan, ditambah daftar kota & hotel
- `/tema`: katalog tema dengan filter kategori, serta halaman detail tema
- Kalender bulanan (slot jadwal ditambah kegiatan khusus), dan `/kegiatan` untuk daftar kelas yang sudah terbentuk
- Detail kegiatan: jadwal, lokasi, kuota, paket biaya, fasilitas, materi, tombol bagikan, dan schema.org Event untuk SEO
- **Surat undangan PDF otomatis** per kegiatan, untuk keperluan SPPD
- **Pendaftaran online, perorangan maupun rombongan** (hingga 20 peserta dari satu instansi dengan satu tagihan): kode pendaftaran per peserta, email konfirmasi, dan notifikasi ke admin
- **Halaman status pendaftaran**: daftar peserta, total tagihan, **unggah bukti transfer**, serta unduh **invoice** dan **kuitansi PDF** (dengan terbilang) untuk SPJ
- **Sertifikat otomatis PDF** dengan **QR verifikasi**; nomor dibuat sesuai format di Pengaturan, dan latar sertifikat bisa diganti
- **Cek & unduh sertifikat** berdasarkan kode pendaftaran atau nomor sertifikat
- Form **in-house training** dan form kontak
- Berita/artikel, galeri (dengan lightbox), FAQ, halaman statis, dan pencarian
- Tombol WhatsApp melayang, meta SEO/Open Graph, `sitemap.xml`, dan `robots.txt` dinamis (alamat sitemap lengkap, halaman status pendaftaran tidak diindeks)
- **SEO per artikel, kegiatan, dan halaman statis**: judul dan deskripsi SEO dengan penghitung karakter, pratinjau tampilan di Google, teks alternatif gambar, opsi "sembunyikan dari mesin pencari", schema.org `Article`/`NewsArticle`, dan gambar bagikan bawaan yang dibuat otomatis (`/og-image.png`)
- **Skor SEO artikel (0–100)** yang diperbarui saat menulis, berdasarkan kata kunci utama, struktur isi, dan keterbacaan, lengkap dengan saran perbaikan. Skor juga tampil sebagai kolom di daftar artikel
- **Redirect 301 otomatis**: jika slug artikel, kegiatan, tema, galeri, atau halaman diubah, URL lama dialihkan ke URL baru

**Panel admin** (`/admin`)
- Dasbor: statistik, grafik pendaftar per bulan, kegiatan terdekat, dan pendaftar terbaru
- Kegiatan: paket biaya, fasilitas, brosur PDF, tombol duplikat, serta daftar peserta per kegiatan
- Pendaftar: tab per status, ubah status massal, tombol WhatsApp ke peserta, **ekspor Excel**, terbitkan nomor sertifikat, dan pratinjau sertifikat PDF
- **Tagihan & Pembayaran**: lihat bukti transfer, **verifikasi lunas** (kuitansi dan email terbit otomatis), atau tolak bukti beserta alasannya
- Kategori, Berita, Galeri, Testimoni, Mitra, FAQ, Halaman Statis
- Pesan masuk: permintaan in-house dan pesan kontak
- Pengaturan situs: identitas, beranda, kontak, media sosial, rekening, dan penanda tangan undangan
- Hak akses: **Super Admin** (semua menu, akun admin, pengaturan), **Editor Konten** (kegiatan, jadwal, tema, konten website; tidak melihat data peserta maupun pemasukan), **Admin Pendaftaran** (pendaftar, tagihan, pesan masuk; dapat melihat kelas beserta pesertanya dan mengonfirmasi kelas dari tema usulan, tetapi tidak mengubah isi kelas). Peran akun sendiri tidak dapat diubah, dan Super Admin aktif terakhir tidak dapat diturunkan, dinonaktifkan, atau dihapus

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
| Nomor WhatsApp, kontak, rekening, dan penanda tangan undangan/invoice/kuitansi | Admin → Pengaturan Situs |
| Format nomor sertifikat, penanda tangan, latar, dan aturan wajib lunas | Admin → Pengaturan Situs → Sertifikat |
| Tampilan beranda: teks dan foto hero, warna utama, urutan dan tampil/sembunyi tiap bagian, judul, teks pengantar, jumlah item, serta angka alumni dan lama pengalaman (dikosongkan = tidak tampil) | Admin → Pengaturan Situs → Beranda |
| Legalitas lembaga dan narahubung WhatsApp di beranda | Admin → Pengaturan Situs → Legalitas / Kontak |
| Pengiriman email (SMTP) | `.env`: `MAIL_MAILER`, `MAIL_HOST`, dst. Bawaan `log` hanya menulis ke `storage/logs` |
| Zona waktu | `.env`: `APP_TIMEZONE` (bawaan `Asia/Jakarta`) |

## Pengujian

```bash
php artisan test
```

Tes memakai SQLite in-memory, sehingga database MySQL tidak tersentuh.
