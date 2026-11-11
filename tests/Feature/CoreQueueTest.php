<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\ServiceDate;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CoreQueueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Setting::create([
            'global_sequence' => 0,
            'is_maintenance' => false,
            'is_ticket_exhausted' => false,
            'spmb_open_at' => now()->subMinute(),
            'voice_speed' => 0.8,
            'voice_pitch' => 1.0,
        ]);
    }

    private function createEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'name' => 'Pelayanan Tes',
            'slug' => 'pelayanan-tes-' . uniqid(),
            'subtitle' => 'Subjudul Event',
            'description' => 'Deskripsi singkat event.',
            'identifier_label' => 'NISN',
            'identifier_placeholder' => 'Masukkan NISN',
            'name_label' => 'Nama Lengkap',
            'form_fields' => [
                [
                    'key' => 'asal_instansi',
                    'label' => 'Asal Instansi',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Instansi',
                    'options' => [],
                ],
                [
                    'key' => 'kategori',
                    'label' => 'Kategori',
                    'type' => 'select',
                    'required' => true,
                    'placeholder' => '',
                    'options' => ['Reguler', 'VIP'],
                ],
                [
                    'key' => 'whatsapp',
                    'label' => 'No. WhatsApp',
                    'type' => 'number',
                    'required' => false,
                    'placeholder' => '08123',
                    'options' => [],
                ],
            ],
            'checklist_items' => ['Bawa kartu identitas'],
            'confirmation_text' => 'Data saya benar.',
            'is_active' => true,
        ], $overrides));
    }

    private function createSessionForEvent(Event $event, ?string $date = null, array $overrides = []): ServiceDate
    {
        return ServiceDate::create(array_merge([
            'event_id' => $event->id,
            'service_date' => $date ?? Carbon::today()->toDateString(),
            'session_name' => 'Sesi Pagi',
            'session_code' => 'A',
            'start_time' => '08:00:00',
            'end_time' => '12:00:00',
            'quota' => 50,
            'is_active' => true,
        ], $overrides));
    }

    public function test_dynamic_index_adapts_to_zero_one_and_multiple_active_events(): void
    {
        // 0 active events -> empty state
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Tidak Ada Event Aktif');

        // 1 active event -> renders direct form
        $eventOne = $this->createEvent([
            'name' => 'Event Satu',
            'slug' => 'event-satu',
            'identifier_label' => 'NIK',
        ]);
        $this->createSessionForEvent($eventOne);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Event Satu')
            ->assertSee('Asal Instansi')
            ->assertDontSee('Pilih Event Layanan');

        // 2 active events + 1 inactive event -> selector on /, direct /e/{slug}
        $eventTwo = $this->createEvent([
            'name' => 'Event Dua',
            'slug' => 'event-dua',
        ]);
        $this->createEvent([
            'name' => 'Event Arsip',
            'slug' => 'event-arsip',
            'is_active' => false,
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Silakan pilih event atau jenis pelayanan yang ingin Anda ikuti.')
            ->assertSee('Event Satu')
            ->assertSee('Event Dua')
            ->assertSee('/e/event-satu')
            ->assertSee('/e/event-dua')
            ->assertDontSee('Event Arsip');

        $this->get('/e/event-dua')
            ->assertStatus(200)
            ->assertSee('Event Dua');

        $this->get('/e/event-arsip')->assertStatus(404);
    }

    public function test_superadmin_can_create_and_toggle_event_and_non_superadmin_is_blocked(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $loket = User::factory()->create(['role' => 'verifikator', 'loket_number' => 1, 'status_loket' => 'aktif', 'is_active' => true]);

        $this->actingAs($loket)->get('/admin/manage-events')->assertStatus(403);
        $this->actingAs($loket)->post('/admin/events/store', ['name' => 'Illegal'])->assertStatus(403);

        $response = $this->actingAs($superAdmin)->post('/admin/events/store', [
            'name' => 'Pendaftaran Beasiswa',
            'subtitle' => 'Gelombang 1',
            'description' => 'Antrian verifikasi beasiswa',
            'identifier_label' => 'NIM',
            'identifier_placeholder' => 'Masukkan NIM mahasiswa',
            'name_label' => 'Nama Mahasiswa',
            'fields' => [
                ['label' => 'Fakultas', 'type' => 'text', 'required' => '1', 'placeholder' => 'Teknik'],
                ['label' => 'Jalur', 'type' => 'select', 'required' => '1', 'options' => 'KIP-K, Prestasi'],
                ['label' => 'Berkas', 'type' => 'checkbox', 'required' => '0', 'options' => 'KTP, Transkrip'],
            ],
            'checklist_text' => "Bawa KTM\nBawa KRS",
            'confirmation_text' => 'Saya setuju.',
            'is_active' => '1',
        ]);

        $response->assertRedirect();
        $event = Event::where('name', 'Pendaftaran Beasiswa')->firstOrFail();

        $this->assertTrue($event->is_active);
        $this->assertFalse($event->requires_checkin);
        $this->assertSame('NIM', $event->identifier_label);
        $this->assertCount(3, $event->form_fields);
        $this->assertSame(['Bawa KTM', 'Bawa KRS'], $event->checklist_items);

        // Super admin enables requires_checkin via updateEvent
        $this->actingAs($superAdmin)->put("/admin/events/{$event->id}", [
            'name' => 'Pendaftaran Beasiswa',
            'identifier_label' => 'NIM',
            'name_label' => 'Nama Mahasiswa',
            'requires_checkin' => '1',
            'is_active' => '1',
        ])->assertRedirect();
        $this->assertTrue($event->fresh()->requires_checkin);

        $this->actingAs($superAdmin)->post("/admin/events/{$event->id}/toggle")->assertRedirect();
        $this->assertFalse($event->fresh()->is_active);
    }

    public function test_same_identifier_allowed_across_events_and_dynamic_form_persists(): void
    {
        $eventA = $this->createEvent(['name' => 'Event A', 'slug' => 'event-a', 'confirmation_text' => null]);
        $eventB = $this->createEvent(['name' => 'Event B', 'slug' => 'event-b', 'confirmation_text' => null]);
        $this->createSessionForEvent($eventA);
        $this->createSessionForEvent($eventB);

        // Missing required dynamic field triggers validation error
        $this->post('/ticket', [
            'event_id' => $eventA->id,
            'nisn' => '332800001',
            'nama_cpd' => 'Budi Santoso',
            'fields' => [
                'asal_instansi' => '',
                'kategori' => 'Reguler',
            ],
        ])->assertSessionHasErrors(['fields.asal_instansi']);

        // Valid registration in Event A
        $resA = $this->post('/ticket', [
            'event_id' => $eventA->id,
            'nisn' => '332800001',
            'nama_cpd' => 'Budi Santoso',
            'fields' => [
                'asal_instansi' => 'SMAN 1',
                'kategori' => 'VIP',
                'whatsapp' => '08123456789',
            ],
        ]);

        $ticketA = Ticket::where('event_id', $eventA->id)->where('nisn', '332800001')->firstOrFail();
        $resA->assertRedirect(route('ticket.success', $ticketA->ticket_number));

        // Verify stored dynamic form_data & PDF/success pages
        $this->assertSame('08123456789', $ticketA->fieldValue('whatsapp'));
        $this->assertSame('SMAN 1', $ticketA->fieldValue('asal_instansi'));
        $this->assertSame('VIP', $ticketA->fieldValue('kategori'));
        $this->get(route('ticket.success', $ticketA->ticket_number))
            ->assertStatus(200)
            ->assertSee('Event A')
            ->assertSee(explode('-', $ticketA->ticket_number)[0]);
        $pdfRes = $this->get(route('ticket.download', $ticketA->ticket_number));
        $pdfRes->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $pdfRes->headers->get('content-type'));

        // Reprint works for Event A even when multiple events share same identifier
        $this->postJson(route('ticket.reprint'), [
            'nisn' => '332800001',
            'event_id' => $eventA->id,
        ])->assertStatus(200)->assertJson([
            'success' => true,
            'redirect' => route('ticket.success', $ticketA->ticket_number),
        ]);

        // Duplicate identifier on SAME Event A fails
        $this->post('/ticket', [
            'event_id' => $eventA->id,
            'nisn' => '332800001',
            'nama_cpd' => 'Budi Santoso',
            'fields' => [
                'asal_instansi' => 'SMAN 1',
                'kategori' => 'Reguler',
            ],
        ])->assertSessionHasErrors(['nisn']);

        // Same identifier on DIFFERENT Event B succeeds
        $this->post('/ticket', [
            'event_id' => $eventB->id,
            'nisn' => '332800001',
            'nama_cpd' => 'Budi Santoso',
            'fields' => [
                'asal_instansi' => 'SMAN 2',
                'kategori' => 'Reguler',
            ],
        ])->assertRedirect();

        $this->assertSame(2, Ticket::where('nisn', '332800001')->count());
    }

    public function test_scanner_mutasi_verifikator_layar_and_csv_export_work_per_event(): void
    {
        $event = $this->createEvent([
            'name' => 'Layanan Alumni',
            'slug' => 'layar-alumni',
            'requires_checkin' => true,
        ]);
        $yesterdaySession = $this->createSessionForEvent($event, Carbon::yesterday()->toDateString(), ['session_name' => 'Kemarin']);
        $todaySession = $this->createSessionForEvent($event, Carbon::today()->toDateString(), ['session_name' => 'Hari Ini']);

        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $scanner = User::factory()->create(['role' => 'scanner', 'is_active' => true]);
        $loket = User::factory()->create(['role' => 'verifikator', 'loket_number' => 1, 'status_loket' => 'aktif', 'is_active' => true]);

        // Create missed ticket from yesterday
        $missedTicket = Ticket::create([
            'event_id' => $event->id,
            'ticket_number' => '001-LAMA',
            'nisn' => '99887766',
            'nama_cpd' => 'Siti Aminah',
            'form_data' => [
                ['key' => 'asal_instansi', 'label' => 'Asal Instansi', 'value' => 'Alumni 2020'],
            ],
            'service_date_id' => $yesterdaySession->id,
            'status' => 'menunggu',
        ]);

        // Scanner Go-Show
        $this->actingAs($scanner)->postJson(route('admin.scanner.goshow'), [
            'event_id' => $event->id,
            'nisn' => '11223344',
            'nama_cpd' => 'Andi Wijaya',
            'fields' => [
                'asal_instansi' => 'Umum',
                'kategori' => 'Reguler',
            ],
        ])->assertStatus(200)->assertJson([
            'success' => true,
            'event_name' => 'Layanan Alumni',
            'nama_cpd' => 'ANDI WIJAYA',
        ]);

        // Scanner Check-In for a normal queued ticket on today's session
        $checkinTicket = Ticket::create([
            'event_id' => $event->id,
            'ticket_number' => 'S003-CHK',
            'nisn' => '77665544',
            'nama_cpd' => 'BUDI CHECKIN',
            'form_data' => [['key' => 'asal_instansi', 'label' => 'Asal Instansi', 'value' => 'Alumni 2022']],
            'service_date_id' => $todaySession->id,
            'status' => 'menunggu',
        ]);

        $this->actingAs($scanner)->postJson(route('admin.scanner.checkin'), [
            'ticket_number' => $checkinTicket->ticket_number,
        ])->assertStatus(200)->assertJson([
            'success' => true,
            'event_name' => 'Layanan Alumni',
            'ticket_short' => 'S003',
        ]);
        $this->assertSame('hadir_di_lokasi', $checkinTicket->fresh()->status);

        $goShowTicket = Ticket::where('nisn', '11223344')->firstOrFail();
        $this->assertSame('hadir_di_lokasi', $goShowTicket->status);
        $this->assertSame($todaySession->id, $goShowTicket->service_date_id);

        // Mutasi yesterday's ticket into today's session for the same event
        $this->actingAs($loket)->postJson(route('admin.cari_nisn'), [
            'nisn' => '99887766',
        ])->assertStatus(200)->assertJson([
            'success' => true,
            'event_name' => 'Layanan Alumni',
        ]);

        $this->actingAs($loket)->postJson(route('admin.mutasi'), [
            'ticket_id' => $missedTicket->id,
        ])->assertStatus(200)->assertJson(['success' => true]);

        $missedTicket->refresh();
        $this->assertSame($todaySession->id, $missedTicket->service_date_id);
        $this->assertSame('hadir_di_lokasi', $missedTicket->status);

        // Loket dashboard renders Dashboard Loket & unlimited repeat calls track click count
        $this->actingAs($loket)->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('Dashboard Loket')
            ->assertDontSee('Dashboard Verifikator')
            ->assertSee('Layanan Alumni')
            ->assertSee('Andi Wijaya');

        foreach ([1, 2, 3, 4] as $expectedCount) {
            $this->actingAs($loket)->postJson(route('admin.panggil'), [
                'ticket_id' => $goShowTicket->id,
            ])->assertStatus(200)->assertJson([
                'success' => true,
                'call_count' => $expectedCount,
            ]);
        }

        $this->actingAs($loket)->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('4 kali')
            ->assertDontSee('Max Panggilan');

        $this->getJson(route('api.layar'))
            ->assertStatus(200)
            ->assertJsonFragment([
                'event_name' => 'Layanan Alumni',
                'ticket' => explode('-', $goShowTicket->ticket_number)[0],
                'loket' => 1,
                'call_count' => 4,
            ]);

        // Super admin manages sessions, attendance tracker & scanner without Blade errors
        $this->actingAs($superAdmin)->get(route('admin.manage_sessions', ['event_id' => $event->id]))
            ->assertStatus(200)
            ->assertSee('Layanan Alumni');
        $this->actingAs($superAdmin)->post(route('admin.service_date.store'), [
            'event_id' => $event->id,
            'service_date' => Carbon::tomorrow()->toDateString(),
            'session_name' => 'Sesi Tambahan',
            'session_code' => 'ST',
            'start_time' => '13:00',
            'end_time' => '15:00',
            'quota' => 25,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('service_dates', [
            'event_id' => $event->id,
            'session_name' => 'Sesi Tambahan',
        ]);

        $this->actingAs($superAdmin)->get(route('admin.attendance', ['event_id' => $event->id]))
            ->assertStatus(200)
            ->assertSee('Layanan Alumni')
            ->assertSee('Andi Wijaya');
        $this->actingAs($scanner)->get(route('admin.scanner'))
            ->assertStatus(200)
            ->assertSee('Layanan Alumni');

        // CSV Export works with dynamic columns
        $exportRes = $this->actingAs($superAdmin)->get(route('admin.attendance', [
            'event_id' => $event->id,
            'export' => 'true',
        ]));
        $exportRes->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $exportRes->headers->get('content-type'));
    }

    public function test_event_without_checkin_enters_queue_directly_and_hides_checkin_menu(): void
    {
        $event = $this->createEvent([
            'name' => 'Layanan Cepat',
            'slug' => 'layanan-cepat',
            'requires_checkin' => false,
            'confirmation_text' => null,
        ]);
        $this->createSessionForEvent($event);

        $scanner = User::factory()->create(['role' => 'scanner', 'is_active' => true]);
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

        // Scanner menu hides Check-In section when no active event requires check-in
        $this->actingAs($scanner)->get(route('admin.scanner'))
            ->assertStatus(200)
            ->assertDontSee('Scanner Check-In Antrian')
            ->assertSee('Pendaftaran Offline (Go-Show)');

        // Public ticket submission immediately enters 'hadir_di_lokasi' queue without check-in
        $this->post('/ticket', [
            'event_id' => $event->id,
            'nisn' => '55667788',
            'nama_cpd' => 'Rina Kartika',
            'fields' => [
                'asal_instansi' => 'SMK 1',
                'kategori' => 'Reguler',
            ],
        ])->assertRedirect();

        $ticket = Ticket::where('event_id', $event->id)->where('nisn', '55667788')->firstOrFail();
        $this->assertSame('hadir_di_lokasi', $ticket->status);

        $this->get(route('ticket.success', $ticket->ticket_number))
            ->assertStatus(200)
            ->assertSee('Terdaftar di Antrian')
            ->assertDontSee('Petugas Scanner di Lokasi');

        $this->get(route('ticket.download', $ticket->ticket_number))->assertStatus(200);

        // Attempting check-in for a non-checkin event returns clear error JSON
        $this->actingAs($scanner)->postJson(route('admin.scanner.checkin'), [
            'ticket_number' => $ticket->ticket_number,
        ])->assertStatus(200)->assertJson([
            'success' => false,
        ]);

        // Turning ON requires_checkin restores Check-In menu & sets new registrations to 'menunggu'
        $this->actingAs($superAdmin)->put("/admin/events/{$event->id}", [
            'name' => 'Layanan Cepat',
            'identifier_label' => 'NISN',
            'name_label' => 'Nama Lengkap',
            'requires_checkin' => '1',
            'is_active' => '1',
        ])->assertRedirect();

        $this->actingAs($scanner)->get(route('admin.scanner'))
            ->assertStatus(200)
            ->assertSee('Scanner Check-In Antrian');
    }

    public function test_tts_endpoint_caches_indonesian_audio_and_display_settings_persist_voice_engine(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);

        $this->actingAs($superAdmin)->get(route('admin.manage_display'))
            ->assertStatus(200)
            ->assertSee('disapa-tts.js')
            ->assertSee('server_id');

        $this->get(route('ticket.layar'))
            ->assertStatus(200)
            ->assertSee('disapa-tts.js');

        $this->actingAs($superAdmin)->postJson(route('admin.live_setting'), [
            'field' => 'voice_engine',
            'value' => 'browser_id',
        ])->assertStatus(200)->assertJson(['success' => true]);

        $this->assertSame('browser_id', Setting::first()->voice_engine);

        $phrase = 'Nomor antrian A satu loket satu ' . uniqid();
        $cachePath = storage_path('app/tts-cache/' . sha1('id:' . $phrase) . '.mp3');
        @unlink($cachePath);

        try {
            Http::fake([
                'translate.google.com/*' => Http::sequence()
                    ->push('ID3' . str_repeat("\x01", 320), 200, ['Content-Type' => 'audio/mpeg'])
                    ->push('unavailable', 503),
            ]);

            $res = $this->get(route('api.tts', ['q' => $phrase]));
            $res->assertStatus(200);
            $this->assertStringContainsString('audio/mpeg', (string) $res->headers->get('content-type'));
            $this->assertFileExists($cachePath);

            // Subsequent call serves from disk cache without hitting upstream step 2
            $this->get(route('api.tts', ['q' => $phrase]))->assertStatus(200);

            // Uncached phrase hits upstream step 2 (503) so frontend falls back cleanly
            $this->get(route('api.tts', ['q' => 'Frasa offline ' . uniqid()]))->assertStatus(503);
        } finally {
            @unlink($cachePath);
        }
    }

    public function test_superadmin_can_download_database_backup_and_non_superadmin_is_blocked(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $loket = User::factory()->create(['role' => 'verifikator', 'loket_number' => 2, 'is_active' => true]);

        $event = $this->createEvent([
            'name' => 'Verifikasi Berkas Backup Test',
            'slug' => 'backup-test-event',
        ]);
        $session = $this->createSessionForEvent($event);

        Ticket::create([
            'event_id' => $event->id,
            'ticket_number' => 'B001-TEST',
            'nisn' => '44556677',
            'nama_cpd' => 'Peserta Backup',
            'form_data' => [['key' => 'asal_instansi', 'label' => 'Asal Instansi', 'value' => 'SMK Uji']],
            'service_date_id' => $session->id,
            'status' => 'hadir_di_lokasi',
        ]);

        $this->actingAs($loket)->get(route('admin.backup_db'))->assertStatus(403);

        $response = $this->actingAs($superAdmin)->get(route('admin.backup_db'));
        $response->assertStatus(200);
        $this->assertStringContainsString('.sql', (string) $response->headers->get('content-disposition'));

        $sql = $response->streamedContent();
        $this->assertStringContainsString('CREATE TABLE', $sql);
        $this->assertStringContainsString('Verifikasi Berkas Backup Test', $sql);
        $this->assertStringContainsString('B001-TEST', $sql);
        $this->assertStringContainsString('Peserta Backup', $sql);
    }
}
