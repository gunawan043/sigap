<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $table = 'attendances';
    protected $fillable = [
        'user_id', 'date', 'check_in_time', 'check_out_time', 'status', 'is_late', 'selisih_menit', 'jam_masuk', 'jam_pulang'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function scopePeriodeAktif($query) {
        $periodeId = Periode::where('aktif', true)->first()?->id;
        return $query->where('periode_id', $periodeId);
    }
}
