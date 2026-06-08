<?php

namespace App\Console\Commands;

use Database\Seeders\SiakadInitialSuperAdminSeeder;
use Illuminate\Console\Command;

class SyncSiakadSuperAdminCommand extends Command
{
    protected $signature = 'sipepeng:sync-siakad-super-admin {--keep-demo : Jangan hapus akun @sipepeng.test}';

    protected $description = 'Hapus akun demo dan sinkronkan super admin awal dari profil SIAKAD-GS';

    public function handle(): int
    {
        if ($this->option('keep-demo')) {
            config(['sipepeng_bootstrap.purge_demo_accounts' => false]);
        }

        $this->call('db:seed', ['--class' => SiakadInitialSuperAdminSeeder::class]);

        $this->newLine();
        $this->info('Login awal: gunakan bashar.ypgs@gmail.com + password SIAKAD-GS di halaman /login.');

        return self::SUCCESS;
    }
}
