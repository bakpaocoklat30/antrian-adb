<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_dates', function (Blueprint $table) {
            $table->id();
            $table->date('service_date')->comment('Tanggal pelayanan');
            // Menambahkan kolom Sesi dan Waktu
            $table->string('session_name')->comment('Contoh: Pagi / Siang');
            $table->time('start_time')->comment('Contoh: 08:00:00');
            $table->time('end_time')->comment('Contoh: 10:30:00');
            
            $table->integer('quota')->default(50);
            $table->integer('booked_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_dates');
    }
};