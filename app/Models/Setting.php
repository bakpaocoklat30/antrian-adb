<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'global_sequence',
        'running_text',
        'video_url',
        'video_type',
        'video_volume',
        'announcer_text',
        'voice_speed',
        'voice_pitch',
        'spmb_open_at',
        'is_maintenance',
        'is_ticket_exhausted',
    ];

    protected function casts(): array
    {
        return [
            'is_maintenance' => 'boolean',
            'is_ticket_exhausted' => 'boolean',
            'spmb_open_at' => 'datetime',
        ];
    }
}
