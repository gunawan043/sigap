<?php

namespace App\Support;

use App\Models\JamAbsensi;
use Carbon\Carbon;

/**
 * Sumber tunggal untuk logika hari libur:
 * - libur nasional & libur sekolah (periode libur di tabel jam_absensi)
 * - pengaturan khusus per tanggal (kolom `hari` berisi tanggal Y-m-d)
 * - jadwal harian biasa
 */
class HariLibur
{
    /**
     * Daftar tanggal libur dalam rentang tertentu (dipakai perhitungan gaji).
     *
     * @return string[] daftar tanggal format Y-m-d
     */
    public static function daftar($mulai, $selesai): array
    {
        $mulai = Carbon::parse($mulai)->toDateString();
        $selesai = Carbon::parse($selesai)->toDateString();

        try {
            $periode = JamAbsensi::where('is_periode_libur', true)
                ->whereNotNull('tanggal_mulai')
                ->whereNotNull('tanggal_selesai')
                ->where(function ($query) use ($mulai, $selesai) {
                    $query->whereBetween('tanggal_mulai', [$mulai, $selesai])
                        ->orWhereBetween('tanggal_selesai', [$mulai, $selesai])
                        ->orWhere(function ($q) use ($mulai, $selesai) {
                            $q->where('tanggal_mulai', '<=', $mulai)
                                ->where('tanggal_selesai', '>=', $selesai);
                        });
                })
                ->get();

            $daftar = [];

            foreach ($periode as $p) {
                $tanggal = Carbon::parse($p->tanggal_mulai);
                $akhir = Carbon::parse($p->tanggal_selesai);

                while ($tanggal->lte($akhir)) {
                    $daftar[] = $tanggal->toDateString();
                    $tanggal->addDay();
                }
            }

            return array_values(array_unique($daftar));
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Status satu tanggal.
     *
     * @return array{libur: bool, keterangan: ?string, check_in: ?string, check_out: ?string}
     */
    public static function status($tanggal): array
    {
        $tanggal = Carbon::parse($tanggal)->toDateString();

        try {
            // 1. Pengaturan khusus per tanggal (paling tinggi prioritasnya).
            //    Kolom `hari` berisi tanggal Y-m-d, mis. "2026-10-15".
            $khusus = JamAbsensi::where('hari', $tanggal)->first();

            if ($khusus) {
                $nonaktif = strtolower((string) $khusus->status) === 'nonactive'
                    || (bool) $khusus->is_periode_libur;

                return [
                    'libur' => $nonaktif,
                    'keterangan' => $khusus->keterangan_libur,
                    'check_in' => $nonaktif ? null : $khusus->check_in,
                    'check_out' => $nonaktif ? null : $khusus->check_out,
                ];
            }

            // 2. Periode libur (nasional / sekolah).
            $periode = JamAbsensi::where('is_periode_libur', true)
                ->whereNotNull('tanggal_mulai')
                ->whereNotNull('tanggal_selesai')
                ->where('tanggal_mulai', '<=', $tanggal)
                ->where('tanggal_selesai', '>=', $tanggal)
                ->first();

            if ($periode) {
                return [
                    'libur' => true,
                    'keterangan' => $periode->keterangan_libur,
                    'check_in' => null,
                    'check_out' => null,
                ];
            }

            // 3. Jadwal harian biasa.
            $hari = Carbon::parse($tanggal)->locale('id')->dayName;

            $jadwal = JamAbsensi::whereIn('hari', [$hari, strtolower($hari), ucfirst(strtolower($hari))])
                ->where('is_periode_libur', false)
                ->whereIn('status', ['Active', 'active'])
                ->first();

            if (!$jadwal) {
                return [
                    'libur' => true,
                    'keterangan' => 'Tidak ada jadwal kerja pada tanggal ini',
                    'check_in' => null,
                    'check_out' => null,
                ];
            }

            return [
                'libur' => false,
                'keterangan' => null,
                'check_in' => $jadwal->check_in,
                'check_out' => $jadwal->check_out,
            ];
        } catch (\Throwable $e) {
            return [
                'libur' => false,
                'keterangan' => null,
                'check_in' => null,
                'check_out' => null,
            ];
        }
    }

    /**
     * Ambil keterangan libur saja (null kalau bukan libur).
     */
    public static function keterangan($tanggal): ?string
    {
        $status = self::status($tanggal);

        return $status['libur'] ? ($status['keterangan'] ?? 'Libur') : null;
    }
}
