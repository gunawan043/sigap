<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArsipKitir extends Model
{
    use HasFactory;
    protected $table = 'arsip_kitir';

    protected $fillable = [
        'user_id', 'nama_ptk', 'induk', 'tmt', 'jk','f','mk','pt','gol_pt',
        'gaji_pokok','kj1','kj2','kj3','kj4','kj5','t_jab','total_gaji',
        'jam_mengajar','total_rp_jam','insentif_kehadiran','uang_lauk_pauk', 'tunjangan_kinerja', 'pot_bos',
        'pot_kasbon','pot_insidentil','total_potongan', 'total_tanpa_potongan', 'gaji_normal', 'nominal','priode_gaji', 'total_diterima'  //bisa format 'April 2025' atau '2025-04'
    ];
    

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kasbonInstallments()
    {
        return $this->hasMany(KasbonInstallment::class);
    }

    public function scopePeriodeAktif($query) {
        $periodeId = Periode::where('aktif', true)->first()?->id;
        return $query->where('periode_id', $periodeId);
    }
}
