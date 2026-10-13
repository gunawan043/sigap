<?php

/*
|--------------------------------------------------------------------------
| Daftar Hari Libur Nasional
|--------------------------------------------------------------------------
|
| Format: 'Y-m-d' => 'Nama libur'
|
| PENTING: Daftar ini dipakai oleh tombol "Generate Libur Nasional" dan
| command terkait. Sebelum dipakai, cocokkan dengan SKB 3 Menteri terbaru
| untuk tahun berjalan. Jika ada tanggal yang berubah, cukup edit file ini
| (atau hapus/perbaiki datanya langsung dari halaman Pengaturan Absensi).
|
| Libur sekolah / libur semester TIDAK dimasukkan ke sini karena tanggalnya
| ditentukan sekolah — gunakan form "Tambah Periode Libur" dengan jenis
| "Libur Sekolah".
|
*/

return [

    2025 => [
        '2025-01-01' => 'Tahun Baru 2025',
        '2025-01-27' => 'Isra Mikraj Nabi Muhammad SAW',
        '2025-01-29' => 'Tahun Baru Imlek 2576 Kongzili',
        '2025-03-29' => 'Hari Suci Nyepi (Tahun Baru Saka 1947)',
        '2025-03-31' => 'Idul Fitri 1446 H',
        '2025-04-01' => 'Idul Fitri 1446 H',
        '2025-04-18' => 'Wafat Isa Almasih',
        '2025-05-01' => 'Hari Buruh Internasional',
        '2025-05-12' => 'Hari Raya Waisak 2569 BE',
        '2025-05-29' => 'Kenaikan Isa Almasih',
        '2025-06-01' => 'Hari Lahir Pancasila',
        '2025-06-06' => 'Idul Adha 1446 H',
        '2025-06-27' => 'Tahun Baru Islam 1447 H',
        '2025-09-05' => 'Maulid Nabi Muhammad SAW',
        '2025-08-17' => 'Hari Kemerdekaan Republik Indonesia',
        '2025-12-25' => 'Hari Raya Natal',
    ],

    2026 => [
        '2026-01-01' => 'Tahun Baru 2026',
        '2026-01-16' => 'Isra Mikraj Nabi Muhammad SAW',
        '2026-02-17' => 'Tahun Baru Imlek 2577 Kongzili',
        '2026-03-19' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)',
        '2026-03-20' => 'Idul Fitri 1447 H',
        '2026-03-21' => 'Idul Fitri 1447 H',
        '2026-04-03' => 'Wafat Isa Almasih',
        '2026-05-01' => 'Hari Buruh Internasional',
        '2026-05-14' => 'Kenaikan Isa Almasih',
        '2026-05-27' => 'Idul Adha 1447 H',
        '2026-06-01' => 'Hari Lahir Pancasila',
        '2026-06-16' => 'Tahun Baru Islam 1448 H',
        '2026-08-25' => 'Maulid Nabi Muhammad SAW',
        '2026-08-17' => 'Hari Kemerdekaan Republik Indonesia',
        '2026-12-25' => 'Hari Raya Natal',
    ],

    // Tambahkan tahun berikutnya sesuai SKB terbaru, contoh:
    // 2027 => [ '2027-01-01' => 'Tahun Baru 2027', ... ],

];
