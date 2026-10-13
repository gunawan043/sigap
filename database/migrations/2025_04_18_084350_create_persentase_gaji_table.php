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
        Schema::create('persentase_gaji', function (Blueprint $table) {
            $table->id();
            $table->string('persentase_golongan_1')->nullable();
            $table->string('persentase_golongan_2')->nullable();
            $table->string('persentase_golongan_3')->nullable();
            $table->string('persentase_golongan_4')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persentase_gaji');
    }
};
