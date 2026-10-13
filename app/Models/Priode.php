<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    use HasFactory;

    protected $table = 'priode';
    protected $fillable = [
        'tahun',
        'semester',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    // Relasi: misalnya kalau kitir atau attendance terhubung
    public function kitirs()
    {
        return $this->hasMany(KitirGaji::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function kasbonInstallments()
    {
        return $this->hasMany(KasbonInstallment::class);
    }

    // Scope untuk ambil periode aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
