<?php

namespace App\Support;

use App\Models\Absensi;
use App\Models\Attendance;
use Carbon\Carbon;

/**
 * Perhitungan insentif kehadiran — dipakai bersama oleh:
 * - KitirGajiController (penggajian)
 * - halaman Rekap Absensi
 * - command sigap:cek-insentif
 *
 * Aturan (sesuai tabel `absensi`):
 * - Insentif mulai dari nilai penuh (tunjangan_kehadiran).
 * - Akumulasi menit telat dibagi (toleransi/4), dibulatkan ke bawah,
 *   dikali pot_terlambat.
 * - Lupa absen pulang dan hari alfa masing-masing menambah `pot_absen`
 *   menit ke akumulasi.
 * - Hasil akhir tidak pernah negatif.
 */
class InsentifKehadiran
{
    public static function hitung(
        $userId,
        ?Carbon $awalMasuk,
        int $bulan,
        int $tahun,
        ?Absensi $absensi,
        array $hariLibur = []
    ): array {
        $tunjangan = Angka::rupiah($absensi->tunjangan_kehadiran ?? 0);
        $toleransi = Angka::rupiah($absensi->toleransi_keterlambatan ?? 0);
        $potTerlambat = Angka::rupiah($absensi->pot_terlambat ?? 0);
        $potAbsenMenit = Angka::rupiah($absensi->pot_absen ?? 0);

        $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $akhirBulan = $awalBulan->copy()->endOfMonth();

        // Untuk bulan berjalan, hanya hitung sampai hari ini.
        $batas = $awalBulan->isCurrentMonth() ? Carbon::today() : $akhirBulan->copy();
        if ($batas->gt($akhirBulan)) {
            $batas = $akhirBulan->copy();
        }

        $dataAbsensi = Attendance::where('user_id', $userId)
            ->whereBetween('date', [$awalBulan->toDateString(), $akhirBulan->toDateString()])
            ->get()
            ->keyBy(function ($row) {
                return (int) Carbon::parse($row->date)->day;
            });

        $totalMenitTelat = 0;
        $jumlahLupaPulang = 0;
        $jumlahAlpha = 0;

        for ($tanggal = $awalBulan->copy(); $tanggal->lte($batas); $tanggal->addDay()) {
            // Minggu libur
            if ($tanggal->dayOfWeek === Carbon::SUNDAY) {
                continue;
            }

            // Belum mulai bekerja
            if ($awalMasuk && $tanggal->lt($awalMasuk->copy()->startOfDay())) {
                continue;
            }

            // Periode libur (nasional / sekolah)
            if (in_array($tanggal->toDateString(), $hariLibur, true)) {
                continue;
            }

            $absen = $dataAbsensi->get($tanggal->day);

            if (!$absen) {
                // Tidak ada data absensi sama sekali = alfa
                $jumlahAlpha++;
                continue;
            }

            $totalMenitTelat += (int) ($absen->selisih_menit ?? 0);

            if (!empty($absen->check_in_time) && empty($absen->check_out_time)) {
                $jumlahLupaPulang++;
            }
        }

        // Lupa absen pulang & alfa dihitung sebagai tambahan menit
        // sesuai pengaturan "pot_absen" (dalam menit).
        $akumulasiMenit = $totalMenitTelat + (($jumlahLupaPulang + $jumlahAlpha) * $potAbsenMenit);

        $potongan = 0;
        $satuan = $toleransi > 0 ? ($toleransi / 4) : 0;

        if ($satuan > 0 && $akumulasiMenit > 0) {
            $potongan = floor($akumulasiMenit / $satuan) * $potTerlambat;
        }

        return [
            'insentif' => max(0, $tunjangan - $potongan),
            'menit_telat' => $totalMenitTelat,
            'lupa_pulang' => $jumlahLupaPulang,
            'alpha' => $jumlahAlpha,
            'potongan' => $potongan,
            'tunjangan' => $tunjangan,
        ];
    }
}
