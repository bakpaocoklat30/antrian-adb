<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'running_text',
        'video_url',
        'video_type',
        'video_volume',
        'announcer_text',
        'voice_type',
        'voice_speed',
        'voice_pitch',
        'spmb_open_at',
        'is_maintenance',
        'is_ticket_exhausted',
        'jadwal_daftar_ulang' // 🔥 PENYEBAB UTAMANYA DI SINI! SEKARANG SUDAH DIIZINKAN 🔥
    ];
}