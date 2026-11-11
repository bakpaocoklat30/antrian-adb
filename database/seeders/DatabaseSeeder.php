<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 0. Event Default Awal (dapat diedit / ditambah dari menu Manajemen Event)
        Event::firstOrCreate(
            ['slug' => 'pelayanan-spmb'],
            [
                'name' => 'Pelayanan Verifikasi Berkas SPMB',
                'subtitle' => 'SMK Negeri 1 Adiwerna',
                'description' => 'Silakan lengkapi formulir di bawah ini untuk memperoleh nomor antrian dan e-Tiket QR Code.',
                'identifier_label' => 'NISN',
                'identifier_placeholder' => 'Ketik 10 Digit NISN...',
                'name_label' => 'Nama Lengkap (Sesuai Ijazah)',
                'form_fields' => Event::defaultFormFields(),
                'checklist_items' => [
                    'Membawa dokumen asli & fotokopi persyaratan sesuai ketentuan event',
                    'Menyiapkan e-Tiket ini saat nomor antrian dipanggil menuju loket pelayanan',
                ],
                'confirmation_text' => 'Saya menyatakan data yang saya isikan di atas sudah benar dan sesuai.',
                'requires_checkin' => false,
                'is_active' => true,
            ]
        );

        // 1. Akun Super Admin
        User::firstOrCreate(
            ['email' => 'admin@smkn1adw.sch.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // 2. Akun Scanner (Pos OSIS)
        User::firstOrCreate(
            ['email' => 'osis1@smkn1adw.sch.id'],
            [
                'name' => 'Petugas OSIS 1',
                'password' => Hash::make('password'),
                'role' => 'scanner',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'osis2@smkn1adw.sch.id'],
            [
                'name' => 'Petugas OSIS 2',
                'password' => Hash::make('password'),
                'role' => 'scanner',
                'is_active' => true,
            ]
        );

        // 3. Akun 7 Loket Verifikator
        for ($i = 1; $i <= 7; $i++) {
            User::firstOrCreate(
                ['email' => "loket$i@smkn1adw.sch.id"],
                [
                    'name' => "Petugas Loket $i",
                    'password' => Hash::make('password'),
                    'role' => 'verifikator',
                    'loket_number' => $i,
                    'is_active' => true,
                ]
            );
        }
    }
}
