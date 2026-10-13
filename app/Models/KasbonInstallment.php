<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasbonInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'kasbon_id', 'user_id', 'kitir_id', 'bulan', 'jumlah', 'terpotong',
    ];

    public function kasbon()
    {
        return $this->belongsTo(Kasbon::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kitir()
    {
        return $this->belongsTo(KitirGaji::class);
    }

    public function scopePeriodeAktif($query) {
        $periodeId = Periode::where('aktif', true)->first()?->id;
        return $query->where('periode_id', $periodeId);
    }
}
