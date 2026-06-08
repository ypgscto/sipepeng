<?php

namespace Tests\Feature\ResearchEthics;

use App\Models\Lppm\ResearchScheme;
use App\Models\Research\ResearchProposal;
use App\Models\ResearchEthics\ResearchEthicsApplication;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResearchEthicsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    public function test_dosen_can_create_ethics_from_proposal(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.ethics', 'Dosen Ethics');
        $proposal = $this->makeProposal($dosen);

        $response = $this->actingAs($dosen)->post(route('admin.research-ethics.store'), [
            'research_proposal_id' => $proposal->id,
            'proposal_number_snapshot' => $proposal->proposal_number,
            'proposal_judul_snapshot' => $proposal->judul,
            'ketua_dosen_id' => $dosen->siakad_login,
            'ketua_dosen_nama_snapshot' => $dosen->name,
            'prodi_id' => $proposal->prodi_id,
            'prodi_nama_snapshot' => $proposal->prodi_nama_snapshot,
            'study_type' => 'observational',
            'risk_level' => 'minimal',
            'file_protocol' => UploadedFile::fake()->create('protocol.pdf', 100, 'application/pdf'),
            'file_ethics_application' => UploadedFile::fake()->create('form.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lppm_research_ethics_applications', [
            'research_proposal_id' => $proposal->id,
            'status' => 'draft',
        ]);
    }

    public function test_ethics_submit_moves_to_committee_review(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.ethics2', 'Dosen Ethics 2');
        $proposal = $this->makeProposal($dosen);
        $app = ResearchEthicsApplication::query()->create([
            'application_number' => 'ETB/2026/0001',
            'research_proposal_id' => $proposal->id,
            'proposal_number_snapshot' => $proposal->proposal_number,
            'proposal_judul_snapshot' => $proposal->judul,
            'ketua_dosen_id' => $dosen->siakad_login,
            'ketua_dosen_nama_snapshot' => $dosen->name,
            'ketua_user_id' => $dosen->id,
            'prodi_id' => $proposal->prodi_id,
            'prodi_nama_snapshot' => $proposal->prodi_nama_snapshot,
            'status' => 'draft',
            'file_protocol' => 'lppm/ethics/test/protocol.pdf',
            'file_ethics_application' => 'lppm/ethics/test/form.pdf',
            'created_by' => $dosen->id,
            'updated_by' => $dosen->id,
        ]);

        $this->actingAs($dosen)->post(route('admin.research-ethics.submit', $app))->assertRedirect();
        $app->refresh();
        $this->assertSame('committee_review', $app->status);
    }

    protected function makeProposal(User $dosen): ResearchProposal
    {
        return ResearchProposal::query()->create([
            'proposal_number' => 'PNL/'.now()->year.'/9999',
            'tahun_akademik_id' => '1',
            'tahun_akademik_nama_snapshot' => '2025/2026',
            'semester_id' => '1',
            'semester_nama_snapshot' => 'Ganjil',
            'prodi_id' => 'P001',
            'prodi_nama_snapshot' => 'Prodi Test',
            'skema_id' => ResearchScheme::query()->first()->id,
            'judul' => 'Proposal untuk Etik',
            'ketua_dosen_id' => $dosen->siakad_login,
            'ketua_dosen_nama_snapshot' => $dosen->name,
            'ketua_user_id' => $dosen->id,
            'status' => 'approved',
            'created_by' => $dosen->id,
            'updated_by' => $dosen->id,
        ]);
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
