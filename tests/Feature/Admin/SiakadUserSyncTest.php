<?php

namespace Tests\Feature\Admin;

use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use App\Services\Siakad\SiakadApiService;
use App\Services\Siakad\SiakadUserSyncService;
use App\Support\Siakad\SiakadResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SiakadUserSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_sync_imports_karyawan_only_user_with_ketua_lppm_role(): void
    {
        $api = Mockery::mock(SiakadApiService::class);
        $api->shouldReceive('fetchAll')
            ->once()
            ->with(SiakadResource::LOGIN_USERS)
            ->andReturn([
                'records' => [[
                    'siakad_user_id' => 'kalemlp2m',
                    'siakad_login' => 'kalemlp2m',
                    'email' => 'kalemlp2m@stikesgunungsari.ac.id',
                    'name' => 'Kepala LP2M',
                    'user_category' => 'pegawai',
                    'jenis_user' => '5',
                    'level_id' => '91',
                    'is_active' => true,
                    'sipepeng_roles' => ['ketua_lppm'],
                    'auth_source' => 'karyawan',
                ]],
                'total' => 1,
                'correlation_id' => 'test',
            ]);
        $this->app->instance(SiakadApiService::class, $api);

        $result = app(SiakadUserSyncService::class)->syncAll();

        $this->assertSame(1, $result['created']);
        $user = User::query()->where('siakad_login', 'kalemlp2m')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('ketua_lppm'));
        $this->assertFalse($user->is_allowed_login);
    }

    public function test_user_sync_page_requires_settings_access(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin@test.com', 'Admin LPPM');

        $this->actingAs($admin)
            ->get(route('admin.settings.user-sync.index'))
            ->assertOk();
    }

    protected function userWithRole(string $roleCode, string $login, string $name): User
    {
        $role = SipepengRole::query()->where('code', $roleCode)->firstOrFail();
        $user = User::factory()->create([
            'name' => $name,
            'siakad_login' => $login,
            'siakad_user_id' => $login,
            'is_active' => true,
            'is_allowed_login' => true,
            'password' => 'password',
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
