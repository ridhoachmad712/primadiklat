# Deploy ke Hostinger (primadiklat.com)

Aplikasi berjalan di shared hosting Hostinger (hPanel) dengan SSH.

| Hal | Nilai |
|---|---|
| Folder aplikasi | `~/primadiklat` (clone dari GitHub, di luar folder web) |
| Folder web | `~/domains/primadiklat.com/public_html` → symlink ke `~/primadiklat/public` |
| PHP | 8.3 (web) / 8.4 (CLI), Composer tersedia, **tidak ada Node.js** |
| SSL | Let's Encrypt otomatis dari Hostinger; HTTP → HTTPS dan `www` → domain utama sudah dialihkan |
| Database | MariaDB, kredensial hanya di `~/primadiklat/.env` |

## Memperbarui situs

Di komputer lokal: `npm run build` lalu commit (folder `public/build` ikut di-commit karena server tidak punya Node.js), kemudian push.

Di server:

```bash
cd ~/primadiklat && git pull && composer install --no-dev -o && php artisan migrate --force && php artisan optimize
```

## Catatan khusus Hostinger

- **`php artisan storage:link` gagal** karena fungsi `exec()`/`symlink()` dinonaktifkan. Buat link secara manual (cukup sekali):

  ```bash
  cd ~/primadiklat/public && ln -s ../storage/app/public storage
  ```

  Tanpa link ini, foto yang diunggah di admin tidak tampil di website.
- Situs berada di belakang CDN Hostinger; header proxy sudah dipercaya (`trustProxies`) dan URL dipaksa `https://` di produksi.
- Nilai rahasia di `.env` diisi tanpa tampil di layar: `php artisan app:env-secret DB_PASSWORD` (atau `MAIL_PASSWORD`), lalu `php artisan config:cache`.
- Akun Super Admin baru: `php artisan app:create-admin`.
- Data awal produksi (tanpa data contoh): `php artisan db:seed --class=ProductionSeeder --force`.
