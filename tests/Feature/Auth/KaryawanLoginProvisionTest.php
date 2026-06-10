<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Services\Siakad\SiakadUserProvisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaryawanLoginProvisionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_karyawan_profile_with_zero_siakad_user_id_uses_login_as_id(): void
    {
        $user = app(SiakadUserProvisionService::class)->provisionFromLoginProfile([
            'login' => 'kalemlp2m',
            'nama' => 'Kepala LP2M',
            'jenis_user' => '5',
            'level_id' => '91',
            'auth_source' => 'karyawan',
            'siakad_user_id' => 0,
            '_form_login' => 'kalemlp2m',
        ]);

        $this->assertSame('kalemlp2m', $user->siakad_user_id);
        $this->assertSame('kalemlp2m', $user->siakad_login);
        $this->assertTrue($user->hasRole('ketua_lppm'));
    }

    public function test_existing_user_matched_by_login_when_siakad_user_id_was_zero(): void
    {
        User::factory()->create([
            'siakad_user_id' => 'kalemlp2m',
            'siakad_login' => 'kalemlp2m',
            'email' => 'kalemlp2m@stikesgunungsari.ac.id',
            'is_allowed_login' => true,
        ]);

        $user = app(SiakadUserProvisionService::class)->provisionFromLoginProfile([
            'login' => 'kalemlp2m',
            'nama' => 'Kepala LP2M Updated',
            'jenis_user' => '5',
            'level_id' => '91',
            'siakad_user_id' => 0,
        ]);

        $this->assertSame('kalemlp2m', $user->siakad_user_id);
        $this->assertSame('Kepala LP2M Updated', $user->name);
    }

    public function test_login_prefers_email_match_over_duplicate_siakad_user_id_stub(): void
    {
        $canonical = User::factory()->create([
            'name' => 'Sri Wahyuni Bahrun',
            'email' => 'swbahrun@gmail.com',
            'siakad_login' => 'swbahrun@gmail.com',
            'siakad_user_id' => 'swbahrun@gmail.com',
            'is_allowed_login' => true,
        ]);

        User::factory()->create([
            'name' => 'Stub Sync',
            'email' => 'stub-sync@stikesgunungsari.ac.id',
            'siakad_user_id' => '148',
            'siakad_login' => '148',
        ]);

        $user = app(SiakadUserProvisionService::class)->provisionFromLoginProfile([
            'login' => 'swbahrun@gmail.com',
            'nama' => 'Sri Wahyuni Bahrun',
            'email' => 'swbahrun@gmail.com',
            'jenis_user' => '8',
            'level_id' => '91',
            'siakad_user_id' => 148,
            '_form_login' => 'swbahrun@gmail.com',
        ]);

        $this->assertSame($canonical->id, $user->id);
        $this->assertSame('148', $user->siakad_user_id);
        $this->assertSame('swbahrun@gmail.com', $user->email);
        $this->assertSame(1, User::query()->where('email', 'swbahrun@gmail.com')->count());
    }
}
