<?php

namespace Tests\Feature;

use App\Models\ServiceDate;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_can_book_ticket_and_respects_lock_settings(): void
    {
        $session = ServiceDate::create([
            'service_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'session_name' => 'Pagi',
            'session_code' => 'P',
            'start_time' => '08:00:00',
            'end_time' => '11:00:00',
            'quota' => 5,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        Setting::create([
            'running_text' => 'Test',
            'is_maintenance' => false,
            'is_ticket_exhausted' => true,
        ]);

        $blocked = $this->post(route('ticket.store'), [
            'nisn' => '1234567890',
            'nama_cpd' => 'Budi Santoso',
            'asal_sekolah' => 'SMPN 1 Adiwerna',
            'whatsapp' => '081234567890',
            'email' => 'budi@example.com',
            'alamat' => 'Tegal',
            'jalur_pendaftaran' => 'REGULER',
        ]);

        $blocked->assertRedirect();
        $this->assertDatabaseCount('tickets', 0);

        Setting::first()->update(['is_ticket_exhausted' => false]);

        $response = $this->post(route('ticket.store'), [
            'nisn' => '1234567890',
            'nama_cpd' => 'Budi Santoso',
            'asal_sekolah' => 'SMPN 1 Adiwerna',
            'whatsapp' => '081234567890',
            'email' => 'budi@example.com',
            'alamat' => 'Tegal',
            'jalur_pendaftaran' => 'REGULER',
        ]);

        $ticket = Ticket::first();
        $this->assertNotNull($ticket);
        $this->assertStringStartsWith('P001-', $ticket->ticket_number);
        $this->assertEquals('menunggu', $ticket->status);
        $this->assertEquals(1, $session->fresh()->booked_count);

        $response->assertRedirect(route('ticket.success', $ticket->ticket_number));
        $this->get(route('ticket.success', $ticket->ticket_number))->assertOk();

        $pdfResponse = $this->get(route('ticket.download', $ticket->ticket_number));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdfResponse->headers->get('content-type'));
    }

    public function test_deactivated_user_is_blocked_and_goshow_and_mutasi_work(): void
    {
        $inactive = User::create([
            'name' => 'Inactive Verifikator',
            'email' => 'inactive@example.com',
            'password' => 'password',
            'role' => 'verifikator',
            'loket_number' => 2,
            'is_active' => false,
        ]);

        $this->actingAs($inactive)->get(route('admin.dashboard'))->assertRedirect(route('login'));

        $scanner = User::create([
            'name' => 'OSIS Scanner',
            'email' => 'scanner2@example.com',
            'password' => 'password',
            'role' => 'scanner',
            'is_active' => true,
        ]);

        $verifikator = User::create([
            'name' => 'Loket 1',
            'email' => 'loket1b@example.com',
            'password' => 'password',
            'role' => 'verifikator',
            'loket_number' => 1,
            'is_active' => true,
        ]);

        $yesterdaySession = ServiceDate::create([
            'service_date' => Carbon::yesterday('Asia/Jakarta')->toDateString(),
            'session_name' => 'Pagi Kemarin',
            'session_code' => 'P',
            'start_time' => '08:00:00',
            'end_time' => '11:00:00',
            'quota' => 50,
            'booked_count' => 1,
            'is_active' => true,
        ]);

        $todaySession = ServiceDate::create([
            'service_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'session_name' => 'Pagi Hari Ini',
            'session_code' => 'P',
            'start_time' => '08:00:00',
            'end_time' => '23:59:00',
            'quota' => 50,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        $expiredTicket = Ticket::create([
            'ticket_number' => 'P001-OLD1',
            'nisn' => '1122334455',
            'nama_cpd' => 'Ahmad Mutasi',
            'asal_sekolah' => 'SMPN 3 Adiwerna',
            'whatsapp' => '081234567800',
            'email' => 'ahmad@example.com',
            'alamat' => 'Tegal',
            'jalur_pendaftaran' => 'REGULER',
            'service_date_id' => $yesterdaySession->id,
            'status' => 'menunggu',
        ]);

        // Go-show oleh scanner
        $this->actingAs($scanner)
            ->postJson(route('admin.scanner.goshow'), [
                'nisn' => '5544332211',
                'nama_cpd' => 'Dewi GoShow',
                'asal_sekolah' => 'SMPN 4 Adiwerna',
                'whatsapp' => '081234567811',
                'email' => 'dewi@example.com',
                'alamat' => 'Adiwerna',
                'jalur_pendaftaran' => 'KIP',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(1, $todaySession->fresh()->booked_count);

        // Mutasi oleh verifikator
        $this->actingAs($verifikator)
            ->postJson(route('admin.mutasi'), ['ticket_id' => $expiredTicket->id])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals(0, $yesterdaySession->fresh()->booked_count);
        $this->assertEquals(2, $todaySession->fresh()->booked_count);
        $this->assertEquals($todaySession->id, $expiredTicket->fresh()->service_date_id);
        $this->assertEquals('hadir_di_lokasi', $expiredTicket->fresh()->status);
    }

    public function test_role_based_access_control_enforces_boundaries(): void
    {
        $scanner = User::create([
            'name' => 'OSIS Scanner',
            'email' => 'scanner@example.com',
            'password' => 'password',
            'role' => 'scanner',
            'is_active' => true,
        ]);

        $verifikator = User::create([
            'name' => 'Loket 1',
            'email' => 'loket1@example.com',
            'password' => 'password',
            'role' => 'verifikator',
            'loket_number' => 1,
            'is_active' => true,
        ]);

        // Guest cannot call toggle_loket
        $this->postJson(route('admin.toggle_loket'))->assertUnauthorized();

        // Scanner cannot open verifikator dashboard or super admin sessions
        $this->actingAs($scanner)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($scanner)->get(route('admin.manage_sessions'))->assertForbidden();
        $this->actingAs($scanner)->get(route('admin.scanner'))->assertOk();

        // Verifikator cannot open scanner or super admin users
        $this->actingAs($verifikator)->get(route('admin.scanner'))->assertForbidden();
        $this->actingAs($verifikator)->get(route('admin.manage_users'))->assertForbidden();
        $this->actingAs($verifikator)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_checkin_call_and_layar_api_end_to_end(): void
    {
        $scanner = User::create([
            'name' => 'OSIS Scanner',
            'email' => 'scanner@example.com',
            'password' => 'password',
            'role' => 'scanner',
            'is_active' => true,
        ]);

        $verifikator = User::create([
            'name' => 'Loket 1',
            'email' => 'loket1@example.com',
            'password' => 'password',
            'role' => 'verifikator',
            'loket_number' => 1,
            'is_active' => true,
        ]);

        $session = ServiceDate::create([
            'service_date' => Carbon::today('Asia/Jakarta')->toDateString(),
            'session_name' => 'Pagi',
            'session_code' => 'P',
            'start_time' => '08:00:00',
            'end_time' => '11:00:00',
            'quota' => 50,
            'booked_count' => 1,
            'is_active' => true,
        ]);

        $ticket = Ticket::create([
            'ticket_number' => 'P001-ABCD',
            'nisn' => '0099887766',
            'nama_cpd' => 'Siti Aminah',
            'asal_sekolah' => 'SMPN 2 Adiwerna',
            'whatsapp' => '081234567899',
            'email' => 'siti@example.com',
            'alamat' => 'Adiwerna',
            'jalur_pendaftaran' => 'REGULER',
            'service_date_id' => $session->id,
            'status' => 'menunggu',
        ]);

        // 1. Scanner check-in
        $this->actingAs($scanner)
            ->postJson(route('admin.scanner.checkin'), ['ticket_number' => 'P001-ABCD'])
            ->assertOk()
            ->assertJson(['success' => true, 'ticket_short' => 'P001']);

        $this->assertEquals('hadir_di_lokasi', $ticket->fresh()->status);

        // 2. Verifikator tutup & panggil (auto-open loket)
        $this->actingAs($verifikator)
            ->postJson(route('admin.toggle_loket'), ['keterangan' => 'ISTIRAHAT'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertEquals('tutup', $verifikator->fresh()->status_loket);

        $this->actingAs($verifikator)
            ->postJson(route('admin.panggil'), ['ticket_id' => $ticket->id])
            ->assertOk()
            ->assertJson(['success' => true, 'ticket_number' => 'P001', 'loket_number' => 1]);

        $this->assertEquals('aktif', $verifikator->fresh()->status_loket);
        $this->assertEquals('dipanggil', $ticket->fresh()->status);

        // 3. Layar API reflects active call & active loket
        $this->getJson(route('api.layar'))
            ->assertOk()
            ->assertJsonPath('active.ticket', 'P001')
            ->assertJsonPath('active.loket', 1)
            ->assertJsonPath('lokets.0.loket', 1)
            ->assertJsonPath('lokets.0.ticket', 'P001');

        // 4. Menghadap & Selesai
        $this->actingAs($verifikator)
            ->post(route('admin.menghadap'), ['ticket_id' => $ticket->id])
            ->assertRedirect();
        $this->assertEquals('menghadap', $ticket->fresh()->status);

        $this->actingAs($verifikator)
            ->post(route('admin.selesaikan'), ['ticket_id' => $ticket->id, 'catatan_loket' => 'Lengkap'])
            ->assertRedirect();
        $this->assertEquals('selesai', $ticket->fresh()->status);
        $this->assertEquals('Lengkap', $ticket->fresh()->catatan_loket);
    }
}
