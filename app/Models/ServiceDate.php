<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'service_date',
        'session_name',
        'session_code',
        'start_time',
        'end_time',
        'quota',
        'booked_count',
        'is_active',
    ];

    protected $casts = [
        'service_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
