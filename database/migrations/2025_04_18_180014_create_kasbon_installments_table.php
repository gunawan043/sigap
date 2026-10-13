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
        Schema::create('kasbon_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('kasbon_id')->nullable(); // <-- harus nullable!
            $table->unsignedBigInteger('user_id')->nullable();   // juga nullable
            $table->unsignedBigInteger('kitir_id')->nullable();  // juga nullable
            // $table->unsignedBigInteger('periode_id')->nullable();  // juga nullable
        
            $table->string('bulan'); // contoh: '2025-04'
            $table->string('jumlah')->nullable(); 
            $table->boolean('terpotong')->default(false);
            $table->timestamps();
        
            $table->foreign('kasbon_id')->references('id')->on('kasbon')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('kitir_id')->references('id')->on('kitir_gaji')->nullOnDelete();
            // $table->foreign('periode_id')->references('id')->on('priode')->nullOnDelete();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasbon_installments');
    }
};
