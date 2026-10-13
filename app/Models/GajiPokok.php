<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GajiPokok extends Model
{
    use HasFactory;
    protected $table = 'gaji_pokok';

    protected $fillable = [
        'nominal_golongan_1', 'nominal_golongan_2', 'nominal_golongan_3', 'nominal_golongan_4'
    ];
}
