<?php

namespace Tests\Feature\Settings;

use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\BackupLog;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use App\Services\DatabaseBackupService;
use App\Support\Siakad\SiakadConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_dosen_cannot_access_settings(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.settings', 'Dosen Settings');

        $this->actingAs($dosen)
            ->get(route('admin.settings.index'))
            ->assertRedirect(route('access.denied'));
    }

    public function test_admin_lppm_can_view_settings_hub(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.settings', 'Admin Settings');

        $this->actingAs($admin)
            ->get(route('admin.settings.index'))
            ->assertOk()
            ->assertSee('Pengaturan SiPepeng');
    }

    public function test_admin_can_update_general_settings_and_footer(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.general', 'Admin General');

        $this->actingAs($admin)
            ->put(route('admin.settings.general.update'), [
                'app_name' => 'SiPepeng Test',
                'app_subtitle' => 'Subtitle Test',
                'institution_name' => 'STIKES Test',
                'institution_url' => 'https://example.test',
                'institution_url_label' => 'example.test',
                'module' => 'LPPM',
                'footer_credit' => 'YPGS IT Division, 2026',
            ])
            ->assertRedirect(route('admin.settings.general.edit'));

        $this->assertSame('SiPepeng Test', AppSetting::query()->where('key', 'app_name')->value('value'));
        $this->assertSame('YPGS IT Division, 2026', AppSetting::query()->where('key', 'footer_credit')->value('value'));

        $this->assertTrue(
            ActivityLog::query()->where('event', 'settings_updated')->where('log_name', 'security')->exists()
        );
    }

    public function test_admin_can_upload_institution_logo(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.logo', 'Admin Logo');

        $file = UploadedFile::fake()->image('logo.png', 120, 120);

        $this->actingAs($admin)
            ->post(route('admin.settings.logo.update'), ['logo' => $file])
            ->assertRedirect(route('admin.settings.logo.edit'));

        $path = AppSetting::query()->where('key', 'logo_path')->value('value');
        $this->assertIsString($path);
        $this->assertFileExists(public_path($path));
    }

    public function test_siakad_settings_page_does_not_expose_token(): void
    {
        config(['siakad.token' => 'super-secret-token']);

        $admin = $this->userWithRole('admin_lppm', 'admin.siakad', 'Admin Siakad');

        $this->actingAs($admin)
            ->put(route('admin.settings.siakad.update'), [
                'base_url' => 'https://siakad.test',
                'api_token_new' => 'new-encrypted-token-value',
                'cache_enabled' => '1',
                'cache_ttl_minutes' => 120,
                'timeout' => 60,
            ])
            ->assertRedirect(route('admin.settings.siakad.edit'));

        $response = $this->actingAs($admin)->get(route('admin.settings.siakad.edit'));
        $response->assertOk();
        $response->assertDontSee('super-secret-token');
        $response->assertDontSee('new-encrypted-token-value');
        $response->assertSee('Terkonfigurasi');

        $this->assertSame('new-encrypted-token-value', SiakadConfig::token());
    }

    public function test_admin_can_update_role_mapping(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.roles', 'Admin Roles');
        $role = SipepengRole::query()->where('code', 'reviewer')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.settings.roles.update', $role), [
                'siakad_map_type' => 'jenis_user',
                'siakad_map_key' => '99',
            ])
            ->assertRedirect(route('admin.settings.roles.index'));

        $role->refresh();
        $this->assertSame('jenis_user', $role->siakad_map_type);
        $this->assertSame('99', $role->siakad_map_key);
    }

    public function test_admin_lppm_cannot_access_backup(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.nobackup', 'Admin No Backup');

        $this->actingAs($admin)
            ->get(route('admin.settings.backup.index'))
            ->assertRedirect(route('access.denied'));
    }

    public function test_super_admin_can_create_database_backup(): void
    {
        Storage::fake('local');

        $superAdmin = $this->userWithRole('super_admin', 'super.backup', 'Super Backup');
        $relativePath = 'backups/sipepeng_backup_test.sqlite';

        Storage::disk('local')->put($relativePath, 'backup-content');

        $log = BackupLog::query()->create([
            'filename' => 'sipepeng_backup_test.sqlite',
            'disk' => 'local',
            'path' => $relativePath,
            'size_bytes' => strlen('backup-content'),
            'driver' => 'sqlite',
            'status' => 'completed',
            'created_by' => $superAdmin->id,
            'completed_at' => now(),
        ]);

        $this->mock(DatabaseBackupService::class, function ($mock) use ($superAdmin, $log, $relativePath): void {
            $mock->shouldReceive('create')
                ->once()
                ->withArgs(fn (User $user) => $user->is($superAdmin))
                ->andReturn([
                    'log' => $log,
                    'absolute_path' => Storage::disk('local')->path($relativePath),
                ]);

            $mock->shouldReceive('resolveAbsolutePath')
                ->withArgs(fn (BackupLog $backup) => $backup->is($log))
                ->andReturn(Storage::disk('local')->path($relativePath));
        });

        $this->actingAs($superAdmin)
            ->post(route('admin.settings.backup.store'))
            ->assertRedirect(route('admin.settings.backup.index'));

        $this->actingAs($superAdmin)
            ->get(route('admin.settings.backup.download', $log))
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
