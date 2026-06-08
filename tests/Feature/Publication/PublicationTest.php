<?php

namespace Tests\Feature\Publication;

use App\Models\Lppm\PublicationType;
use App\Models\Publication\Publication;
use App\Models\Research\ResearchProposal;
use App\Models\SipepengRole;
use App\Models\SipepengUserRoleMapping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local');
    }

    public function test_dosen_can_create_publication(): void
    {
        $dosen = $this->userWithRole('dosen', 'dosen.pub', 'Dosen Pub');

        $response = $this->actingAs($dosen)->post(route('admin.publications.store'), [
            'publication_type_id' => PublicationType::query()->first()->id,
            'judul' => 'Artikel Test Publikasi',
            'prodi_id' => 'P001',
            'prodi_nama_snapshot' => 'Prodi Test',
            'source_type' => 'standalone',
            'publication_year' => now()->year,
            'authors' => [[
                'dosen_id' => 'dosen.pub',
                'dosen_nama_snapshot' => 'Dosen Pub',
                'author_order' => 1,
                'role' => 'lead',
            ]],
            'file_manuscript' => UploadedFile::fake()->create('ms.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lppm_publications', ['judul' => 'Artikel Test Publikasi', 'status' => 'draft']);
    }

    public function test_admin_can_view_all_publications(): void
    {
        $admin = $this->userWithRole('admin_lppm', 'admin.pub', 'Admin Pub');
        $dosen = $this->userWithRole('dosen', 'dosen.other', 'Dosen Other');

        Publication::query()->create([
            'registration_number' => 'PUB/2026/9999',
            'publication_type_id' => PublicationType::query()->first()->id,
            'judul' => 'Publikasi Admin View',
            'prodi_id' => 'P001',
            'prodi_nama_snapshot' => 'Prodi',
            'source_type' => 'standalone',
            'status' => 'draft',
            'created_by' => $dosen->id,
            'updated_by' => $dosen->id,
        ]);

        $this->actingAs($admin)->get(route('admin.publications.index'))->assertOk()->assertSee('Publikasi Admin View');
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
