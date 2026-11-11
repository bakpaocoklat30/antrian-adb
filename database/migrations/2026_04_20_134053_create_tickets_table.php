<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->string('nisn')->unique();
            $table->string('nama_cpd');
            $table->string('asal_sekolah');
            $table->string('whatsapp');
            $table->string('email');
            $table->text('alamat');
            $table->string('jalur_pendaftaran');
            
            $table->foreignId('service_date_id')->constrained('service_dates')->cascadeOnDelete();
            
            // STATUS BARU LENGKAP
            $table->enum('status', ['menunggu', 'hadir_di_lokasi', 'dipanggil', 'menghadap', 'selesai', 'dilewati'])->default('menunggu');
            
            // LOGIKA PANGGILAN
            $table->integer('call_count')->default(0)->comment('Berapa kali dipanggil (Maks 3x)');
            
            $table->foreignId('loket_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('qr_code_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};