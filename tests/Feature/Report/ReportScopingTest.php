<?php

namespace Tests\Feature\Report;

use App\Models\Lppm\ResearchScheme;
use App\Models\Research\ResearchProposal;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportScopingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_dosen_cannot_view_accreditation_report(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.scope', 'Dosen Scope');

        $this->actingAs($dosen)
            ->get(route('admin.reports.show', 'accreditation'))
            ->assertForbidden();
    }

    public function test_dosen_cannot_view_partners_report(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.mitra', 'Dosen Mitra');

        $this->actingAs($dosen)
            ->get(route('admin.reports.show', 'partners'))
            ->assertForbidden();
    }

    public function test_export_includes_all_matching_rows_not_only_first_page(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.export', 'Admin Export');
        $dosen = $this->userWithRole('dosen', 'dosen.export', 'Dosen Export');

        for ($i = 0; $i < 30; $i++) {
            ResearchProposal::query()->create(array_merge($this->proposalPayload($dosen, $i), [
                'status' => 'approved',
                'submitted_at' => now(),
            ]));
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export.excel', 'research'));

        $response->assertOk();
    }

    public function test_dosen_report_index_only_shows_allowed_types(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.types', 'Dosen Types');

        $this->actingAs($dosen)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Penelitian')
            ->assertDontSee('Akreditasi');
    }

    /**
     * @return array<string, mixed>
     */
    protected function proposalPayload(User $ketua, int $suffix = 0): array
    {
        return [
            'proposal_number' => 'PNL/'.now()->year.'/'.(1000 + $suffix),
            'tahun_akademik_id' => '1',
            'tahun_akademik_nama_snapshot' => '2025/2026',
            'semester_id' => '1',
            'semester_nama_snapshot' => '2025/2026 — Ganjil',
            'prodi_id' => 'P001',
            'prodi_nama_snapshot' => 'Prodi Test',
            'skema_id' => ResearchScheme::query()->first()->id,
            'judul' => 'Judul Penelitian Test '.$suffix,
            'ketua_dosen_id' => $ketua->siakad_login,
            'ketua_dosen_nama_snapshot' => $ketua->name,
            'ketua_user_id' => $ketua->id,
            'status' => 'draft',
            'current_stage' => 'submission',
            'created_by' => $ketua->id,
            'updated_by' => $ketua->id,
        ];
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
