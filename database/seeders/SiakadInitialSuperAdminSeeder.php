<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Siakad\SiakadUserProvisionService;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class SiakadInitialSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SipepengRoleSeeder::class);

        if (config('sipepeng_bootstrap.purge_demo_accounts', true)) {
            $removed = User::query()
                ->where('email', 'like', '%@sipepeng.test')
                ->delete();

            if ($removed > 0) {
                $this->command?->info("Menghapus {$removed} akun demo @sipepeng.test.");
            }
        }

        $profiles = config('sipepeng_bootstrap.super_admins', []);
        if ($profiles === []) {
            $this->command?->warn('Tidak ada super admin bootstrap di config/sipepeng_bootstrap.php.');

            return;
        }

        $provision = app(SiakadUserProvisionService::class);

        foreach ($profiles as $profile) {
            $login = (string) ($profile['login'] ?? $profile['email'] ?? '');

            try {
                $user = $provision->provisionFromLoginProfile($profile);
            } catch (InvalidArgumentException $e) {
                $this->command?->error("Gagal provision {$login}: {$e->getMessage()}");

                continue;
            }

            $user->forceFill([
                'is_allowed_login' => true,
                'is_active' => true,
            ])->save();

            $this->command?->info("Super admin Siakad: {$user->email} [super_admin] — login via SIAKAD-GS.");
        }
    }
}
