<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Masakerja extends Model
{
    use HasFactory;
    protected $table = 'masakerja';
    protected $fillable = [
        'masa_kerja', 'level'
    ];
}
