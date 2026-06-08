<?php

namespace Tests\Feature\Lppm;

use App\Models\Lppm\FundingSource;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LppmMasterCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_master_dashboard_requires_view_role(): void
    {
        $user = $this->createUserWithRole('dosen');

        $this->actingAs($user)
            ->get(route('admin.master.index'))
            ->assertRedirect(route('access.denied'));
    }

    public function test_ketua_lppm_can_view_but_not_create_master_data(): void
    {
        $user = $this->createUserWithRole('ketua_lppm');

        $this->actingAs($user)
            ->get(route('admin.master.funding-sources.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('admin.master.funding-sources.create'))
            ->assertRedirect(route('access.denied'));
    }

    public function test_admin_lppm_can_crud_funding_source(): void
    {
        $user = $this->createUserWithRole('admin_lppm');

        $this->actingAs($user)
            ->get(route('admin.master.funding-sources.create'))
            ->assertOk();

        $response = $this->actingAs($user)
            ->post(route('admin.master.funding-sources.store'), [
                'code' => 'hibah_test',
                'name' => 'Hibah Uji',
                'source_category' => 'external',
                'institution_name' => 'LPPM Test',
                'sort_order' => 5,
                'is_active' => 1,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lppm_funding_sources', [
            'code' => 'hibah_test',
            'name' => 'Hibah Uji',
        ]);

        $record = FundingSource::query()->where('code', 'hibah_test')->firstOrFail();

        $this->actingAs($user)
            ->get(route('admin.master.funding-sources.index', ['q' => 'Hibah']))
            ->assertOk()
            ->assertSee('Hibah Uji');

        $this->actingAs($user)
            ->patch(route('admin.master.funding-sources.toggle-active', $record))
            ->assertRedirect();

        $record->refresh();
        $this->assertFalse($record->is_active);

        $this->actingAs($user)
            ->delete(route('admin.master.funding-sources.destroy', $record))
            ->assertRedirect(route('admin.master.funding-sources.index'));

        $this->assertSoftDeleted('lppm_funding_sources', ['id' => $record->id]);

        $this->actingAs($user)
            ->patch(route('admin.master.funding-sources.restore', $record->id))
            ->assertRedirect();

        $record->refresh();
        $this->assertNull($record->deleted_at);
    }

    protected function createUserWithRole(string $roleCode): User
    {
        $role = SipepengRole::query()->where('code', $roleCode)->firstOrFail();

        $user = User::factory()->create([
            'is_active' => true,
            'is_allowed_login' => true,
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
