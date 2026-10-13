<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kasbon extends Model
{
    use HasFactory;

    protected $table = 'kasbon';
    protected $fillable = [
        'user_id', 'nominal', 'tanggal_pinjaman', 'angsuran_perbulan', 'terakhir_angsuran', 'lama_angsuran',
        'lunas',
        'angsuran_ke_1',
        'angsuran_ke_2',
        'angsuran_ke_3',
        'angsuran_ke_4',
        'angsuran_ke_5',
        'angsuran_ke_6',
        'angsuran_ke_7',
        'angsuran_ke_8',
        'angsuran_ke_9',
        'angsuran_ke_10',
        'angsuran_ke_11',
        'angsuran_ke_12',
        'angsuran_ke_13',
        'angsuran_ke_14',
        'angsuran_ke_15',
        'angsuran_ke_16',
        'angsuran_ke_17',
        'angsuran_ke_18',
        'angsuran_ke_19',
        'angsuran_ke_20',
        'angsuran_ke_21',
        'angsuran_ke_22',
        'angsuran_ke_23',
        'angsuran_ke_24',
        'angsuran_ke_25',
        'angsuran_ke_26',
        'angsuran_ke_27',
        'angsuran_ke_28',
        'angsuran_ke_29',
        'angsuran_ke_30',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function installments()
    {
        return $this->hasMany(KasbonInstallment::class);
    }

    public function scopePeriodeAktif($query) {
        $periodeId = Periode::where('aktif', true)->first()?->id;
        return $query->where('periode_id', $periodeId);
    }
}
