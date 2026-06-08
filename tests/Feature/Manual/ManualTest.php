<?php

namespace Tests\Feature\Manual;

use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_cannot_access_manual(): void
    {
        $this->get(route('manual.index'))->assertRedirect(route('login'));
    }

    public function test_dosen_can_access_manual_index_and_module(): void
    {
        $user = $this->userWithRole('dosen');

        $this->actingAs($user)
            ->get(route('manual.index'))
            ->assertOk()
            ->assertSee('Panduan')
            ->assertSee('Penelitian');

        $this->actingAs($user)
            ->get(route('manual.show', 'penelitian'))
            ->assertOk()
            ->assertSee('siklus proposal penelitian');
    }

    public function test_unknown_module_returns_404(): void
    {
        $user = $this->userWithRole('dosen');

        $this->actingAs($user)
            ->get(route('manual.show', 'tidak-ada'))
            ->assertNotFound();
    }

    public function test_pdf_download_returns_pdf(): void
    {
        $user = $this->userWithRole('dosen');

        $response = $this->actingAs($user)
            ->get(route('manual.pdf', 'umum'));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $response->headers->get('content-type'));
    }

    protected function userWithRole(string $roleCode): User
    {
        $role = SipepengRole::query()->where('code', $roleCode)->firstOrFail();

        $user = User::factory()->create([
            'is_allowed_login' => true,
            'is_active' => true,
        ]);

        SipepengUserRoleMapping::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'is_primary' => true,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        return $user;
    }
}
