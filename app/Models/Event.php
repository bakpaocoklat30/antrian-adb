<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subtitle',
        'description',
        'identifier_label',
        'identifier_placeholder',
        'name_label',
        'form_fields',
        'checklist_items',
        'confirmation_text',
        'requires_checkin',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'form_fields' => 'array',
            'checklist_items' => 'array',
            'requires_checkin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function serviceDates()
    {
        return $this->hasMany(ServiceDate::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Daftar field default saat membuat event baru atau seeding awal.
     */
    public static function defaultFormFields(): array
    {
        return [
            [
                'key' => 'asal_sekolah',
                'label' => 'Asal Sekolah / Instansi',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'Contoh: SMPN 1 Adiwerna',
                'options' => [],
            ],
            [
                'key' => 'whatsapp',
                'label' => 'No. WhatsApp Aktif',
                'type' => 'number',
                'required' => true,
                'placeholder' => '081234567890',
                'options' => [],
            ],
            [
                'key' => 'email',
                'label' => 'Email Aktif',
                'type' => 'email',
                'required' => true,
                'placeholder' => 'nama@contoh.com',
                'options' => [],
            ],
            [
                'key' => 'alamat',
                'label' => 'Alamat Lengkap',
                'type' => 'textarea',
                'required' => true,
                'placeholder' => 'Nama Jalan, RT/RW, Desa, Kecamatan, Kabupaten/Kota',
                'options' => [],
            ],
        ];
    }

    /**
     * Normalisasi definisi form_fields yang dikirim dari admin form builder.
     */
    public static function normalizeFields(array $rawFields): array
    {
        $normalized = [];
        $usedKeys = [];

        foreach ($rawFields as $index => $field) {
            $label = trim((string) ($field['label'] ?? ''));
            if ($label === '') {
                continue;
            }

            $baseKey = Str::slug((string) ($field['key'] ?? $label), '_');
            if ($baseKey === '') {
                $baseKey = 'field_' . ($index + 1);
            }

            $key = $baseKey;
            $suffix = 2;
            while (in_array($key, $usedKeys, true) || in_array($key, ['nisn', 'nama_cpd', 'event_id'], true)) {
                $key = $baseKey . '_' . $suffix;
                $suffix++;
            }
            $usedKeys[] = $key;

            $type = (string) ($field['type'] ?? 'text');
            if (!in_array($type, ['text', 'number', 'email', 'textarea', 'select', 'checkbox'], true)) {
                $type = 'text';
            }

            $optionsRaw = $field['options'] ?? [];
            if (is_string($optionsRaw)) {
                $optionsRaw = preg_split('/[\r\n,]+/', $optionsRaw) ?: [];
            }
            $options = array_values(array_filter(array_map('trim', (array) $optionsRaw), fn ($v) => $v !== ''));

            $normalized[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'required' => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'placeholder' => trim((string) ($field['placeholder'] ?? '')),
                'options' => $options,
            ];
        }

        return $normalized;
    }

    /**
     * Aturan validasi dinamis berdasarkan konfigurasi form_fields event.
     */
    public function dynamicValidationRules(): array
    {
        $rules = [];

        foreach ($this->form_fields ?? [] as $field) {
            $key = $field['key'] ?? null;
            if (!$key) {
                continue;
            }

            $inputName = 'fields.' . $key;
            $requiredRule = !empty($field['required']) ? 'required' : 'nullable';
            $type = $field['type'] ?? 'text';

            if ($type === 'email') {
                $rules[$inputName] = [$requiredRule, 'email', 'max:255'];
            } elseif ($type === 'number') {
                $rules[$inputName] = [$requiredRule, 'numeric'];
            } elseif ($type === 'checkbox') {
                $rules[$inputName] = [$requiredRule, 'array'];
                if (!empty($field['options'])) {
                    $rules[$inputName . '.*'] = ['string', 'in:' . implode(',', $field['options'])];
                }
            } elseif ($type === 'select' && !empty($field['options'])) {
                $rules[$inputName] = [$requiredRule, 'string', 'in:' . implode(',', $field['options'])];
            } else {
                $rules[$inputName] = [$requiredRule, 'string', 'max:2000'];
            }
        }

        return $rules;
    }

    /**
     * Ekstrak nilai isian dinamis menjadi array terstruktur [{'key', 'label', 'value'}].
     */
    public function extractFormData(Request $request): array
    {
        $submitted = $request->input('fields', []);
        $entries = [];

        foreach ($this->form_fields ?? [] as $field) {
            $key = $field['key'] ?? null;
            if (!$key) {
                continue;
            }

            $rawVal = $submitted[$key] ?? null;
            if (is_array($rawVal)) {
                $cleaned = array_values(array_filter(array_map('trim', $rawVal), fn ($v) => $v !== ''));
                $val = count($cleaned) > 0 ? implode(', ', $cleaned) : '-';
            } else {
                $val = trim((string) $rawVal);
                if ($val === '') {
                    $val = '-';
                }
            }

            $entries[] = [
                'key' => $key,
                'label' => $field['label'] ?? $key,
                'value' => $val,
            ];
        }

        return $entries;
    }
}
