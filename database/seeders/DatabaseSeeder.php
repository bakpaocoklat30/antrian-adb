<?php

namespace Database\Seeders;

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
