<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id', 'location_id', 'latitude', 'longitude', 'keterangan', 'jam',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function scopePeriodeAktif($query) {
        $periodeId = Periode::where('aktif', true)->first()?->id;
        return $query->where('periode_id', $periodeId);
    }
}
