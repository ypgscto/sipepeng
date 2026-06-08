<?php

namespace Tests\Feature\Notification;

use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\Lppm\ResearchScheme;
use App\Models\Lppm\Reviewer;
use App\Models\Notification\LppmNotification;
use App\Models\Research\ResearchProposal;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_submitting_research_notifies_admin_lppm(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.notif', 'Admin Notif');
        $dosen = $this->userWithRole('dosen', 'dosen.notif', 'Dosen Notif');

        $proposal = ResearchProposal::query()->create(array_merge($this->researchPayload($dosen), [
            'status' => 'draft',
            'file_proposal' => 'lppm/research/test/proposal.pdf',
            'file_pengesahan' => 'lppm/research/test/pengesahan.pdf',
            'file_pernyataan' => 'lppm/research/test/pernyataan.pdf',
        ]));

        $this->actingAs($dosen)
            ->post(route('admin.research.submit', $proposal))
            ->assertRedirect();

        $this->assertTrue(
            LppmNotification::query()->where('user_id', $admin->id)->where('type', 'proposal_submitted_research')->exists()
        );
    }

    public function test_revision_request_notifies_proposer(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.rev', 'Admin Rev');
        $dosen = $this->userWithRole('dosen', 'dosen.rev', 'Dosen Rev');

        $proposal = ResearchProposal::query()->create(array_merge($this->researchPayload($dosen), [
            'status' => 'admin_pending',
            'current_stage' => 'admin_review',
            'file_proposal' => 'lppm/research/test/proposal.pdf',
            'file_pengesahan' => 'lppm/research/test/pengesahan.pdf',
            'file_pernyataan' => 'lppm/research/test/pernyataan.pdf',
        ]));

        $this->actingAs($admin)
            ->post(route('admin.research.admin-verification.store', $proposal), [
                'decision' => 'revision_required',
                'is_document_complete' => 0,
                'notes' => 'Lengkapi lampiran',
            ])
            ->assertRedirect();

        $notification = LppmNotification::query()
            ->where('user_id', $dosen->id)
            ->where('type', 'proposal_revision_research')
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString('Lengkapi lampiran', $notification->body);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.read', 'Dosen Read');

        $notification = LppmNotification::query()->create([
            'user_id' => $dosen->id,
            'category' => 'revision',
            'type' => 'proposal_revision_research',
            'severity' => 'warning',
            'title' => 'Proposal penelitian perlu revisi',
            'body' => 'Test body',
        ]);

        $this->actingAs($dosen)
            ->patch(route('notifications.read', $notification))
            ->assertRedirect();

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_user_cannot_read_other_users_notification(): void
    {
        $userA = $this->userWithRole('dosen', 'dosen.a', 'Dosen A');
        $userB = $this->userWithRole('dosen', 'dosen.b', 'Dosen B');

        $notification = LppmNotification::query()->create([
            'user_id' => $userA->id,
            'category' => 'workflow',
            'type' => 'proposal_submitted_research',
            'severity' => 'info',
            'title' => 'Test',
            'body' => 'Test',
        ]);

        $this->actingAs($userB)
            ->patch(route('notifications.read', $notification))
            ->assertForbidden();
    }

    public function test_notifications_index_supports_unread_filter(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.filter', 'Dosen Filter');

        LppmNotification::query()->create([
            'user_id' => $dosen->id,
            'category' => 'workflow',
            'type' => 'proposal_submitted_research',
            'severity' => 'info',
            'title' => 'Unread',
            'body' => 'Unread body',
        ]);

        LppmNotification::query()->create([
            'user_id' => $dosen->id,
            'category' => 'workflow',
            'type' => 'proposal_submitted_research',
            'severity' => 'info',
            'title' => 'Read',
            'body' => 'Read body',
            'read_at' => now(),
        ]);

        $this->actingAs($dosen)
            ->get(route('notifications.index', ['filter' => 'unread']))
            ->assertOk()
            ->assertSee('Unread')
            ->assertDontSee('Read body');
    }

    public function test_login_success_writes_security_log(): void
    {
        $api = \Mockery::mock(\App\Services\Siakad\SiakadAuthApiService::class);
        $api->shouldReceive('attemptLogin')->once()->andReturn([
            'login' => 'dosen.loginlog',
            'nama' => 'Dosen Login Log',
            'jenis_user' => '7',
            'email' => 'dosen.loginlog@stikesgunungsari.ac.id',
        ]);
        $this->app->instance(\App\Services\Siakad\SiakadAuthApiService::class, $api);

        $dosen = $this->userWithRole('dosen', 'dosen.loginlog', 'Dosen Login Log');

        $this->post(route('login'), [
            'login' => $dosen->siakad_login,
            'password' => 'secret',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'log_name' => 'security',
            'event' => 'login_success',
            'causer_id' => $dosen->id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function researchPayload(User $ketua): array
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
            'judul' => 'Judul Penelitian Notif',
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
