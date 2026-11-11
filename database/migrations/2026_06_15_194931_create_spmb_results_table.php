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
        Schema::create('spmb_results', function (Blueprint $table) {
            $table->id();
            $table->string('nisn')->unique()->comment('Nomor Induk Siswa Nasional');
            $table->string('nama')->comment('Nama Lengkap Siswa');
            $table->string('asal_sekolah')->comment('Asal SMP/MTs');
            $table->string('jurusan')->comment('Singkatan Jurusan: TPFL, TK, TM, dll');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spmb_results');
    }
};