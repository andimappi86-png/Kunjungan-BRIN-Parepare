<?php

namespace Tests\Feature;

use App\Models\Visit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function guest_cannot_access_dashboard(): void
    {
        $this->get(route('dashboard'))
             ->assertRedirect(route('login'));
    }

    #[Test]
    public function authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();
        Visit::factory()->count(5)->create();

        $this->actingAs($user)
             ->get(route('dashboard'))
             ->assertOk()
             ->assertSee('Total Pengajuan');
    }

    #[Test]
    public function dashboard_shows_correct_statistics(): void
    {
        $user = User::factory()->create();
        Visit::factory()->create(['status' => Visit::STATUS_MENUNGGU]);
        Visit::factory()->create(['status' => Visit::STATUS_DISETUJUI]);
        Visit::factory()->create(['status' => Visit::STATUS_DITOLAK]);

        $response = $this->actingAs($user)->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewHas('total', 3);
        $response->assertViewHas('waiting', 1);
        $response->assertViewHas('approved', 1);
        $response->assertViewHas('rejected', 1);
    }

    #[Test]
    public function update_status_and_schedule_works(): void
    {
        $user = User::factory()->create();
        $visit = Visit::factory()->create(['status' => Visit::STATUS_MENUNGGU]);

        $this->actingAs($user)
             ->patch(route('visits.update', $visit->id), [
                 'status' => Visit::STATUS_DISETUJUI,
                 'scheduled_date' => now()->addDays(3)->toDateString(),
             ])
             ->assertRedirect();

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'status' => Visit::STATUS_DISETUJUI,
        ]);
    }

    #[Test]
    public function invalid_status_is_rejected(): void
    {
        $user = User::factory()->create();
        $visit = Visit::factory()->create(['status' => Visit::STATUS_MENUNGGU]);

        $this->actingAs($user)
             ->patch(route('visits.update', $visit->id), [
                 'status' => 'INVALID_STATUS',
             ])
             ->assertSessionHasErrors('status');
    }

    #[Test]
    public function whatsapp_status_toggle_works(): void
    {
        $user = User::factory()->create();
        $visit = Visit::factory()->create(['whatsapp_status' => Visit::WA_BELUM]);

        $this->actingAs($user)
             ->patch(route('visits.whatsapp', $visit->id))
             ->assertRedirect();

        $this->assertDatabaseHas('visits', [
            'id' => $visit->id,
            'whatsapp_status' => Visit::WA_TERKIRIM,
        ]);
    }

    #[Test]
    public function search_filter_works(): void
    {
        $user = User::factory()->create();
        Visit::factory()->create(['applicant_name' => 'Budi Santoso', 'status' => Visit::STATUS_MENUNGGU]);
        Visit::factory()->create(['applicant_name' => 'Sari Dewi', 'status' => Visit::STATUS_MENUNGGU]);

        $response = $this->actingAs($user)
                         ->get(route('dashboard', ['search' => 'Budi']));
        $response->assertSee('Budi Santoso');
        $response->assertDontSee('Sari Dewi');
    }

    #[Test]
    public function status_filter_works(): void
    {
        $user = User::factory()->create();
        Visit::factory()->create(['applicant_name' => 'KunjunganUnikDisetujui', 'status' => Visit::STATUS_DISETUJUI]);
        Visit::factory()->create(['applicant_name' => 'KunjunganUnikDitolak', 'status' => Visit::STATUS_DITOLAK]);

        $response = $this->actingAs($user)
                         ->get(route('dashboard', ['status' => Visit::STATUS_DISETUJUI]));
        $response->assertSee('KunjunganUnikDisetujui');
        $response->assertDontSee('KunjunganUnikDitolak');
    }

    #[Test]
    public function logout_invalidates_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
             ->post(route('logout'))
             ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
