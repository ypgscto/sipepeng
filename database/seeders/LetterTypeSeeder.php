<?php

namespace Database\Seeders;

use App\Models\Lppm\DocumentCategory;
use App\Models\Lppm\DocumentTemplate;
use App\Models\Lppm\LetterType;
use Illuminate\Database\Seeder;

class LetterTypeSeeder extends Seeder
{
    public function run(): void
    {
        $category = DocumentCategory::query()->updateOrCreate(
            ['code' => 'surat_lppm'],
            ['name' => 'Surat LPPM', 'module_type' => 'general', 'sort_order' => 80, 'is_active' => true],
        );

        $types = [
            ['code' => 'surat_tugas_penelitian', 'name' => 'Surat Tugas Penelitian', 'prefix' => 'LPPM/ST-P', 'applies_to' => 'research', 'requires_proposal' => true, 'allow_dosen' => true, 'view' => 'admin.letters.templates.surat-tugas-penelitian', 'sort' => 10],
            ['code' => 'surat_tugas_pkm', 'name' => 'Surat Tugas Pengabdian', 'prefix' => 'LPPM/ST-K', 'applies_to' => 'community_service', 'requires_proposal' => true, 'allow_dosen' => true, 'view' => 'admin.letters.templates.surat-tugas-pkm', 'sort' => 20],
            ['code' => 'surat_izin_penelitian', 'name' => 'Surat Izin Penelitian', 'prefix' => 'LPPM/SIP', 'applies_to' => 'research', 'requires_proposal' => true, 'allow_dosen' => true, 'view' => 'admin.letters.templates.surat-izin-penelitian', 'sort' => 30],
            ['code' => 'surat_izin_pkm', 'name' => 'Surat Izin Pengabdian', 'prefix' => 'LPPM/SIK', 'applies_to' => 'community_service', 'requires_proposal' => true, 'allow_dosen' => true, 'view' => 'admin.letters.templates.surat-izin-pkm', 'sort' => 40],
            ['code' => 'surat_permohonan_data', 'name' => 'Surat Permohonan Data', 'prefix' => 'LPPM/SPD', 'applies_to' => 'general', 'requires_proposal' => false, 'allow_dosen' => true, 'view' => 'admin.letters.templates.surat-permohonan-data', 'sort' => 50],
            ['code' => 'surat_pengantar_mitra', 'name' => 'Surat Pengantar ke Mitra', 'prefix' => 'LPPM/SPM', 'applies_to' => 'partner', 'requires_proposal' => false, 'requires_partner' => true, 'allow_dosen' => false, 'view' => 'admin.letters.templates.surat-pengantar-mitra', 'sort' => 60],
            ['code' => 'surat_undangan_reviewer', 'name' => 'Surat Undangan Reviewer', 'prefix' => 'LPPM/SUR', 'applies_to' => 'reviewer', 'requires_proposal' => false, 'allow_dosen' => false, 'view' => 'admin.letters.templates.surat-undangan-reviewer', 'sort' => 70],
            ['code' => 'surat_undangan_seminar', 'name' => 'Surat Undangan Seminar', 'prefix' => 'LPPM/SUS', 'applies_to' => 'general', 'requires_proposal' => false, 'allow_dosen' => false, 'view' => 'admin.letters.templates.surat-undangan-seminar', 'sort' => 80],
            ['code' => 'surat_keterangan_selesai_penelitian', 'name' => 'Surat Keterangan Selesai Penelitian', 'prefix' => 'LPPM/SKP', 'applies_to' => 'research', 'requires_proposal' => true, 'allow_dosen' => false, 'view' => 'admin.letters.templates.surat-keterangan-selesai-penelitian', 'sort' => 90],
            ['code' => 'surat_keterangan_selesai_pkm', 'name' => 'Surat Keterangan Selesai Pengabdian', 'prefix' => 'LPPM/SKK', 'applies_to' => 'community_service', 'requires_proposal' => true, 'allow_dosen' => false, 'view' => 'admin.letters.templates.surat-keterangan-selesai-pkm', 'sort' => 100],
            ['code' => 'surat_keterangan_luaran', 'name' => 'Surat Keterangan Luaran', 'prefix' => 'LPPM/SKL', 'applies_to' => 'publication', 'requires_proposal' => false, 'allow_dosen' => true, 'view' => 'admin.letters.templates.surat-keterangan-luaran', 'sort' => 110],
        ];

        LetterType::query()->where('code', 'surat_tugas')->delete();

        foreach ($types as $def) {
            $template = DocumentTemplate::query()->updateOrCreate(
                ['template_code' => 'tpl_'.$def['code']],
                [
                    'name' => 'Template '.$def['name'],
                    'document_category_id' => $category->id,
                    'module_type' => 'letter',
                    'file_path' => 'templates/letters/'.$def['code'].'.blade',
                    'file_name' => $def['code'].'.blade.php',
                    'mime_type' => 'text/html',
                    'file_size' => 0,
                    'render_engine' => 'blade_pdf',
                    'blade_view' => $def['view'],
                    'is_default' => true,
                    'sort_order' => $def['sort'],
                    'is_active' => true,
                ],
            );

            LetterType::query()->updateOrCreate(
                ['code' => $def['code']],
                [
                    'name' => $def['name'],
                    'letter_prefix' => $def['prefix'],
                    'document_template_id' => $template->id,
                    'requires_approval' => true,
                    'applies_to' => $def['applies_to'],
                    'number_format_pattern' => '{prefix}/{seq:04d}/{year}',
                    'requires_proposal_link' => $def['requires_proposal'] ?? false,
                    'requires_partner_link' => $def['requires_partner'] ?? false,
                    'min_proposal_status' => 'approved',
                    'allow_dosen_create' => $def['allow_dosen'] ?? true,
                    'sort_order' => $def['sort'],
                    'is_active' => true,
                ],
            );
        }
    }
}
