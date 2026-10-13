<?php

namespace App\Support;

class Angka
{
    /**
     * Ubah nilai dari database yang bisa berformat Indonesia
     * ("1.500.000", "1,5", 100000) menjadi float murni.
     */
    public static function rupiah($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $bersih = trim((string) $value);
        $bersih = str_replace(['Rp', 'rp', ' '], '', $bersih);
        // Pemakaian umum di aplikasi ini: titik = pemisah ribuan, koma = desimal.
        $bersih = str_replace('.', '', $bersih);
        $bersih = str_replace(',', '.', $bersih);

        return (float) $bersih;
    }

    /**
     * Format angka menjadi gaya Indonesia tanpa desimal ("1.500.000").
     */
    public static function format($value): string
    {
        return number_format(self::rupiah($value), 0, ',', '.');
    }
}
