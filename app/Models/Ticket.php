<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number',
        'nisn',
        'nama_cpd',
        'asal_sekolah',
        'whatsapp',
        'email',
        'alamat',
        'jalur_pendaftaran',
        'service_date_id',
        'status',
        'loket_id',
        'qr_code_path'
    ];

    // Relasi: Tiket ini milik Tanggal/Jadwal tertentu
    public function serviceDate()
    {
        return $this->belongsTo(ServiceDate::class);
    }

    // Relasi: Tiket ini dilayani oleh Verifikator (User) siapa?
    public function verifikator()
    {
        return $this->belongsTo(User::class, 'loket_id');
    }
}