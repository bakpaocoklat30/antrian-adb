<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpmbResult extends Model
{
    use HasFactory;

    protected $table = 'spmb_results';

    protected $fillable = [
        'nisn',
        'nama',
        'asal_sekolah',
        'jalur_pendaftaran',
        'jurusan',
        'latitude',   // 👈 Wajib ditambahkan
        'longitude',  // 👈 Wajib ditambahkan
    ];
}