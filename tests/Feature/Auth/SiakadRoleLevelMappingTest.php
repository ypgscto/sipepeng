<?php

namespace Tests\Feature\Auth;

use App\Models\SipepengRole;
use App\Services\Siakad\SiakadRoleMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiakadRoleLevelMappingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_level_id_91_maps_to_ketua_lppm(): void
    {
        $roles = app(SiakadRoleMapper::class)->resolveRoleCodes([
            'login' => 'kalemlp2m',
            'jenis_user' => '5',
            'level_id' => '91',
            'auth_source' => 'karyawan',
        ]);

        $this->assertSame(['ketua_lppm'], $roles);
    }

    public function test_jenis_user_5_maps_to_pimpinan_without_level_id(): void
    {
        $roles = app(SiakadRoleMapper::class)->resolveRoleCodes([
            'login' => 'kepala@test.com',
            'jenis_user' => '5',
        ]);

        $this->assertSame(['pimpinan'], $roles);
    }

    public function test_level_id_91_alone_maps_to_ketua_lppm_via_config(): void
    {
        SipepengRole::query()
            ->where('code', 'ketua_lppm')
            ->update(['siakad_map_type' => null, 'siakad_map_key' => null]);

        $roles = app(SiakadRoleMapper::class)->resolveRoleCodes([
            'login' => 'kalemlp2m',
            'level_id' => '91',
            'auth_source' => 'karyawan',
        ]);

        $this->assertSame(['ketua_lppm'], $roles);
    }
}
