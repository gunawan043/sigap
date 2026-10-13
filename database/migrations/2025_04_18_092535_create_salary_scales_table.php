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
        Schema::create('salary_scales', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('masa_kerja')->nullable(); // MK (0 - 15)
            $table->string('level')->nullable(); // A, B, C, D, E, F
            $table->string('golongan_pt')->nullable(); // Gol 1 - Gol 4
            $table->string('strata')->nullable();// S0 - S3 (opsional, bisa diambil dari users juga)
            $table->string('gaji_pokok')->nullable(); // disimpan sebagai string agar fleksibel
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_scales');
    }
};
