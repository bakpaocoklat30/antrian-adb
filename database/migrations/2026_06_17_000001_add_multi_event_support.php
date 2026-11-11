<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('subtitle')->nullable();
                $table->text('description')->nullable();
                $table->string('identifier_label', 60)->default('NISN');
                $table->string('identifier_placeholder')->nullable();
                $table->string('name_label', 80)->default('Nama Lengkap');
                $table->json('form_fields')->nullable();
                $table->json('checklist_items')->nullable();
                $table->text('confirmation_text')->nullable();
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }

        Schema::table('service_dates', function (Blueprint $table) {
            if (!Schema::hasColumn('service_dates', 'event_id')) {
                $table->foreignId('event_id')->nullable()->after('id')->constrained('events')->cascadeOnDelete();
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'event_id')) {
                $table->foreignId('event_id')->nullable()->after('id')->constrained('events')->cascadeOnDelete();
            }
            if (!Schema::hasColumn('tickets', 'form_data')) {
                $table->json('form_data')->nullable()->after('jalur_pendaftaran');
            }
        });

        // Lepas unique global pada nisn agar peserta yang sama bisa mengikuti lebih dari 1 event
        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropUnique('tickets_nisn_unique');
            });
        } catch (\Throwable $e) {
            // Abaikan jika index sudah tidak ada
        }

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('asal_sekolah')->nullable()->change();
            $table->string('whatsapp')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->text('alamat')->nullable()->change();
            $table->string('jalur_pendaftaran')->nullable()->change();
        });

        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->unique(['event_id', 'nisn'], 'tickets_event_identifier_unique');
            });
        } catch (\Throwable $e) {
            // Abaikan jika index sudah ada
        }

        // Backfill data lama (jika sudah ada sesi/tiket sebelum multi-event)
        $hasOrphanSessions = DB::table('service_dates')->whereNull('event_id')->exists();
        $hasOrphanTickets = DB::table('tickets')->whereNull('event_id')->exists();

        if ($hasOrphanSessions || $hasOrphanTickets) {
            $eventId = DB::table('events')->value('id');
            if (!$eventId) {
                $eventId = DB::table('events')->insertGetId([
                    'name' => 'Pendaftaran Antrian SPMB',
                    'slug' => 'spmb',
                    'subtitle' => 'SMK Negeri 1 Adiwerna',
                    'description' => 'Silakan lengkapi data diri Anda untuk mendapatkan nomor antrian pelayanan.',
                    'identifier_label' => 'NISN',
                    'identifier_placeholder' => 'Ketik 10 Digit NISN...',
                    'name_label' => 'Nama Lengkap (Sesuai Ijazah)',
                    'form_fields' => json_encode(\App\Models\Event::defaultFormFields()),
                    'checklist_items' => json_encode([]),
                    'confirmation_text' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('service_dates')->whereNull('event_id')->update(['event_id' => $eventId]);
            DB::table('tickets')->whereNull('event_id')->update(['event_id' => $eventId]);
        }
    }

    public function down(): void
    {
        try {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropUnique('tickets_event_identifier_unique');
            });
        } catch (\Throwable $e) {
        }

        Schema::table('tickets', function (Blueprint $table) {
            if (Schema::hasColumn('tickets', 'form_data')) {
                $table->dropColumn('form_data');
            }
            if (Schema::hasColumn('tickets', 'event_id')) {
                $table->dropConstrainedForeignId('event_id');
            }
        });

        Schema::table('service_dates', function (Blueprint $table) {
            if (Schema::hasColumn('service_dates', 'event_id')) {
                $table->dropConstrainedForeignId('event_id');
            }
        });

        Schema::dropIfExists('events');
    }
};
