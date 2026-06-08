<?php

namespace Tests\Feature\Research;

use App\Models\Lppm\FocusArea;
use App\Models\Lppm\ResearchScheme;
use App\Models\Lppm\Reviewer;
use App\Models\Lppm\ScienceCluster;
use App\Models\Research\ResearchProposal;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ResearchProposalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    public function test_dosen_only_sees_own_proposals(): void
    {
        $dosenA = $this->userWithRole('dosen', 'dosen.a', 'Dosen A');
        $dosenB = $this->userWithRole('dosen', 'dosen.b', 'Dosen B');

        ResearchProposal::query()->create($this->proposalPayload($dosenA));
        ResearchProposal::query()->create($this->proposalPayload($dosenB));

        $this->actingAs($dosenA)
            ->get(route('admin.research.index'))
            ->assertOk()
            ->assertSee('Dosen A')
            ->assertDontSee('Dosen B');
    }

    public function test_admin_can_view_all_proposals(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.lppm', 'Admin LPPM');
        $dosen = $this->userWithRole('dosen', 'dosen.x', 'Dosen X');

        ResearchProposal::query()->create($this->proposalPayload($dosen));

        $this->actingAs($admin)
            ->get(route('admin.research.index'))
            ->assertOk()
            ->assertSee('Dosen X');
    }

    public function test_dosen_can_create_and_submit_proposal(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.submit', 'Dosen Submit');

        $response = $this->actingAs($dosen)->post(route('admin.research.store'), array_merge(
            $this->formPayload(),
            [
                'ketua_dosen_id' => 'dosen.submit',
                'ketua_dosen_nama_snapshot' => 'Dosen Submit',
                'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
                'file_pengesahan' => UploadedFile::fake()->create('pengesahan.pdf', 100, 'application/pdf'),
                'file_pernyataan' => UploadedFile::fake()->create('pernyataan.pdf', 100, 'application/pdf'),
            ],
        ));

        $response->assertRedirect();
        $proposal = ResearchProposal::query()->first();
        $this->assertNotNull($proposal);
        $this->assertSame('draft', $proposal->status);

        $this->actingAs($dosen)
            ->post(route('admin.research.submit', $proposal))
            ->assertRedirect();

        $proposal->refresh();
        $this->assertSame('admin_pending', $proposal->status);
    }

    public function test_empty_budget_rows_are_ignored_on_create(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.budget', 'Dosen Budget');

        $this->actingAs($dosen)->post(route('admin.research.store'), array_merge(
            $this->formPayload(),
            [
                'ketua_dosen_id' => 'dosen.budget',
                'ketua_dosen_nama_snapshot' => 'Dosen Budget',
                'budget_items' => [
                    ['item_name' => 'Honorarium', 'category' => 'honorarium', 'quantity' => 1, 'unit' => 'paket', 'unit_price' => 1000000],
                    ['item_name' => '', 'category' => 'other', 'quantity' => 1, 'unit' => '', 'unit_price' => 0],
                    ['item_name' => '', 'category' => 'other', 'quantity' => 1, 'unit' => '', 'unit_price' => 0],
                ],
                'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
                'file_pengesahan' => UploadedFile::fake()->create('pengesahan.pdf', 100, 'application/pdf'),
                'file_pernyataan' => UploadedFile::fake()->create('pernyataan.pdf', 100, 'application/pdf'),
            ],
        ))->assertRedirect();

        $proposal = ResearchProposal::query()->first();
        $this->assertNotNull($proposal);
        $this->assertCount(1, $proposal->budgetItems);
    }

    public function test_admin_can_verify_proposal(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.verify', 'Admin Verify');
        $dosen = $this->userWithRole('dosen', 'dosen.verify', 'Dosen Verify');
        $proposal = ResearchProposal::query()->create(array_merge($this->proposalPayload($dosen), [
            'status' => 'admin_pending',
            'current_stage' => 'admin_review',
            'file_proposal' => 'lppm/research/test/proposal.pdf',
            'file_pengesahan' => 'lppm/research/test/pengesahan.pdf',
            'file_pernyataan' => 'lppm/research/test/pernyataan.pdf',
        ]));

        $this->actingAs($admin)
            ->post(route('admin.research.admin-verification.store', $proposal), [
                'decision' => 'verified',
                'is_document_complete' => 1,
                'notes' => 'OK',
            ])
            ->assertRedirect();

        $proposal->refresh();
        $this->assertSame('admin_verified', $proposal->status);
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

    /**
     * @return array<string, mixed>
     */
    protected function formPayload(): array
    {
        return [
            'tahun_akademik_id' => '1',
            'tahun_akademik_nama_snapshot' => '2025/2026',
            'semester_id' => '1',
            'semester_nama_snapshot' => '2025/2026 — Ganjil',
            'prodi_id' => 'P001',
            'prodi_nama_snapshot' => 'Prodi Test',
            'skema_id' => ResearchScheme::query()->first()->id,
            'judul' => 'Proposal Baru dari Test',
            'ringkasan' => 'Ringkasan test',
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
