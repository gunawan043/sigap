<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersentaseGaji extends Model
{
    use HasFactory;

    protected $table = 'persentase_gaji';

    protected $fillable = [
        'persentase_golongan_1', 'persentase_golongan_2', 'persentase_golongan_3', 'persentase_golongan_4'
    ];
}
