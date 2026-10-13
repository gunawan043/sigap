<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class BuatOperatorSekolah extends Command
{
    protected $signature = 'sigap:buat-operator
                            {email : Email login untuk operator sekolah}
                            {--name=Operator Sekolah : Nama tampilan operator}
                            {--password= : Password (disarankan dikosongkan agar diminta interaktif)}';

    protected $description = 'Membuat akun admin operator sekolah yang hanya bisa mengatur jam kerja & hari libur';

    public function handle()
    {
        $email = (string) $this->argument('email');
        $nama = (string) $this->option('name');

        $password = (string) ($this->option('password') ?: $this->secret('Password untuk operator (minimal 8 karakter)'));

        $validator = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $id = (string) (((int) DB::table('users')->max('id')) + 1);

        DB::table('users')->insert([
            'id' => $id,
            'name' => $nama,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'operator',
            'avatar' => 'avatar-1.jpg',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info('Akun operator sekolah berhasil dibuat.');
        $this->line('ID     : ' . $id);
        $this->line('Nama   : ' . $nama);
        $this->line('Email  : ' . $email);
        $this->warn('Operator hanya dapat membuka halaman Pengaturan Absensi (jam kerja, hari libur, pengaturan tanggal).');

        return self::SUCCESS;
    }
}
