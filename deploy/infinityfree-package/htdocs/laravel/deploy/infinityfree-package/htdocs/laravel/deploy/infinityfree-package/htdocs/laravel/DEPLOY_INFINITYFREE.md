# Deploy ke InfinityFree (Gratis)

Panduan menghosting demo **Absensi RS / HRIS Klinik Jannah** di InfinityFree agar bisa diakses publik.

Domain contoh Anda: **https://klinikjannahdemo.free.je**

---

## Ringkasan langkah

1. Siapkan paket upload di komputer lokal
2. Buat database MySQL di panel InfinityFree
3. Upload file ke folder `htdocs` via File Manager / FTP
4. Isi file `.env` dengan kredensial MySQL
5. Jalankan setup database sekali
6. Hapus file setup & uji login demo

---

## 1. Siapkan paket di komputer Anda

Buka PowerShell di folder proyek:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/prepare-infinityfree.ps1
```

Script akan:
- Install dependensi production (`composer --no-dev`)
- Build asset frontend (`npm run build`)
- Membuat folder `deploy/infinityfree-package/htdocs/`
- Membuat ZIP: `deploy/infinityfree-upload.zip`

Struktur upload:

```text
htdocs/
  index.php          ← entry point Laravel
  .htaccess
  build/             ← CSS/JS
  setup-once.php     ← hapus setelah setup
  laravel/           ← seluruh aplikasi
    app/
    bootstrap/
    vendor/
    .env             ← isi manual
    storage/         ← harus writable
```

---

## 2. Panel InfinityFree

Login ke https://dash.infinityfree.com

### a) Atur PHP 8.3

1. Klik **Manage** pada domain `klinikjannahdemo.free.je`
2. Buka **PHP Version** (atau Select PHP Version)
3. Pilih **PHP 8.3** (wajib untuk Laravel 13)
4. Aktifkan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`

### b) Buat database MySQL

1. Menu **MySQL Databases** → **Create Database**
2. Catat:
   - **MySQL Host** (mis. `sql312.infinityfree.com`)
   - **Database Name**
   - **Username**
   - **Password**

---

## 3. Upload file

### Opsi A — File Manager (lebih mudah)

1. Panel InfinityFree → **Manage** → **File Manager**
2. Buka folder **`htdocs`**
3. Hapus file default (`index.html`, dll.) jika ada
4. Upload isi ZIP `infinityfree-upload.zip` lalu extract di `htdocs`

### Opsi B — FTP (FileZilla)

| Field | Nilai |
|-------|-------|
| Host | `ftpupload.net` atau host FTP di panel |
| Username | `if0_42256442` (username akun Anda) |
| Password | Password akun InfinityFree |
| Port | 21 |

Upload seluruh isi folder `deploy/infinityfree-package/htdocs/` ke `/htdocs/`.

---

## 4. Konfigurasi `.env`

Edit file `htdocs/laravel/.env` (via File Manager):

```env
APP_NAME="HRIS Klinik Jannah"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://klinikjannahdemo.free.je
APP_KEY=base64:...   # generate di bawah

DB_HOST=sqlXXX.infinityfree.com
DB_DATABASE=if0_xxxxx_absensi
DB_USERNAME=if0_xxxxx
DB_PASSWORD=password_mysql_anda

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
QUEUE_CONNECTION=sync
FINGERPRINT_LOG_MODE=scheduled
```

### Generate APP_KEY

Di komputer lokal (setelah isi DB di .env lokal tidak perlu), jalankan:

```bash
php artisan key:generate --show
```

Salin output `base64:...` ke `APP_KEY` di server.

Atau buat file sementara `htdocs/key.php`:

```php
<?php echo 'base64:'.base64_encode(random_bytes(32));
```

Buka sekali di browser, salin ke `.env`, **hapus key.php**.

---

## 5. Setup database (sekali)

1. Edit `htdocs/setup-once.php` — ganti token:

```php
$setupToken = 'rahasia-demo-2026';
```

2. Buka di browser:

```text
https://klinikjannahdemo.free.je/setup-once.php?token=rahasia-demo-2026
```

3. Jika muncul `Migrate: OK` dan `Seed: OK`, setup berhasil.

4. **PENTING:** Hapus file `setup-once.php` dari server.

---

## 6. Permission folder storage

Pastikan folder ini **writable** (chmod 755 atau 775 via File Manager):

```text
htdocs/laravel/storage/
htdocs/laravel/storage/framework/
htdocs/laravel/storage/logs/
htdocs/laravel/bootstrap/cache/
```

Di InfinityFree File Manager: klik folder → **Permissions** → centang write untuk owner/group.

---

## 7. Uji demo

Buka: **https://klinikjannahdemo.free.je**

| Peran | Email | Password |
|-------|-------|----------|
| Super Admin | `admin@rs.local` | `password` |
| HRD | `hrd@rs.local` | `password` |
| Pegawai | `budi@rs.local` | `password` |

---

## Batasan InfinityFree (gratis)

| Fitur | Status |
|-------|--------|
| Login & dashboard | ✅ |
| CRUD pegawai, shift, cuti | ✅ |
| Scan wajah + GPS | ✅ (butuh HTTPS + izin browser) |
| Upload logo branding | ✅ |
| Mesin fingerprint ZKTeco | ❌ (butuh server daemon TCP) |
| Queue / fingerprint watch | ❌ (gunakan `QUEUE_CONNECTION=sync`) |

---

## Troubleshooting

### Error 500

- Cek PHP version = **8.3**
- Cek `APP_KEY` sudah diisi
- Cek kredensial MySQL di `.env`
- Lihat log: `htdocs/laravel/storage/logs/laravel.log`

### CSS/JS tidak muncul

- Pastikan folder `htdocs/build/` ter-upload
- `APP_URL` harus `https://klinikjannahdemo.free.je` (tanpa slash di akhir)

### Logo tidak muncul

Logo disajikan lewat `/branding/logo` — tidak perlu symlink storage.

### Session / login gagal

- `SESSION_DRIVER=database` + tabel `sessions` ada (dari migrate)
- `APP_URL` sesuai domain HTTPS
- `SESSION_SECURE_COOKIE=true`

### "Too many connections" / database lambat

InfinityFree gratis membatasi query MySQL. Untuk demo kecil biasanya cukup.

### Halaman default InfinityFree masih muncul

Pastikan file `index.php` Laravel ada di `htdocs/` (bukan hanya `index.html`).

---

## Keamanan demo publik

1. `APP_DEBUG=false` di production
2. Hapus `setup-once.php` setelah setup
3. Jangan simpan data sensitif sungguhan
4. Ganti password demo secara berkala
5. Jangan commit `.env` ke GitHub

---

## Update aplikasi nanti

1. Jalankan ulang `scripts/prepare-infinityfree.ps1`
2. Upload ulang folder `htdocs/laravel/` (atau file yang berubah)
3. Upload ulang `htdocs/build/` jika ada perubahan CSS/JS
4. Jangan timpa `.env` di server

---

## File pendukung

| File | Fungsi |
|------|--------|
| `scripts/prepare-infinityfree.ps1` | Buat paket upload + ZIP |
| `deploy/infinityfree/env.template` | Template `.env` production |
| `deploy/infinityfree/htdocs-index.php` | Entry point di `htdocs` |
| `deploy/infinityfree/setup-once.php` | Migrate + seed sekali |
