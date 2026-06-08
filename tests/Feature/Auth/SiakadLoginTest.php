<?php

namespace Tests\Feature\Auth;

use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use App\Services\Siakad\SiakadAuthApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SiakadLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_is_accessible_for_guests(): void
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_siakad_user_blocked_when_not_allowed_login(): void
    {
        $api = Mockery::mock(SiakadAuthApiService::class);
        $api->shouldReceive('attemptLogin')->once()->andReturn([
            'login' => 'dosen.test',
            'nama' => 'Dosen Test',
            'jenis_user' => '7',
            'email' => 'dosen.test@stikesgunungsari.ac.id',
        ]);
        $this->app->instance(SiakadAuthApiService::class, $api);

        $this->post(route('login'), [
            'login' => 'dosen.test',
            'password' => 'secret',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();

        $user = User::query()->where('siakad_login', 'dosen.test')->first();
        $this->assertNotNull($user);
        $this->assertFalse($user->is_allowed_login);
    }

    public function test_siakad_user_can_login_when_allowed(): void
    {
        $api = Mockery::mock(SiakadAuthApiService::class);
        $api->shouldReceive('attemptLogin')->once()->andReturn([
            'login' => 'admin.lppm.siakad',
            'nama' => 'Admin LPPM',
            'jenis_user' => '8',
            'email' => 'admin.lppm.siakad@stikesgunungsari.ac.id',
        ]);
        $this->app->instance(SiakadAuthApiService::class, $api);

        $role = SipepengRole::query()->where('code', 'admin_lppm')->firstOrFail();

        $user = User::query()->create([
            'name' => 'Admin LPPM',
            'email' => 'admin.lppm.siakad@stikesgunungsari.ac.id',
            'password' => 'placeholder',
            'siakad_user_id' => 'admin.lppm.siakad',
            'siakad_login' => 'admin.lppm.siakad',
            'jenis_user' => '8',
            'user_category' => 'pegawai',
            'is_active' => true,
            'is_allowed_login' => true,
        ]);

        SipepengUserRoleMapping::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'is_primary' => true,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $this->post(route('login'), [
            'login' => 'admin.lppm.siakad',
            'password' => 'secret',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_logout_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'is_active' => true,
            'is_allowed_login' => true,
        ]);

        $role = SipepengRole::query()->where('code', 'dosen')->firstOrFail();
        SipepengUserRoleMapping::query()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'is_primary' => true,
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->actingAs($user)
            ->get(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
