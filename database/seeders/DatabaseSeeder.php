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
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@smkn1adw.sch.id',
            'password' => Hash::make('password'),
            'role' => 'super_admin'
        ]);

        // 2. Akun Scanner (Pos OSIS)
        User::create([
            'name' => 'Petugas OSIS 1',
            'email' => 'osis1@smkn1adw.sch.id',
            'password' => Hash::make('password'),
            'role' => 'scanner'
        ]);

        User::create([
            'name' => 'Petugas OSIS 2',
            'email' => 'osis2@smkn1adw.sch.id',
            'password' => Hash::make('password'),
            'role' => 'scanner'
        ]);

        // 3. Akun 7 Loket Verifikator
        for ($i = 1; $i <= 7; $i++) {
            User::create([
                'name' => "Petugas Loket $i",
                'email' => "loket$i@smkn1adw.sch.id",
                'password' => Hash::make('password'),
                'role' => 'verifikator',
                'loket_number' => $i
            ]);
        }
    }
}