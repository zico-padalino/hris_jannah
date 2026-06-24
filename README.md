# Sistem Absensi Rumah Sakit

Aplikasi absensi pegawai rumah sakit berbasis **Laravel 13** dengan dukungan **fingerprint ZKTeco (TCP)**, **scan wajah + GPS**, **jam kerja/shift**, **keterlambatan**, **cuti/izin**, dan **payroll** dengan potongan gaji otomatis.

---

## Daftar Isi

1. [Fitur Utama](#fitur-utama)
2. [Persyaratan Sistem](#persyaratan-sistem)
3. [Instalasi](#instalasi)
4. [Menjalankan Aplikasi](#menjalankan-aplikasi)
5. [Konfigurasi (.env)](#konfigurasi-env)
6. [Akun Demo](#akun-demo)
7. [Panduan Modul](#panduan-modul)
8. [Logika Absensi](#logika-absensi)
9. [Mesin Fingerprint (ZKTeco TCP)](#mesin-fingerprint-zkteco-tcp)
10. [Scan Wajah & GPS](#scan-wajah--gps)
11. [Payroll & Potongan Gaji](#payroll--potongan-gaji)
12. [Peran & Hak Akses](#peran--hak-akses)
13. [Perintah Artisan](#perintah-artisan)
14. [Query SQL](#query-sql)
15. [Struktur Folder Penting](#struktur-folder-penting)
16. [Troubleshooting](#troubleshooting)

---

## Fitur Utama

| Modul | Keterangan |
|-------|------------|
| **Absensi Fingerprint** | Tarik log dari mesin ZKTeco via TCP (port 4370), proses otomatis ke database |
| **Absensi Wajah** | Verifikasi wajah (face-api.js) + geofencing GPS |
| **Jam Kerja (Shift)** | Template shift, hari kerja, toleransi keterlambatan, sync ke mesin fingerprint |
| **Jam Kerja Pegawai** | Penugasan shift per pegawai (bulk/single) |
| **Keterlambatan** | Deteksi otomatis saat masuk melewati jadwal + toleransi |
| **Masuk/Pulang Bergantian** | Tap/scan 1 = masuk, 2 = pulang, reset setiap hari baru |
| **Cuti/Izin** | Pengajuan, persetujuan HRD, upload bukti, sinkron ke riwayat absensi |
| **Payroll** | Generate gaji bulanan dengan potongan terlambat & invalid |
| **Detail Potongan** | Rincian per absensi terlambat/invalid per pegawai per periode |
| **Role & Permission** | Matriks hak akses per peran (Super Admin, HRD, Admin Cabang, Pegawai) |
| **Laporan** | Ringkasan absensi per cabang |

---

## Persyaratan Sistem

- **PHP** ≥ 8.3 dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `sockets` (wajib untuk fingerprint TCP)
- **Composer** 2.x
- **Node.js** ≥ 18 & **npm**
- **MySQL** 8.x / MariaDB
- **Mesin fingerprint ZKTeco** (opsional, untuk absensi fingerprint)
- Browser modern dengan kamera & GPS (untuk scan wajah)

---

## Instalasi

### 1. Clone & masuk ke folder proyek

```bash
git clone https://github.com/zico-padalino/hris_jannah.git
cd hris_jannah
```

### 2. Install dependensi & setup otomatis

```bash
composer setup
```

Perintah di atas akan menjalankan: `composer install`, copy `.env`, `key:generate`, `migrate`, `npm install`, dan `npm run build`.

### 3. Setup manual (alternatif)

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Buat database MySQL bernama `absensi_rs`, lalu sesuaikan kredensial di `.env`:

```env
DB_DATABASE=absensi_rs
DB_USERNAME=root
DB_PASSWORD=
```

Jalankan migrasi & seeder:

```bash
php artisan migrate
php artisan db:seed
npm install
npm run build
php artisan storage:link
```

---

## Menjalankan Aplikasi

### Mode development lengkap (server + fingerprint + queue + vite)

```bash
composer dev
```

### Mode server + fingerprint saja (disarankan untuk produksi lokal)

```bash
composer run serve:tcp
```

Setara dengan menjalankan:

```bash
php artisan serve --host=0.0.0.0 --port=8000
php artisan fingerprint:watch
```

Buka browser: **http://localhost:8000**

### Demo publik di InfinityFree

Hosting gratis dengan domain seperti `klinikjannahdemo.free.je`:

1. Jalankan `powershell -ExecutionPolicy Bypass -File scripts/prepare-infinityfree.ps1`
2. Ikuti panduan **[DEPLOY_INFINITYFREE.md](./DEPLOY_INFINITYFREE.md)**

---

## Konfigurasi (.env)

| Variabel | Default | Keterangan |
|----------|---------|------------|
| `APP_TIMEZONE` | `Asia/Jakarta` | Zona waktu absensi |
| `DB_*` | — | Koneksi MySQL |
| `FINGERPRINT_LOG_MODE` | `tcp` | Mode log fingerprint (`tcp`, `scheduled`, `hybrid`, `adms`) |
| `FINGERPRINT_AUTO_PULL_SECONDS` | `30` | Interval tarik log TCP (detik) |

Pengaturan tambahan (disimpan di database, menu **Pengaturan Sistem**):

- Ambang kecocokan wajah (0.1–1)
- Buffer radius lokasi GPS (meter)
- Potongan gaji per absensi terlambat/invalid (Rp)

---

## Akun Demo

Setelah `php artisan db:seed`:

| Peran | Email | Password |
|-------|-------|----------|
| Super Admin | `admin@rs.local` | `password` |
| HRD Pusat | `hrd@rs.local` | `password` |
| Admin Cabang Serang | `admin.serang@rs.local` | `password` |
| Pegawai (Dr. Budi) | `budi@rs.local` | `password` |

> Pegawai demo sudah memiliki data wajah demo dan shift pagi (07:00–15:00).

---

## Panduan Modul

### Alur setup awal (HRD / Admin)

1. **Cabang RS** — buat cabang dan titik lokasi GPS (radius geofence)
2. **Departemen** — kelompok pegawai per cabang
3. **Template Jam Kerja** — buat shift (jam masuk/pulang, hari kerja, toleransi)
4. **Pegawai** — daftarkan pegawai, PIN fingerprint, gaji pokok
5. **Jam Kerja Pegawai** — assign shift ke pegawai
6. **Mesin Fingerprint** — daftarkan IP mesin, kaitkan ke cabang, sync pegawai & shift
7. **Pengaturan Sistem** — atur ambang wajah, buffer GPS, nominal potongan gaji

### Absensi harian (Pegawai)

- **Fingerprint** — tap di mesin (otomatis masuk/pulang bergantian)
- **Scan Wajah** — menu Scan Absensi, izinkan kamera & GPS

### Cuti/Izin

1. Pegawai ajukan cuti + upload bukti (jika ada)
2. HRD/Admin setujui/tolak di menu Persetujuan Cuti
3. Cuti disetujui otomatis tercatat di riwayat absensi

### Payroll bulanan

1. Menu **Payroll** → pilih cabang, bulan, tahun → **Buat Payroll**
2. Sistem hitung potongan dari absensi terlambat & invalid
3. Klik **Detail potongan** untuk melihat rincian per pegawai
4. **Finalisasi** setelah dicek

---

## Logika Absensi

### Masuk / Pulang bergantian

Per pegawai, **per tanggal kalender**:

| Urutan tap/scan | Tipe |
|-----------------|------|
| 1 | Masuk |
| 2 | Pulang |
| 3 | Masuk |
| 4 | Pulang |
| … | bergantian |

Hari baru → **reset** (tap pertama = Masuk lagi).

Berlaku untuk fingerprint dan scan wajah. Cuti/izin tidak ikut dihitung dalam urutan bergantian.

### Keterlambatan

Keterlambatan dihitung saat **absensi masuk (check_in)** jika:

```
waktu absen > jam masuk jadwal + toleransi (menit)
```

Contoh: shift pagi 07:00, toleransi 15 menit → masuk 07:16 ke atas = **Terlambat**.

Status absensi:

| Status | Badge | Potongan gaji |
|--------|-------|---------------|
| Tepat waktu | Hijau | Tidak |
| Terlambat | Oranye | Ya |
| Invalid wajah/lokasi | Merah | Ya |

---

## Mesin Fingerprint (ZKTeco TCP)

### Prasyarat

1. Ekstensi PHP `sockets` aktif
2. Mesin dan server dalam **jaringan yang sama** (LAN)
3. Mesin dikonfigurasi dengan **IP statis**

### Langkah setup

1. Buka **Mesin Fingerprint** → tambah/edit mesin
2. Isi **IP Address** mesin (contoh: `192.168.1.201`)
3. Kaitkan mesin ke **cabang** yang sesuai
4. Setiap pegawai harus punya **PIN fingerprint** yang sama dengan mesin
5. Klik **Sync Semua** (shift + pegawai) atau sync terpisah
6. Jalankan watcher:

```bash
php artisan fingerprint:watch
```

### Mode log (`FINGERPRINT_LOG_MODE`)

| Mode | Keterangan |
|------|------------|
| `tcp` | Tarik log via TCP port 4370 (**default**, disarankan) |
| `scheduled` | Sama seperti tcp, via `schedule:work` |
| `hybrid` | Cadangan TCP + ADMS push |
| `adms` | Hanya terima push dari mesin cloud |

### Tarik log manual

Di halaman mesin fingerprint, gunakan tombol **Tarik Log** atau:

```bash
php artisan fingerprint:pull
```

---

## Scan Wajah & GPS

Scan wajah memerlukan **HTTPS** atau **localhost** agar browser mengizinkan kamera & GPS.

### Opsi A — Akses via localhost (di komputer server)

```
http://localhost:8000
```

### Opsi B — HTTPS proxy (akses dari HP/tablet di jaringan)

Terminal 1:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Terminal 2:

```bash
npm run serve:https
```

Buka: **https://[IP-server]:8443** (terima peringatan sertifikat self-signed).

### Sebelum scan

1. Daftarkan wajah pegawai: **Pegawai → Daftarkan Wajah**
2. Pastikan lokasi GPS berada dalam radius cabang (menu **Cabang RS → Lokasi**)

---

## Payroll & Potongan Gaji

### Rumus potongan

```
Total potongan = (jumlah terlambat + jumlah invalid) × nominal per absensi
Gaji net = gaji pokok + tunjangan - total potongan
```

Nominal per absensi diatur di **Pengaturan Sistem** (default Rp 50.000).

### Melihat detail

1. **Payroll** → buka periode → klik **Detail potongan** pada baris pegawai
2. Tampilan berisi: tanggal, jam, keterlambatan (menit), status, sumber, nominal potongan per baris

Pegawai juga bisa melihat detail potongan sendiri di menu **Gaji Saya**.

---

## Peran & Hak Akses

| Peran | Cakupan |
|-------|---------|
| **Super Admin** | Semua cabang, semua menu |
| **HRD** | Kelola pegawai, payroll, cuti, laporan (semua cabang) |
| **Admin Cabang** | Kelola data cabang sendiri |
| **Pegawai** | Scan absensi, riwayat sendiri, ajukan cuti, lihat gaji sendiri |

Matriks permission dapat diedit di menu **Peran & Hak Akses**.

---

## Perintah Artisan

| Perintah | Fungsi |
|----------|--------|
| `php artisan migrate` | Jalankan migrasi database |
| `php artisan db:seed` | Isi data demo |
| `php artisan fingerprint:watch` | Loop tarik log TCP dari semua mesin aktif |
| `php artisan fingerprint:pull` | Tarik log sekali (semua mesin) |
| `php artisan storage:link` | Buat symlink storage publik (foto absensi/wajah) |
| `php artisan serve --host=0.0.0.0 --port=8000` | Jalankan server development |

---

## Query SQL

File query siap pakai ada di folder `database/sql/`:

| File | Isi |
|------|-----|
| `jam_kerja_dan_keterlambatan.sql` | Absensi + jadwal + evaluasi keterlambatan + potongan gaji |
| `absensi_rs_full.sql` | Kumpulan query referensi (dashboard, payroll, dll.) |

Jalankan di phpMyAdmin / MySQL client dengan database `absensi_rs`.

---

## Struktur Folder Penting

```
app/
├── Enums/              # Status absensi, peran, permission
├── Http/Controllers/   # Controller web & API
├── Models/             # Model Eloquent
└── Services/           # Logika bisnis utama
    ├── AttendanceService.php          # Scan wajah
    ├── FingerprintAttendanceService.php  # Proses log fingerprint
    ├── ShiftScheduleService.php     # Shift, keterlambatan, masuk/pulang
    └── PayrollService.php           # Generate gaji & potongan

config/attendance.php   # Konfigurasi absensi & fingerprint
database/
├── migrations/         # Skema database
├── seeders/            # Data demo
└── sql/                # Query SQL referensi

resources/views/        # Tampilan Blade (Tailwind CSS)
routes/web.php          # Route aplikasi web
```

---

## Troubleshooting

### Fingerprint tidak masuk ke sistem

- Pastikan `php artisan fingerprint:watch` berjalan
- Cek ekstensi `sockets`: `php -m | findstr sockets`
- Pastikan IP mesin benar dan server bisa ping ke mesin
- Pastikan pegawai punya PIN fingerprint & cabang mesin = cabang pegawai
- Lihat log di halaman mesin fingerprint (status proses log)

### Scan wajah gagal / kamera tidak muncul

- Gunakan HTTPS (`npm run serve:https`) atau `localhost`
- Izinkan akses kamera & lokasi di browser
- Pastikan wajah sudah didaftarkan

### Absensi terlambat tapi status "Tepat waktu"

- Pastikan pegawai sudah di-assign **Jam Kerja**
- Pastikan migrasi `is_late` sudah dijalankan:
  ```bash
  php artisan migrate --path=database/migrations/2026_06_21_000004_add_late_fields_to_attendances_table.php
  ```

### Kolom `is_late` tidak ditemukan (SQL error)

Jalankan migrasi yang belum di-apply (lihat perintah di atas).

### Potongan gaji tidak muncul

- Atur nominal di **Pengaturan Sistem**
- Generate ulang payroll (status draft): tombol **Generate Ulang**

---

## Lisensi

Proyek ini menggunakan [Laravel Framework](https://laravel.com) yang dilisensikan under [MIT license](https://opensource.org/licenses/MIT).
