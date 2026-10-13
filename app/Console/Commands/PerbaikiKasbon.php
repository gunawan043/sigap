<?php

namespace App\Console\Commands;

use App\Models\Kasbon;
use App\Support\Angka;
use Illuminate\Console\Command;

class PerbaikiKasbon extends Command
{
    protected $signature = 'sigap:perbaiki-kasbon {--execute : Terapkan perbaikan (tanpa opsi ini hanya menampilkan)}';

    protected $description = 'Menyelaraskan status lunas kasbon dengan total angsuran yang sudah tercatat';

    public function handle()
    {
        $kasbons = Kasbon::orderBy('id')->get();
        $baris = [];
        $perluPerbaikan = 0;

        foreach ($kasbons as $kasbon) {
            $nominal = Angka::rupiah($kasbon->nominal);
            $sudahDibayar = 0;

            for ($i = 1; $i <= 30; $i++) {
                $sudahDibayar += Angka::rupiah($kasbon->{'angsuran_ke_' . $i});
            }

            $sisa = max(0, $nominal - $sudahDibayar);
            $seharusnyaLunas = $sisa <= 0;
            $lunasSekarang = (bool) $kasbon->lunas;

            $berubah = $seharusnyaLunas !== $lunasSekarang;

            if ($berubah) {
                $perluPerbaikan++;
            }

            $baris[] = [
                $kasbon->id,
                $kasbon->user_id,
                Angka::format($nominal),
                Angka::format($sudahDibayar),
                Angka::format($sisa),
                $lunasSekarang ? 'lunas' : 'belum',
                $seharusnyaLunas ? 'lunas' : 'belum',
                $berubah ? 'PERBAIKI' : '-',
            ];

            if ($this->option('execute') && $berubah) {
                $kasbon->lunas = $seharusnyaLunas ? 1 : 0;
                $kasbon->save();
            }
        }

        if (empty($baris)) {
            $this->info('Tidak ada data kasbon.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'User', 'Nominal', 'Sudah Dibayar', 'Sisa', 'Status Kini', 'Seharusnya', 'Aksi'],
            $baris
        );

        if ($this->option('execute')) {
            $this->info("Selesai. {$perluPerbaikan} data kasbon diperbaiki.");
        } else {
            $this->warn("Ditemukan {$perluPerbaikan} data yang tidak konsisten.");
            $this->line('Jalankan ulang dengan opsi --execute untuk menerapkan perbaikan.');
        }

        return self::SUCCESS;
    }
}
