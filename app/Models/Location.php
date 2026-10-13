<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $table = 'locations';

    protected $fillable = [
        'name', 'latitude', 'longitude', 'radius_meter',
    ];

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }
    
}
