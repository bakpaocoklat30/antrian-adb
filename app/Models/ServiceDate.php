<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDate extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_date',
        'session_name',
        'session_code', // 🔥 FITUR BARU: Tambahan Kode Sesi (Prefix)
        'start_time',
        'end_time',
        'quota',
        'booked_count',
        'is_active'
    ];

    protected $casts = [
        'service_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}