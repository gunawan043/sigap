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
        Schema::create('kasbon', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // wajib nullable untuk nullOnDelete
            $table->string('nominal')->nullable(); // total pinjaman
            $table->date('tanggal_pinjaman')->nullable();
            $table->string('angsuran_perbulan')->nullable();
            $table->unsignedInteger('lama_angsuran')->nullable(); // dalam bulan
            $table->unsignedInteger('angsuran_ke')->default(1); // mulai dari 1
            $table->boolean('lunas')->default(false);
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            // $table->unsignedBigInteger('periode_id')->nullable();
            // $table->foreign('periode_id')->references('id')->on('priode')->nullOnDelete();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasbon');
    }
};
