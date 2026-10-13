<?php

namespace App\Support;

class Lokasi
{
    /**
     * Jarak antara dua titik koordinat dalam meter (rumus Haversine).
     */
    public static function jarakMeter($lat1, $lng1, $lat2, $lng2): float
    {
        $bumi = 6371000.0;

        $lat1 = (float) $lat1;
        $lng1 = (float) $lng1;
        $lat2 = (float) $lat2;
        $lng2 = (float) $lng2;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $bumi * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
