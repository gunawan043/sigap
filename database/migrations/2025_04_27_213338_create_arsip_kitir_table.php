<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('arsip_kitir', function (Blueprint $table) {
            $table->id();
            $table->string('priode_gaji')->nullable(); // bisa format 'April 2025' atau '2025-04'
            $table->unsignedBigInteger('user_id')->nullable(); // nullable biar aman kalau user dihapus
            $table->string('nama_ptk')->nullable();
            $table->string('induk')->nullable(); // yayasan, sekolah dll
            $table->string('tmt')->nullable(); // Tanggal Mulai Tugas
            $table->string('jk')->nullable();
            $table->string('f')->nullable();
            $table->string('mk')->nullable();
            $table->string('pt')->nullable();
            $table->string('gol_pt')->nullable();
            $table->string('gaji_pokok')->nullable();
            $table->string('kj1')->nullable();
            $table->string('kj2')->nullable();
            $table->string('kj3')->nullable();
            $table->string('kj4')->nullable();
            $table->string('kj5')->nullable();
            $table->string('t_jab')->nullable();
            $table->string('total_gaji')->nullable();
            $table->string('jam_mengajar')->nullable();
            $table->string('total_rp_jam')->nullable();
            $table->string('insentif_kehadiran')->nullable();
            $table->string('uang_lauk_pauk')->nullable();
            $table->string('pot_kasbon')->nullable();
            $table->string('pot_insidentil')->nullable();
            $table->string('total_potongan')->nullable();
            // $table->string('total_gaji_tanpa_pot')->nullable();
            // $table->string('gaji_normal')->nullable();
            // $table->string('nominal')->nullable();
            $table->timestamps();

            // Foreign key tanpa penghapusan otomatis
            // $table->unsignedBigInteger('periode_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            // $table->foreign('periode_id')->references('id')->on('priode')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_kitir');
    }
};
