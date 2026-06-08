<?php

namespace App\Support\Lppm;

use Illuminate\Validation\Rule;

class LppmValidationRules
{
    /**
     * @return array<string, mixed>
     */
    public static function for(string $entityKey, ?int $ignoreId = null): array
    {
        return match ($entityKey) {
            'research-schemes' => self::researchScheme($ignoreId),
            'community-service-schemes' => self::communityServiceScheme($ignoreId),
            'output-types' => self::outputType($ignoreId),
            'funding-sources' => self::fundingSource($ignoreId),
            'focus-areas' => self::focusArea($ignoreId),
            'science-clusters' => self::scienceCluster($ignoreId),
            'partner-types' => self::partnerType($ignoreId),
            'document-templates' => self::documentTemplate($ignoreId),
            'reviewers' => self::reviewer($ignoreId),
            'document-categories' => self::documentCategory($ignoreId),
            'ip-types' => self::ipType($ignoreId),
            'publication-types' => self::publicationType($ignoreId),
            'letter-types' => self::letterType($ignoreId),
            'proposal-statuses' => self::proposalStatus($ignoreId),
            default => self::base($ignoreId, 'lppm_funding_sources'),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected static function base(?int $ignoreId, string $table): array
    {
        return [
            'code' => ['required', 'string', 'max:30', 'regex:/^[a-z0-9_]+$/', Rule::unique($table, 'code')->ignore($ignoreId)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    protected static function researchScheme(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_research_schemes'), [
            'academic_year_label' => ['nullable', 'string', 'max:20'],
            'max_budget' => ['nullable', 'numeric', 'min:0'],
            'min_team_members' => ['nullable', 'integer', 'min:1', 'max:50'],
            'max_team_members' => ['nullable', 'integer', 'min:1', 'max:50', 'gte:min_team_members'],
            'requires_ethics_approval' => ['nullable', 'boolean'],
            'submission_deadline' => ['nullable', 'date'],
            'guideline_url' => ['nullable', 'url', 'max:500'],
            'funding_source_ids' => ['nullable', 'array'],
            'funding_source_ids.*' => ['integer', 'exists:lppm_funding_sources,id'],
        ]);
    }

    protected static function communityServiceScheme(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_community_service_schemes'), [
            'academic_year_label' => ['nullable', 'string', 'max:20'],
            'max_budget' => ['nullable', 'numeric', 'min:0'],
            'min_team_members' => ['nullable', 'integer', 'min:1', 'max:50'],
            'max_team_members' => ['nullable', 'integer', 'min:1', 'max:50'],
            'requires_partner' => ['nullable', 'boolean'],
            'submission_deadline' => ['nullable', 'date'],
            'guideline_url' => ['nullable', 'url', 'max:500'],
            'funding_source_ids' => ['nullable', 'array'],
            'funding_source_ids.*' => ['integer', 'exists:lppm_funding_sources,id'],
        ]);
    }

    protected static function outputType(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_output_types'), [
            'applies_to' => ['required', 'in:research,community_service,both'],
            'is_measurable' => ['nullable', 'boolean'],
            'unit_label' => ['nullable', 'string', 'max:30'],
        ]);
    }

    protected static function fundingSource(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_funding_sources'), [
            'source_category' => ['required', 'in:internal,external,mixed'],
            'institution_name' => ['nullable', 'string', 'max:150'],
            'requires_contract' => ['nullable', 'boolean'],
        ]);
    }

    protected static function focusArea(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_focus_areas'), [
            'parent_id' => array_filter(['nullable', 'integer', 'exists:lppm_focus_areas,id', $id ? 'not_in:'.$id : null]),
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'year_start' => ['nullable', 'integer', 'min:2000', 'max:2100'],
            'year_end' => ['nullable', 'integer', 'min:2000', 'max:2100', 'gte:year_start'],
        ]);
    }

    protected static function scienceCluster(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_science_clusters'), [
            'feeder_code' => ['nullable', 'string', 'max:20', Rule::unique('lppm_science_clusters', 'feeder_code')->ignore($id)],
            'parent_id' => array_filter(['nullable', 'integer', 'exists:lppm_science_clusters,id', $id ? 'not_in:'.$id : null]),
            'level' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);
    }

    protected static function partnerType(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_partner_types'), [
            'requires_legal_document' => ['nullable', 'boolean'],
            'icon' => ['nullable', 'string', 'max:30'],
        ]);
    }

    protected static function documentCategory(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_document_categories'), [
            'module_type' => ['required', 'in:research,community_service,general'],
            'is_required' => ['nullable', 'boolean'],
        ]);
    }

    protected static function documentTemplate(?int $id): array
    {
        return [
            'template_code' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9_]+$/', Rule::unique('lppm_document_templates', 'template_code')->ignore($id)],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'document_category_id' => ['nullable', 'integer', 'exists:lppm_document_categories,id'],
            'module_type' => ['required', 'in:research,community_service,general,letter'],
            'version' => ['nullable', 'string', 'max:20'],
            'is_default' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'file' => [$id ? 'nullable' : 'required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ];
    }

    protected static function reviewer(?int $id): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id', Rule::unique('lppm_reviewers', 'user_id')->ignore($id)],
            'expertise_notes' => ['nullable', 'string', 'max:2000'],
            'science_cluster_id' => ['nullable', 'integer', 'exists:lppm_science_clusters,id'],
            'focus_area_id' => ['nullable', 'integer', 'exists:lppm_focus_areas,id'],
            'max_active_reviews' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'appointed_at' => ['nullable', 'date'],
        ];
    }

    protected static function ipType(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_ip_types'), [
            'registration_body' => ['nullable', 'string', 'max:100'],
            'typical_duration_months' => ['nullable', 'integer', 'min:1', 'max:120'],
        ]);
    }

    protected static function publicationType(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_publication_types'), [
            'indexing_type' => ['nullable', 'in:national,international,other'],
            'requires_issn_isbn' => ['nullable', 'boolean'],
            'feeder_code' => ['nullable', 'string', 'max:20'],
        ]);
    }

    protected static function letterType(?int $id): array
    {
        return array_merge(self::base($id, 'lppm_letter_types'), [
            'letter_prefix' => ['nullable', 'string', 'max:20'],
            'document_template_id' => ['nullable', 'integer', 'exists:lppm_document_templates,id'],
            'requires_approval' => ['nullable', 'boolean'],
        ]);
    }

    protected static function proposalStatus(?int $id): array
    {
        $rules = array_merge(self::base($id, 'lppm_proposal_statuses'), [
            'proposal_type' => ['required', 'in:research,community_service,both'],
            'stage' => ['required', 'string', 'max:30'],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_terminal' => ['nullable', 'boolean'],
            'is_editable_by_proposer' => ['nullable', 'boolean'],
        ]);

        if ($id) {
            $rules['code'] = ['prohibited'];
        }

        return $rules;
    }
}
