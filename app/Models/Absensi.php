<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';
    protected $fillable = [
        'tunjangan_kehadiran', 'toleransi_keterlambatan', 'status', 'jam_masuk', 'jam_pulang', 'catatan', 'pot_terlambat', 'pot_disiplin', 'pot_absen', 'tunjangan_harian'
    ];
}
