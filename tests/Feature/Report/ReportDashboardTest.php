<?php

namespace Tests\Feature\Report;

use App\Models\Lppm\ResearchScheme;
use App\Models\Research\ResearchProposal;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_admin_can_view_dashboard_with_statistics(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.report', 'Admin Report');
        $dosen = $this->userWithRole('dosen', 'dosen.report', 'Dosen Report');

        ResearchProposal::query()->create(array_merge($this->proposalPayload($dosen), [
            'status' => 'approved',
            'total_rab' => 5000000,
            'submitted_at' => now(),
        ]));

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Penelitian Tahun Berjalan')
            ->assertSee('Laporan LPPM');
    }

    public function test_admin_can_view_reports_index_and_research_report(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.lap', 'Admin Lap');
        $dosen = $this->userWithRole('dosen', 'dosen.lap', 'Dosen Lap');

        ResearchProposal::query()->create(array_merge($this->proposalPayload($dosen), [
            'status' => 'approved',
            'submitted_at' => now(),
        ]));

        $this->actingAs($admin)
            ->get(route('admin.reports.index'))
            ->assertOk()
            ->assertSee('Penelitian');

        $this->actingAs($admin)
            ->get(route('admin.reports.show', 'research'))
            ->assertOk()
            ->assertSee('Judul Penelitian Test');
    }

    public function test_admin_can_export_research_excel(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.xlsx', 'Admin Xlsx');
        $dosen = $this->userWithRole('dosen', 'dosen.xlsx', 'Dosen Xlsx');

        ResearchProposal::query()->create(array_merge($this->proposalPayload($dosen), [
            'status' => 'approved',
            'submitted_at' => now(),
        ]));

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export.excel', 'research'));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheet', (string) $response->headers->get('content-type'));
    }

    public function test_dosen_cannot_export_reports(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.noexport', 'Dosen No Export');

        $this->actingAs($dosen)
            ->get(route('admin.reports.export.excel', 'research'))
            ->assertForbidden();
    }

    public function test_accreditation_report_shows_indicators(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.akred', 'Admin Akred');

        $this->actingAs($admin)
            ->get(route('admin.reports.show', 'accreditation'))
            ->assertOk()
            ->assertSee('IND-PEN-01')
            ->assertSee('Indikator Akreditasi');
    }

    /**
     * @return array<string, mixed>
     */
    protected function proposalPayload(User $ketua): array
    {
        return [
            'proposal_number' => 'PNL/'.now()->year.'/'.random_int(1000, 9999),
            'tahun_akademik_id' => '1',
            'tahun_akademik_nama_snapshot' => '2025/2026',
            'semester_id' => '1',
            'semester_nama_snapshot' => '2025/2026 — Ganjil',
            'prodi_id' => 'P001',
            'prodi_nama_snapshot' => 'Prodi Test',
            'skema_id' => ResearchScheme::query()->first()->id,
            'judul' => 'Judul Penelitian Test',
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
