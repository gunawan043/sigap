<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkalaGajiGuru extends Model
{
    use HasFactory;
    protected $table = 'skala_gaji_guru';

    protected $fillable = [
        'masa_kerja', 'level', 'golongan_1', 'golongan_2', 'golongan_3', 'golongan_4', 'golongan_5'
    ];
}
