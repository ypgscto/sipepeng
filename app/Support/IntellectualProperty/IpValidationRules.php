<?php

namespace App\Support\IntellectualProperty;

use Illuminate\Validation\Rule;

class IpValidationRules
{
    /**
     * @return array<string, mixed>
     */
    public static function record(bool $isUpdate = false): array
    {
        $rules = [
            'ip_type_id' => ['required', 'integer', 'exists:lppm_ip_types,id'],
            'judul' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'registration_body' => ['nullable', 'string', 'max:100'],
            'application_number' => ['nullable', 'string', 'max:80'],
            'certificate_number' => ['nullable', 'string', 'max:80'],
            'application_date' => ['nullable', 'date'],
            'registration_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:application_date'],
            'ownership_type' => ['required', Rule::in(['institution', 'inventor', 'joint'])],
            'prodi_id' => ['required', 'string', 'max:50'],
            'prodi_nama_snapshot' => ['required', 'string', 'max:150'],
            'source_type' => ['required', Rule::in(['standalone', 'research', 'community_service'])],
            'research_proposal_id' => ['nullable', 'integer', 'exists:lppm_research_proposals,id'],
            'community_service_proposal_id' => ['nullable', 'integer', 'exists:lppm_community_service_proposals,id'],
            'inventors' => ['required', 'array', 'min:1'],
            'inventors.*.dosen_id' => ['required', 'string', 'max:50'],
            'inventors.*.dosen_nama_snapshot' => ['required', 'string', 'max:150'],
            'inventors.*.inventor_order' => ['nullable', 'integer', 'min:1'],
            'inventors.*.prodi_id' => ['nullable', 'string', 'max:50'],
            'inventors.*.prodi_nama_snapshot' => ['nullable', 'string', 'max:150'],
        ];

        foreach (config('sipepeng_hki.uploads', []) as $field => $cfg) {
            $requiredOnCreate = $field === 'file_application';
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
    public static function verification(): array
    {
        return [
            'decision' => ['required', Rule::in(['verified', 'rejected', 'revision_required'])],
            'is_document_complete' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
