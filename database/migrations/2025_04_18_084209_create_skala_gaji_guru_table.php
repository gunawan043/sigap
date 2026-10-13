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
        Schema::create('skala_gaji_guru', function (Blueprint $table) {
            $table->id();
            $table->string('masa_kerja')->nullable();
            $table->string('level')->nullable();
            $table->string('golongan_1')->nullable();
            $table->string('golongan_2')->nullable();
            $table->string('golongan_3')->nullable();
            $table->string('golongan_4')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skala_gaji_guru');
    }
};
