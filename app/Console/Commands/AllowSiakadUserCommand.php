<?php

namespace App\Console\Commands;

use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Console\Command;

class AllowSiakadUserCommand extends Command
{
    protected $signature = 'sipepeng:allow-siakad-user
                            {login : Email atau username Siakad}
                            {--role=pimpinan : Kode peran SiPepeng (mis. pimpinan, admin_lppm, dosen)}
                            {--name= : Nama tampilan jika user belum ada}';

    protected $description = 'Aktifkan login SiPepeng dan tetapkan peran untuk akun Siakad';

    public function handle(): int
    {
        $login = strtolower(trim($this->argument('login')));
        $roleCode = trim((string) $this->option('role'));

        if ($login === '') {
            $this->error('Login tidak boleh kosong.');

            return self::FAILURE;
        }

        $role = SipepengRole::query()->active()->where('code', $roleCode)->first();
        if ($role === null) {
            $this->error("Peran [{$roleCode}] tidak ditemukan atau nonaktif.");

            return self::FAILURE;
        }

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$login])
            ->orWhereRaw('LOWER(siakad_login) = ?', [$login])
            ->orWhereRaw('LOWER(siakad_user_id) = ?', [$login])
            ->first();

        if ($user === null) {
            $name = trim((string) $this->option('name'));
            if ($name === '') {
                $name = $login;
            }

            $user = User::query()->create([
                'name' => $name,
                'email' => filter_var($login, FILTER_VALIDATE_EMAIL)
                    ? $login
                    : $login.'@'.config('sipepeng_siakad_auth.email_domain', 'stikesgunungsari.ac.id'),
                'password' => bcrypt(str()->random(48)),
                'siakad_login' => $login,
                'siakad_user_id' => $login,
                'user_category' => 'pegawai',
                'jenis_user' => '5',
                'is_active' => true,
                'is_allowed_login' => true,
                'synced_at' => now(),
            ]);

            $this->warn('User belum pernah login — dibuat manual. Password tetap via SIAKAD-GS.');
        } else {
            $user->update([
                'is_active' => true,
                'is_allowed_login' => true,
            ]);
        }

        SipepengUserRoleMapping::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'role_id' => $role->id,
            ],
            [
                'is_primary' => true,
                'is_active' => true,
                'assigned_at' => now(),
                'notes' => 'Diaktifkan via sipepeng:allow-siakad-user',
            ],
        );

        $this->info("Akun {$user->email} diaktifkan dengan peran {$role->name} ({$role->code}).");
        $this->line('User login SiPepeng dengan password SIAKAD-GS yang sama.');

        return self::SUCCESS;
    }
}
