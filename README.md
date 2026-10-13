# SIGAP — Sistem Informasi Gaji dan Absensi Pegawai

Aplikasi berbasis Laravel untuk mengelola absensi pegawai, penggajian (kitir gaji),
kasbon, serta tunjangan/insentif kehadiran dan kedisiplinan.

## Fitur utama

- **Absensi** — absen masuk/pulang via web dan API mobile, validasi radius lokasi,
  jadwal kerja per hari, pengaturan tanggal khusus, serta pengelolaan libur
  nasional dan libur sekolah.
- **Kitir Gaji** — perhitungan gaji pokok (skala gaji), tunjangan jabatan,
  jam mengajar, insentif kehadiran, insentif kedisiplinan, potongan BOS,
  dan potongan insidentil.
- **Kasbon** — pengajuan kasbon, potongan angsuran otomatis per periode gaji
  (berhenti saat lunas), dan ledger `kasbon_installments` agar tidak
  terpotong dua kali.
- **Arsip Gaji** — publish gaji per periode, slip gaji pegawai, export CSV/Excel.
- **Peran (role)** — `admin` (akses penuh), `operator` (hanya pengaturan absensi),
  dan `pegawai`.

## Persyaratan

- PHP >= 8.2 dengan ekstensi standar Laravel
- MySQL / MariaDB
- Composer
- Node.js & NPM (untuk build aset frontend)

## Instalasi

```bash
composer install
cp .env.example .env
php artisan key:generate

# sesuaikan kredensial database pada .env, lalu:
php artisan migrate
```

Build aset (opsional bila folder `public/build` sudah tersedia):

```bash
npm install
npm run build
```

## Akun pertama

Akun admin tidak lagi dibuat otomatis oleh migrasi (agar kredensial tidak
tersimpan di repository). Buat manual:

```bash
# Admin penuh
php artisan sigap:buat-admin admin@sekolah.test --name="Administrator"

# Operator sekolah (hanya boleh mengatur jam kerja & hari libur)
php artisan sigap:buat-operator operator@sekolah.test --name="Operator Sekolah"
```

Password diminta secara interaktif saat command dijalankan.

## Command lain

```bash
# Menyelaraskan status lunas kasbon dengan total angsuran (dry-run)
php artisan sigap:perbaiki-kasbon
php artisan sigap:perbaiki-kasbon --execute

# Reminder absensi (dijadwalkan otomatis setiap 5 menit)
php artisan reminder:attendance
```

## Konfigurasi hari libur

Daftar libur nasional ada di `config/hari_libur.php` (format `'Y-m-d' => 'Nama libur'`).
Setelah ada SKB terbaru, sesuaikan isinya lalu jalankan tombol
**Generate Libur Nasional** di halaman Pengaturan Absensi.

## Keamanan

File berikut **tidak boleh** ikut ter-commit (sudah masuk `.gitignore`):

- `.env` — kredensial database, mail, dan kunci aplikasi
- `storage/app/firebase/*.json` — service account Firebase (private key)
- `storage/logs/*` — log produksi
- foto hasil unggahan pengguna di `public/images/`

## Template UI

Tampilan menggunakan template **Velzon** (Envato). Pastikan lisensi terpisah
sesuai ketentuan pembuat template.
