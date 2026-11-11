<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class Controller
{
    /**
     * 🔥 MESIN GENERATOR ANTI TABRAKAN (RACE CONDITION) 🔥
     * Dilengkapi dengan SMART SELF-HEALING untuk sinkronisasi data yang tertinggal.
     */
    protected function generateSequenceNumber($prefix)
    {
        return DB::transaction(function () use ($prefix) {
            // 1. AUTO-PATCHING SCHEMA (Pastikan kolom global_sequence ada)
            if (!Schema::hasColumn('settings', 'global_sequence')) {
                Schema::table('settings', function ($table) {
                    $table->bigInteger('global_sequence')->default(0)->after('id');
                });
            }

            // 2. PASTIKAN ROW SETTING EXIST
            $exists = DB::table('settings')->where('id', 1)->exists();
            if (!$exists) {
                DB::table('settings')->insert(['id' => 1, 'global_sequence' => 0]);
            }

            // 3. 🛡️ SMART SYNC (PENYEMBUHAN DATA LAMA/TERTINGGAL) 🛡️
            // Secara efisien menarik 100 tiket terakhir untuk mendeteksi angka paling ujung (riil)
            $recentTickets = DB::table('tickets')->orderBy('id', 'desc')->take(100)->pluck('ticket_number');
            $trueMax = 0;
            
            foreach($recentTickets as $tn) {
                $parts = explode('-', $tn);
                if(isset($parts[0])) {
                    // Buang semua huruf, ambil angkanya saja secara murni
                    $num = (int) preg_replace('/[^0-9]/', '', $parts[0]);
                    if($num > $trueMax) $trueMax = $num;
                }
            }

            $currentSeq = DB::table('settings')->where('id', 1)->value('global_sequence');
            
            // Jika sequence di settings tertinggal dari angka asli di tiket, PAKSA LOMPAT!
            if ($currentSeq < $trueMax) {
                DB::table('settings')->where('id', 1)->update(['global_sequence' => $trueMax]);
            }

            // 4. ATOMIC INCREMENT MURNI (BYPASS ELOQUENT SILENT FAIL)
            DB::table('settings')->where('id', 1)->increment('global_sequence', 1);

            // 5. AMBIL ANGKA TERBARU SETELAH DITAMBAH
            $latestSequence = DB::table('settings')->where('id', 1)->value('global_sequence');

            // 6. GENERATE FORMAT TIKET
            $shortNumber = $prefix . str_pad($latestSequence, 3, '0', STR_PAD_LEFT);
            $fullNumber = $shortNumber . '-' . strtoupper(Str::random(4));

            return (object) [
                'short' => $shortNumber,
                'full' => $fullNumber
            ];
        });
    }
}