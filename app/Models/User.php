<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $keyType = 'string';
    public $incrementing = false;


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'id',
        'email',
        'password',
        'avatar',
        'jk',
        'no_hp',
        'alamat',
        'awal_masuk',
        'pendidikan',
        'subjek',
        'ttl',
        'induk',
        'dana_bos',
        'sertifikasi',
        'role'
    ];


    public function isAdmin()   { return $this->role === 'admin';   }
    public function isPegawai(){ return $this->role === 'pegawai'; }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function kasbons()
    {
        return $this->hasMany(Kasbon::class);
    }

    public function kasbonInstallments()
    {
        return $this->hasMany(KasbonInstallment::class);
    }

    public function kitirs()
    {
        return $this->hasMany(KitirGaji::class);
    }
}
