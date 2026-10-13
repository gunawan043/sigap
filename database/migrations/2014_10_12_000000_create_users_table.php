<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('no_hp')->nullable();
            $table->string('jk')->nullable();
            $table->string('alamat')->nullable();
            $table->string('subjek')->nullable();
            $table->string('awal_masuk')->nullable();
            $table->string('pendidikan')->nullable();
            $table->string('induk')->nullable();
            $table->string('ttl')->nullable();
            $table->string('role')->nullable();
            $table->text('avatar');
            $table->rememberToken();
            $table->timestamps();
        });

        // Akun admin pertama TIDAK dibuat di migrasi supaya tidak ada
        // kredensial yang ikut tersimpan di repository.
        // Buat manual dengan command:
        //   php artisan sigap:buat-admin nama@email.test
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
