<?php

namespace Tests\Feature\CommunityService;

use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\Lppm\CommunityServiceScheme;
use App\Models\Lppm\Partner;
use App\Models\User;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PkmProposalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    public function test_dosen_only_sees_own_pkm_proposals(): void
    {
        $dosenA = $this->userWithRole('dosen', 'dosen.a', 'Dosen A');
        $dosenB = $this->userWithRole('dosen', 'dosen.b', 'Dosen B');

        CommunityServiceProposal::query()->create($this->proposalPayload($dosenA));
        CommunityServiceProposal::query()->create($this->proposalPayload($dosenB));

        $this->actingAs($dosenA)
            ->get(route('admin.community-service.index'))
            ->assertOk()
            ->assertSee('Dosen A')
            ->assertDontSee('Dosen B');
    }

    public function test_admin_can_view_all_pkm_proposals(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.lppm', 'Admin LPPM');
        $dosen = $this->userWithRole('dosen', 'dosen.x', 'Dosen X');

        CommunityServiceProposal::query()->create($this->proposalPayload($dosen));

        $this->actingAs($admin)
            ->get(route('admin.community-service.index'))
            ->assertOk()
            ->assertSee('Dosen X');
    }

    public function test_dosen_can_create_and_submit_pkm_proposal(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.submit', 'Dosen Submit');
        $partner = Partner::query()->firstOrFail();

        $response = $this->actingAs($dosen)->post(route('admin.community-service.store'), array_merge(
            $this->formPayload($partner),
            [
                'ketua_dosen_id' => 'dosen.submit',
                'ketua_dosen_nama_snapshot' => 'Dosen Submit',
                'file_proposal' => UploadedFile::fake()->create('proposal.pdf', 100, 'application/pdf'),
                'file_surat_mitra' => UploadedFile::fake()->create('surat_mitra.pdf', 100, 'application/pdf'),
                'file_pengesahan' => UploadedFile::fake()->create('pengesahan.pdf', 100, 'application/pdf'),
            ],
        ));

        $response->assertRedirect();
        $proposal = CommunityServiceProposal::query()->first();
        $this->assertNotNull($proposal);
        $this->assertSame('draft', $proposal->status);

        $this->actingAs($dosen)
            ->post(route('admin.community-service.submit', $proposal))
            ->assertRedirect();

        $proposal->refresh();
        $this->assertSame('admin_pending', $proposal->status);
    }

    public function test_admin_can_verify_pkm_proposal(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.verify', 'Admin Verify');
        $dosen = $this->userWithRole('dosen', 'dosen.verify', 'Dosen Verify');
        $proposal = CommunityServiceProposal::query()->create(array_merge($this->proposalPayload($dosen), [
            'status' => 'admin_pending',
            'current_stage' => 'admin_review',
            'file_proposal' => 'lppm/pkm/test/proposal.pdf',
            'file_surat_mitra' => 'lppm/pkm/test/surat_mitra.pdf',
            'file_pengesahan' => 'lppm/pkm/test/pengesahan.pdf',
        ]));

        $this->actingAs($admin)
            ->post(route('admin.community-service.admin-verification.store', $proposal), [
                'decision' => 'verified',
                'is_document_complete' => 1,
                'is_partner_verified' => 1,
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
        $partner = Partner::query()->firstOrFail();

        return [
            'proposal_number' => 'PKM/'.now()->year.'/'.random_int(1000, 9999),
            'tahun_akademik_id' => '1',
            'tahun_akademik_nama_snapshot' => '2025/2026',
            'semester_id' => '1',
            'semester_nama_snapshot' => '2025/2026 — Ganjil',
            'prodi_id' => 'P001',
            'prodi_nama_snapshot' => 'Prodi Test',
            'skema_id' => CommunityServiceScheme::query()->first()->id,
            'judul' => 'Judul PkM Test',
            'ketua_dosen_id' => $ketua->siakad_login,
            'ketua_dosen_nama_snapshot' => $ketua->name,
            'ketua_user_id' => $ketua->id,
            'mitra_id' => $partner->id,
            'mitra_nama_snapshot' => $partner->name,
            'jenis_mitra_id' => $partner->partner_type_id,
            'jenis_mitra_nama_snapshot' => $partner->partnerType->name,
            'status' => 'draft',
            'current_stage' => 'submission',
            'created_by' => $ketua->id,
            'updated_by' => $ketua->id,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function formPayload(Partner $partner): array
    {
        return [
            'tahun_akademik_id' => '1',
            'tahun_akademik_nama_snapshot' => '2025/2026',
            'semester_id' => '1',
            'semester_nama_snapshot' => '2025/2026 — Ganjil',
            'prodi_id' => 'P001',
            'prodi_nama_snapshot' => 'Prodi Test',
            'skema_id' => CommunityServiceScheme::query()->first()->id,
            'judul' => 'Proposal PkM Baru dari Test',
            'mitra_id' => $partner->id,
            'mitra_nama_snapshot' => $partner->name,
            'jenis_mitra_id' => $partner->partner_type_id,
            'jenis_mitra_nama_snapshot' => $partner->partnerType->name,
            'masalah_mitra' => 'Masalah test',
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
