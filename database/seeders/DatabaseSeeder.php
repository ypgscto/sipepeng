<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SipepengRoleSeeder::class,
            AppSettingSeeder::class,
            LppmMasterSeeder::class,
            LetterTypeSeeder::class,
        ]);

        if (config('sipepeng_bootstrap.seed_on_migrate', false)) {
            $this->call(SiakadInitialSuperAdminSeeder::class);
        }
    }
}
