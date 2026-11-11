<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sinkronisasi kolom inti sistem antrian, loket verifikator, dan layar aula.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'status_loket')) {
                $table->string('status_loket', 20)->default('aktif')->after('is_active');
            }
            if (!Schema::hasColumn('users', 'keterangan_loket')) {
                $table->string('keterangan_loket')->nullable()->after('status_loket');
            }
        });

        Schema::table('service_dates', function (Blueprint $table) {
            if (!Schema::hasColumn('service_dates', 'session_code')) {
                $table->string('session_code', 10)->nullable()->after('session_name');
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'catatan_loket')) {
                $table->text('catatan_loket')->nullable()->after('loket_id');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'global_sequence')) {
                $table->bigInteger('global_sequence')->default(0)->after('id');
            }
            if (!Schema::hasColumn('settings', 'voice_speed')) {
                $table->decimal('voice_speed', 4, 2)->default(0.80)->after('announcer_text');
            }
            if (!Schema::hasColumn('settings', 'voice_pitch')) {
                $table->decimal('voice_pitch', 4, 2)->default(1.00)->after('voice_speed');
            }
            if (!Schema::hasColumn('settings', 'spmb_open_at')) {
                $table->dateTime('spmb_open_at')->nullable()->after('voice_pitch');
            }
            if (!Schema::hasColumn('settings', 'is_maintenance')) {
                $table->boolean('is_maintenance')->default(false)->after('spmb_open_at');
            }
            if (!Schema::hasColumn('settings', 'is_ticket_exhausted')) {
                $table->boolean('is_ticket_exhausted')->default(false)->after('is_maintenance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = array_values(array_filter(['status_loket', 'keterangan_loket'], fn ($c) => Schema::hasColumn('users', $c)));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });

        Schema::table('service_dates', function (Blueprint $table) {
            if (Schema::hasColumn('service_dates', 'session_code')) {
                $table->dropColumn('session_code');
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'catatan_loket')) {
                $table->dropColumn('catatan_loket');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            $cols = array_values(array_filter(
                ['global_sequence', 'voice_speed', 'voice_pitch', 'spmb_open_at', 'is_maintenance', 'is_ticket_exhausted'],
                fn ($c) => Schema::hasColumn('settings', $c)
            ));
            if ($cols) {
                $table->dropColumn($cols);
            }
        });
    }
};
