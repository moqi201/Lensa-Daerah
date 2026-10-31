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
        Schema::create('puskesmas_jabars', function (Blueprint $table) {
            $table->id();
            $table->string('kode_kabupaten_kota')->nullable();
            $table->string('nama_kabupaten_kota');
            $table->integer('jumlah_puskesmas')->default(0);
            $table->integer('kasus_ispa')->default(0);
            $table->integer('penerima_bansos')->default(0);
            $table->integer('balita_stunting')->default(0);
            $table->string('tahun')->default('2026');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puskesmas_jabars');
    }
};