<?php

namespace App\Support\ResearchEthics;

use Illuminate\Validation\Rule;

class EthicsValidationRules
{
    /**
     * @return array<string, mixed>
     */
    public static function record(bool $isUpdate = false): array
    {
        $rules = [
            'research_proposal_id' => ['required', 'integer', 'exists:lppm_research_proposals,id'],
            'proposal_number_snapshot' => ['required', 'string', 'max:40'],
            'proposal_judul_snapshot' => ['required', 'string', 'max:255'],
            'ketua_dosen_id' => ['required', 'string', 'max:50'],
            'ketua_dosen_nama_snapshot' => ['required', 'string', 'max:150'],
            'prodi_id' => ['required', 'string', 'max:50'],
            'prodi_nama_snapshot' => ['required', 'string', 'max:150'],
            'study_type' => ['nullable', Rule::in(['interventional', 'observational', 'survey', 'qualitative', 'other'])],
            'population_description' => ['nullable', 'string', 'max:10000'],
            'risk_level' => ['nullable', Rule::in(['minimal', 'low', 'moderate', 'high'])],
            'data_collection_method' => ['nullable', 'string', 'max:5000'],
            'informed_consent_required' => ['nullable', 'boolean'],
            'conflict_of_interest_declared' => ['nullable', 'boolean'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];

        foreach (config('sipepeng_ethics.uploads', []) as $field => $cfg) {
            $requiredOnCreate = in_array($field, ['file_protocol', 'file_ethics_application'], true);
            $rules[$field] = [
                ($isUpdate || ! $requiredOnCreate) ? 'nullable' : 'required',
                'file', 'mimes:'.implode(',', $cfg['mimes'] ?? ['pdf']), 'max:'.($cfg['max_kb'] ?? 10240),
            ];
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public static function decision(): array
    {
        return [
            'decision' => ['required', Rule::in(['approve', 'reject', 'revision_required'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'valid_until' => ['nullable', 'date'],
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
}
