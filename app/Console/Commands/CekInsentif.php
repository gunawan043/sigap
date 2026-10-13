<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\ArsipKitir;
use App\Models\User;
use App\Support\Angka;
use App\Support\HariLibur;
use App\Support\InsentifKehadiran;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CekInsentif extends Command
{
    protected $signature = 'sigap:cek-insentif
                            {periode : contoh "Sep, 2026"}
                            {--hanya-beda : tampilkan hanya yang berbeda dengan arsip}';

    protected $description = 'Membandingkan insentif kehadiran yang dihitung ulang dengan nilai arsip gaji';

    public function handle()
    {
        try {
            $periode = Carbon::createFromFormat('M, Y', $this->argument('periode'))->startOfMonth();
        } catch (\Throwable $e) {
            $this->error('Format periode salah. Contoh yang benar: "Sep, 2026"');

            return self::FAILURE;
        }

        $bulan = (int) $periode->month;
        $tahun = (int) $periode->year;
        $label = $periode->format('M, Y');

        $absensi = Absensi::first();
        $hariLibur = HariLibur::daftar(
            $periode->copy()->startOfMonth()->toDateString(),
            $periode->copy()->endOfMonth()->toDateString()
        );

        $arsip = ArsipKitir::where('priode_gaji', $label)->get()->keyBy(function ($item) {
            return (string) $item->user_id;
        });

        if ($arsip->isEmpty()) {
            $this->warn('Belum ada arsip gaji untuk periode ' . $label . '.');
        }

        $baris = [];
        $jumlahBeda = 0;
        $jumlahDiperiksa = 0;

        foreach (User::where('role', 'pegawai')->orderBy('name')->get() as $user) {
            // Yayasan/TPQ tidak memakai absensi, jadi tidak dihitung insentif kehadiran.
            if (in_array($user->induk, ['Yayasan', 'TPQ'], true)) {
                continue;
            }

            $awalMasuk = null;
            try {
                $awalMasuk = Carbon::createFromFormat('d M, Y', $user->awal_masuk);
            } catch (\Throwable $e) {
                // biarkan null
            }

            $hitung = InsentifKehadiran::hitung($user->id, $awalMasuk, $bulan, $tahun, $absensi, $hariLibur);

            $nilaiArsip = $arsip->get((string) $user->id)->insentif_kehadiran ?? null;
            $angkaArsip = $nilaiArsip === null ? null : Angka::rupiah($nilaiArsip);

            $beda = ($angkaArsip === null) || ((float) $angkaArsip !== (float) $hitung['insentif']);

            $jumlahDiperiksa++;
            if ($beda) {
                $jumlahBeda++;
            }

            if ($this->option('hanya-beda') && !$beda) {
                continue;
            }

            $baris[] = [
                $user->name,
                $user->induk,
                $hitung['menit_telat'],
                $hitung['lupa_pulang'],
                $hitung['alpha'],
                Angka::format($hitung['insentif']),
                $angkaArsip === null ? '(belum ada)' : Angka::format($angkaArsip),
                $beda ? 'BEDA' : 'sama',
            ];
        }

        $this->table(
            ['Nama', 'Induk', 'Telat (menit)', 'Lupa Pulang', 'Alfa', 'Insentif Hitung', 'Insentif Arsip', 'Status'],
            $baris
        );

        $this->line('Periode: ' . $label);
        $this->line('Diperiksa: ' . $jumlahDiperiksa . ' pegawai, berbeda: ' . $jumlahBeda);

        if ($jumlahBeda > 0) {
            $this->warn('Ada perbedaan. Publish ulang periode ini (Kitir Gaji Bulanan → Publish Gaji) agar arsip memakai perhitungan baru.');
        }

        return self::SUCCESS;
    }
}
