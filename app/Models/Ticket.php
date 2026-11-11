<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'ticket_number',
        'nisn',
        'nama_cpd',
        'asal_sekolah',
        'whatsapp',
        'email',
        'alamat',
        'jalur_pendaftaran',
        'form_data',
        'service_date_id',
        'status',
        'call_count',
        'loket_id',
        'catatan_loket',
        'qr_code_path',
    ];

    protected function casts(): array
    {
        return [
            'form_data' => 'array',
        ];
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function serviceDate()
    {
        return $this->belongsTo(ServiceDate::class);
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'loket_id');
    }

    /**
     * Ambil pasangan label & nilai dinamis dari form_data (atau fallback ke kolom lama).
     */
    public function displayDetails(): array
    {
        if (!empty($this->form_data)) {
            return $this->form_data;
        }

        $fallback = [];
        $map = [
            'asal_sekolah' => 'Asal Sekolah',
            'jalur_pendaftaran' => 'Jalur / Kategori',
            'whatsapp' => 'No. WhatsApp',
            'email' => 'Email',
            'alamat' => 'Alamat',
        ];

        foreach ($map as $col => $label) {
            if (!empty($this->$col)) {
                $fallback[] = [
                    'key' => $col,
                    'label' => $label,
                    'value' => $this->$col,
                ];
            }
        }

        return $fallback;
    }

    /**
     * Ambil nilai field tertentu (misal whatsapp) dari form_data atau kolom langsung.
     */
    public function fieldValue(string $key): ?string
    {
        if (!empty($this->form_data)) {
            foreach ($this->form_data as $item) {
                if (($item['key'] ?? '') === $key && !empty($item['value']) && $item['value'] !== '-') {
                    return (string) $item['value'];
                }
            }
        }

        return !empty($this->$key) ? (string) $this->$key : null;
    }

    /**
     * Payload ringkas untuk modal detail pendaftar di antarmuka admin.
     */
    public function detailPayload(): array
    {
        return [
            'event' => $this->event?->name ?? 'Event',
            'ticket' => $this->ticket_number,
            'identifier_label' => $this->event?->identifier_label ?? 'Identitas',
            'identifier' => $this->nisn,
            'name_label' => $this->event?->name_label ?? 'Nama',
            'name' => $this->nama_cpd,
            'details' => $this->displayDetails(),
        ];
    }
}
