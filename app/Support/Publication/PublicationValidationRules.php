<?php

namespace App\Support\Publication;

use Illuminate\Validation\Rule;

class PublicationValidationRules
{
    /**
     * @return array<string, mixed>
     */
    public static function record(bool $isUpdate = false): array
    {
        $rules = [
            'publication_type_id' => ['required', 'integer', 'exists:lppm_publication_types,id'],
            'judul' => ['required', 'string', 'max:255'],
            'abstract' => ['nullable', 'string', 'max:10000'],
            'journal_or_publisher' => ['nullable', 'string', 'max:255'],
            'issn' => ['nullable', 'string', 'max:20'],
            'isbn' => ['nullable', 'string', 'max:20'],
            'doi' => ['nullable', 'string', 'max:100'],
            'url' => ['nullable', 'url', 'max:500'],
            'indexing_label' => ['nullable', 'string', 'max:100'],
            'publication_year' => ['nullable', 'integer', 'min:1990', 'max:2100'],
            'publication_date' => ['nullable', 'date'],
            'volume' => ['nullable', 'string', 'max:30'],
            'issue' => ['nullable', 'string', 'max:30'],
            'pages' => ['nullable', 'string', 'max:50'],
            'prodi_id' => ['required', 'string', 'max:50'],
            'prodi_nama_snapshot' => ['required', 'string', 'max:150'],
            'source_type' => ['required', Rule::in(['standalone', 'research', 'community_service'])],
            'research_proposal_id' => ['nullable', 'integer', 'exists:lppm_research_proposals,id'],
            'community_service_proposal_id' => ['nullable', 'integer', 'exists:lppm_community_service_proposals,id'],
            'output_type_id' => ['nullable', 'integer', 'exists:lppm_output_types,id'],
            'authors' => ['required', 'array', 'min:1'],
            'authors.*.dosen_id' => ['required', 'string', 'max:50'],
            'authors.*.dosen_nama_snapshot' => ['required', 'string', 'max:150'],
            'authors.*.author_order' => ['nullable', 'integer', 'min:1'],
            'authors.*.role' => ['nullable', Rule::in(['lead', 'corresponding', 'co_author'])],
            'authors.*.prodi_id' => ['nullable', 'string', 'max:50'],
            'authors.*.prodi_nama_snapshot' => ['nullable', 'string', 'max:150'],
        ];

        foreach (config('sipepeng_publication.uploads', []) as $field => $cfg) {
            $requiredOnCreate = in_array($field, ['file_manuscript'], true);
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
