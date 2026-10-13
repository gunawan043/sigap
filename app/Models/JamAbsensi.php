<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JamAbsensi extends Model
{
    use HasFactory;

    protected $table = 'jam_absensi';
    
    protected $fillable = [
        'hari',
        'check_in',
        'check_out',
        'status',
        'is_periode_libur',
        'tanggal_mulai',
        'tanggal_selesai',
        'keterangan_libur'
    ];

    protected $casts = [
        'is_periode_libur' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    // Scope untuk mengambil periode libur
    public function scopePeriodeLibur($query)
    {
        return $query->where('is_periode_libur', true)
                    ->orderBy('tanggal_mulai', 'desc');
    }

    // Scope untuk cek apakah tanggal termasuk libur
    public function scopeCekTanggalLibur($query, $tanggal)
    {
        return $query->where('is_periode_libur', true)
                    ->where('tanggal_mulai', '<=', $tanggal)
                    ->where('tanggal_selesai', '>=', $tanggal)
                    ->first();
    }

    // Scope untuk jadwal harian (bukan libur)
    public function scopeJadwalHarian($query)
    {
        return $query->where('is_periode_libur', false);
    }
}