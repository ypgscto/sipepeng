<?php

namespace Tests\Feature\Letter;

use App\Models\Letter\Letter;
use App\Models\Lppm\LetterType;
use App\Models\Lppm\ResearchScheme;
use App\Models\Research\ResearchProposal;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LetterWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    public function test_dosen_can_create_letter_draft(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.letter', 'Dosen Letter');
        $type = LetterType::query()->where('code', 'surat_permohonan_data')->firstOrFail();

        $response = $this->actingAs($dosen)->post(route('admin.letters.store'), [
            'letter_type_id' => $type->id,
            'perihal' => 'Surat Permohonan Data Test',
            'letter_date' => now()->toDateString(),
            'recipient_external_name' => 'Bapak Rektor',
            'recipient_external_institution' => 'Universitas Test',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lppm_letters', [
            'perihal' => 'Surat Permohonan Data Test',
            'status' => 'draft',
        ]);
    }

    public function test_admin_can_issue_approved_letter(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.letter', 'Admin Letter');
        $type = LetterType::query()->where('code', 'surat_tugas_penelitian')->firstOrFail();

        $letter = Letter::query()->create([
            'internal_number' => 'DRAFT/SRT/2026/9999',
            'letter_type_id' => $type->id,
            'letter_prefix_snapshot' => 'LPPM/ST-P',
            'perihal' => 'Surat Issue Test',
            'letter_date' => now(),
            'status' => 'approved',
            'current_stage' => 'approval',
            'created_by' => $admin->id,
            'updated_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.letters.issue', $letter));

        $response->assertRedirect();
        $letter->refresh();
        $this->assertSame('issued', $letter->status);
        $this->assertNotNull($letter->letter_number);
        $this->assertNotNull($letter->file_pdf);
    }

    public function test_letter_linked_to_research_proposal(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.link', 'Dosen Link');
        $type = LetterType::query()->where('code', 'surat_tugas_penelitian')->firstOrFail();

        $proposal = ResearchProposal::query()->create([
            'proposal_number' => 'RES/2026/TEST',
            'tahun_akademik_id' => 'TA1',
            'tahun_akademik_nama_snapshot' => '2025/2026',
            'semester_id' => 'S1',
            'semester_nama_snapshot' => 'Ganjil',
            'prodi_id' => 'P001',
            'prodi_nama_snapshot' => 'Prodi Test',
            'skema_id' => ResearchScheme::query()->firstOrFail()->id,
            'judul' => 'Judul Penelitian Test',
            'ketua_dosen_id' => 'dosen.link',
            'ketua_dosen_nama_snapshot' => 'Dosen Link',
            'ketua_user_id' => $dosen->id,
            'status' => 'approved',
            'current_stage' => 'decision',
            'created_by' => $dosen->id,
            'updated_by' => $dosen->id,
        ]);

        $this->actingAs($dosen)->post(route('admin.letters.store'), [
            'letter_type_id' => $type->id,
            'perihal' => 'ST dari Proposal',
            'letter_date' => now()->toDateString(),
            'research_proposal_id' => $proposal->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('lppm_letters', [
            'research_proposal_id' => $proposal->id,
            'proposal_judul_snapshot' => 'Judul Penelitian Test',
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
