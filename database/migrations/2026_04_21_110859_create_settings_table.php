<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->text('running_text')->nullable();
            $table->string('video_url')->nullable();
            $table->enum('video_type', ['youtube', 'local'])->default('youtube');
            
            // FITUR BARU FASE 10
            $table->integer('video_volume')->default(60); // Default volume 60%
            $table->text('announcer_text')->nullable(); // Text Voice
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};