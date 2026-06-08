<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_can_access_public_landing(): void
    {
        $this->get(route('public.landing'))
            ->assertOk()
            ->assertSee('Dashboard Umum')
            ->assertSee('Login SiPepeng')
            ->assertDontSee('ketua_dosen')
            ->assertDontSee('total_rab');
    }

    public function test_guest_can_access_public_dashboard(): void
    {
        $this->get(route('public.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Umum')
            ->assertSee('Statistik Tahun')
            ->assertSee('Grafik Kinerja')
            ->assertSee('Tentang SiPepeng')
            ->assertDontSee('ketua_dosen')
            ->assertDontSee('total_rab');
    }

    public function test_guest_internal_dashboard_redirects_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
