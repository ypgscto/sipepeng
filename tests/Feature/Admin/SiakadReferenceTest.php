<?php

namespace Tests\Feature\Admin;

use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use App\Services\Siakad\SiakadReferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class SiakadReferenceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::factory()->create([
            'is_active' => true,
            'is_allowed_login' => true,
        ]);

        $role = SipepengRole::query()->where('code', 'admin_lppm')->firstOrFail();
        SipepengUserRoleMapping::query()->create([
            'user_id' => $this->admin->id,
            'role_id' => $role->id,
            'is_primary' => true,
            'is_active' => true,
            'assigned_at' => now(),
        ]);
    }

    public function test_reference_page_requires_auth(): void
    {
        $this->get(route('admin.siakad-reference.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_reference_page(): void
    {
        $reference = Mockery::mock(SiakadReferenceService::class);
        $reference->shouldReceive('forTab')->once()->andReturn([
            'tab' => 'prodi',
            'records' => [
                ['siakad_id' => 'P01', 'kode_prodi' => 'P01', 'nama_prodi' => 'Keperawatan', 'jenjang' => 'S1', 'is_active' => true],
            ],
            'total' => 1,
            'filtered_total' => 1,
            'meta' => [
                'source' => 'api',
                'fetched_at' => now(),
                'is_stale' => false,
                'cache_enabled' => true,
                'ttl_minutes' => 360,
            ],
            'error' => null,
            'filters' => [],
            'prodi_options' => [],
        ]);
        $this->app->instance(SiakadReferenceService::class, $reference);

        $this->actingAs($this->admin)
            ->get(route('admin.siakad-reference.index', ['tab' => 'prodi']))
            ->assertOk()
            ->assertSee('Keperawatan');
    }

    public function test_reference_page_shows_error_when_api_fails(): void
    {
        $reference = Mockery::mock(SiakadReferenceService::class);
        $reference->shouldReceive('forTab')->once()->andReturn([
            'tab' => 'dosen',
            'records' => [],
            'total' => 0,
            'filtered_total' => 0,
            'meta' => [
                'source' => 'none',
                'fetched_at' => null,
                'is_stale' => false,
                'cache_enabled' => true,
                'ttl_minutes' => 360,
            ],
            'error' => 'Koneksi ke SIAKAD-API gagal.',
            'filters' => [],
            'prodi_options' => [],
        ]);
        $this->app->instance(SiakadReferenceService::class, $reference);

        $this->actingAs($this->admin)
            ->get(route('admin.siakad-reference.index', ['tab' => 'dosen']))
            ->assertOk()
            ->assertSee('Gagal memuat data dari SIAKAD-API');
    }
}
