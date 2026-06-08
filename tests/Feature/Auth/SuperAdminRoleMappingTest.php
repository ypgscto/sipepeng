<?php

namespace Tests\Feature\Auth;

use App\Models\SipepengRole;
use App\Models\User;
use App\Services\Siakad\SiakadAuthApiService;
use App\Services\Siakad\SiakadRoleMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SuperAdminRoleMappingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_override_maps_bashar_to_super_admin(): void
    {
        $codes = app(SiakadRoleMapper::class)->resolveRoleCodes([
            'login' => 'bashar.ypgs@gmail.com',
            'email' => 'bashar.ypgs@gmail.com',
            'jenis_user' => '0',
        ]);

        $this->assertContains('super_admin', $codes);
    }

    public function test_siakad_superadmin_role_field_maps_to_super_admin(): void
    {
        $codes = app(SiakadRoleMapper::class)->resolveRoleCodes([
            'login' => 'other.user',
            'jenis_role' => 'superadmin',
        ]);

        $this->assertContains('super_admin', $codes);
    }

    public function test_bashar_can_login_with_siakad_override(): void
    {
        $api = Mockery::mock(SiakadAuthApiService::class);
        $api->shouldReceive('attemptLogin')->once()->andReturn([
            'login' => 'bashar.ypgs@gmail.com',
            'email' => 'bashar.ypgs@gmail.com',
            'nama' => 'Bashar YPGS',
            'jenis_role' => 'superadmin',
        ]);
        $this->app->instance(SiakadAuthApiService::class, $api);

        $this->post(route('login'), [
            'login' => 'bashar.ypgs@gmail.com',
            'password' => 'secret',
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'bashar.ypgs@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->is_allowed_login);
        $this->assertTrue($user->hasRole('super_admin'));
    }

    public function test_bashar_can_login_when_siakad_profile_lacks_email(): void
    {
        $api = Mockery::mock(SiakadAuthApiService::class);
        $api->shouldReceive('attemptLogin')->once()->andReturn([
            'login' => 'bashar',
            'nama' => 'Bashar YPGS',
            'jenis_user' => '0',
        ]);
        $this->app->instance(SiakadAuthApiService::class, $api);

        $this->post(route('login'), [
            'login' => 'bashar.ypgs@gmail.com',
            'password' => 'secret',
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'bashar.ypgs@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('super_admin'));
    }

    public function test_existing_provisioned_user_can_login_when_siakad_role_map_empty(): void
    {
        $this->seed(\Database\Seeders\SiakadInitialSuperAdminSeeder::class);

        $api = Mockery::mock(SiakadAuthApiService::class);
        $api->shouldReceive('attemptLogin')->once()->andReturn([
            'login' => 'unknown-ref-999',
            'nama' => 'Bashar YPGS',
            'jenis_user' => '0',
        ]);
        $this->app->instance(SiakadAuthApiService::class, $api);

        $this->post(route('login'), [
            'login' => 'bashar.ypgs@gmail.com',
            'password' => 'secret',
        ])->assertRedirect(route('dashboard'));

        $this->assertTrue(
            User::query()->where('email', 'bashar.ypgs@gmail.com')->first()?->hasRole('super_admin') ?? false
        );
    }
}
