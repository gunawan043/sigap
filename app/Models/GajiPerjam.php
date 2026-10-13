<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GajiPerjam extends Model
{
    use HasFactory;
    protected $table = 'gaji_perjam';

    protected $fillable = [
        'rp_gaji', 'lauk_pauk', 'nominal_golongan_3', 'nominal_golongan_4'
    ];
}
