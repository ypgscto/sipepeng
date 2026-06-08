<?php

namespace App\Support\Research;

use Illuminate\Validation\Rule;

class ResearchValidationRules
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
            'skema_id' => ['required', 'integer', 'exists:lppm_research_schemes,id'],
            'judul' => ['required', 'string', 'max:255'],
            'ketua_dosen_id' => ['required', 'string', 'max:50'],
            'ketua_dosen_nama_snapshot' => ['required', 'string', 'max:150'],
            'bidang_fokus_id' => ['nullable', 'integer', 'exists:lppm_focus_areas,id'],
            'rumpun_ilmu_id' => ['nullable', 'integer', 'exists:lppm_science_clusters,id'],
            'ringkasan' => ['nullable', 'string', 'max:5000'],
            'latar_belakang' => ['nullable', 'string', 'max:10000'],
            'rumusan_masalah' => ['nullable', 'string', 'max:5000'],
            'tujuan' => ['nullable', 'string', 'max:5000'],
            'manfaat' => ['nullable', 'string', 'max:5000'],
            'metode' => ['nullable', 'string', 'max:10000'],
            'lokasi' => ['nullable', 'string', 'max:255'],
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

        foreach (config('sipepeng_research.uploads', []) as $field => $cfg) {
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
     * @param  mixed  $items
     * @return list<array<string, mixed>>|null
     */
    public static function filterBudgetItems(mixed $items): ?array
    {
        if (! is_array($items)) {
            return null;
        }

        $filtered = array_values(array_filter($items, function ($item): bool {
            if (! is_array($item)) {
                return false;
            }

            return trim((string) ($item['item_name'] ?? '')) !== '';
        }));

        return $filtered === [] ? null : $filtered;
    }

    /**
     * @return array<string, mixed>
     */
    public static function adminVerification(): array
    {
        return [
            'decision' => ['required', Rule::in(['verified', 'rejected', 'revision_required'])],
            'is_document_complete' => ['nullable', 'boolean'],
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
