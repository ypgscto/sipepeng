<?php

namespace App\Support\CommunityService;

use Illuminate\Validation\Rule;

class PkmValidationRules
{
    /**
     * @return array<string, mixed>
     */
    public static function proposal(bool $isUpdate = false): array
    {
        $rules = [
            'tahun_akademik_id' => ['required', 'string', 'max:50'],
            'tahun_akademik_nama_snapshot' => ['required', 'string', 'max:100'],
            'semester_id' => ['required', 'string', 'max:50'],
            'semester_nama_snapshot' => ['required', 'string', 'max:100'],
            'prodi_id' => ['required', 'string', 'max:50'],
            'prodi_nama_snapshot' => ['required', 'string', 'max:150'],
            'skema_id' => ['required', 'integer', 'exists:lppm_community_service_schemes,id'],
            'judul' => ['required', 'string', 'max:255'],
            'ketua_dosen_id' => ['required', 'string', 'max:50'],
            'ketua_dosen_nama_snapshot' => ['required', 'string', 'max:150'],
            'mitra_id' => ['required', 'integer', 'exists:lppm_partners,id'],
            'mitra_nama_snapshot' => ['required', 'string', 'max:150'],
            'jenis_mitra_id' => ['required', 'integer', 'exists:lppm_partner_types,id'],
            'jenis_mitra_nama_snapshot' => ['required', 'string', 'max:150'],
            'masalah_mitra' => ['nullable', 'string', 'max:10000'],
            'solusi_ditawarkan' => ['nullable', 'string', 'max:10000'],
            'target_capaian' => ['nullable', 'string', 'max:5000'],
            'metode_pelaksanaan' => ['nullable', 'string', 'max:10000'],
            'lokasi_kegiatan' => ['nullable', 'string', 'max:255'],
            'jadwal_mulai' => ['nullable', 'date'],
            'jadwal_selesai' => ['nullable', 'date', 'after_or_equal:jadwal_mulai'],
            'total_rab' => ['nullable', 'numeric', 'min:0'],
            'target_luaran' => ['nullable', 'string', 'max:5000'],
            'budget_items' => ['nullable', 'array'],
            'budget_items.*.item_name' => ['required_with:budget_items', 'string', 'max:150'],
            'budget_items.*.category' => ['nullable', 'string', 'max:30'],
            'budget_items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'budget_items.*.unit' => ['nullable', 'string', 'max:30'],
            'budget_items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ];

        foreach (config('sipepeng_community_service.uploads', []) as $field => $cfg) {
            $mimes = implode(',', $cfg['mimes'] ?? ['pdf']);
            $max = (int) ($cfg['max_kb'] ?? 10240);
            $rules[$field] = [
                $isUpdate ? 'nullable' : 'required',
                'file',
                'mimes:'.$mimes,
                'max:'.$max,
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public static function adminVerification(): array
    {
        return [
            'decision' => ['required', Rule::in(['verified', 'rejected', 'revision_required'])],
            'is_document_complete' => ['nullable', 'boolean'],
            'is_partner_verified' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function assignReviewer(): array
    {
        return [
            'reviewer_id' => ['required', 'integer', 'exists:lppm_reviewers,id'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function submitReview(): array
    {
        return [
            'recommendation' => ['required', Rule::in(['approve', 'approve_with_revision', 'reject'])],
            'overall_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'summary' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function decision(): array
    {
        return [
            'decision' => ['required', Rule::in(['approve', 'reject'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
