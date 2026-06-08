<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\SiakadInitialSuperAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiakadBootstrapSuperAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_provisions_bashar_as_super_admin(): void
    {
        $this->seed(SiakadInitialSuperAdminSeeder::class);

        $user = User::query()->where('email', 'bashar.ypgs@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->is_allowed_login);
        $this->assertSame('bashar.ypgs@gmail.com', $user->siakad_login);
        $this->assertTrue($user->hasRole('super_admin'));
    }

    public function test_bootstrap_removes_demo_accounts(): void
    {
        $this->seed();

        User::factory()->create([
            'email' => 'superadmin@sipepeng.test',
            'is_allowed_login' => true,
        ]);

        $this->seed(SiakadInitialSuperAdminSeeder::class);

        $this->assertNull(User::query()->where('email', 'superadmin@sipepeng.test')->first());
    }
}
