<?php

namespace App\Support\Letter;

use App\Models\Lppm\LetterType;

class LetterValidationRules
{
    /**
     * @return array<string, mixed>
     */
    public static function baseRules(): array
    {
        return [
            'letter_type_id' => ['required', 'integer', 'exists:lppm_letter_types,id'],
            'perihal' => ['required', 'string', 'max:255'],
            'letter_date' => ['required', 'date'],
            'place_of_issue' => ['nullable', 'string', 'max:100'],
            'body_content' => ['nullable', 'string', 'max:10000'],
            'notes_internal' => ['nullable', 'string', 'max:2000'],
            'research_proposal_id' => ['nullable', 'integer', 'exists:lppm_research_proposals,id'],
            'community_service_proposal_id' => ['nullable', 'integer', 'exists:lppm_community_service_proposals,id'],
            'partner_id' => ['nullable', 'integer', 'exists:lppm_partners,id'],
            'reviewer_id' => ['nullable', 'integer', 'exists:lppm_reviewers,id'],
            'publication_id' => ['nullable', 'integer', 'exists:lppm_publications,id'],
            'ip_registration_id' => ['nullable', 'integer', 'exists:lppm_ip_registrations,id'],
            'recipient_external_name' => ['nullable', 'string', 'max:200'],
            'recipient_external_institution' => ['nullable', 'string', 'max:200'],
            'recipient_external_address' => ['nullable', 'string', 'max:1000'],
            'event_date' => ['nullable', 'date'],
            'event_time' => ['nullable', 'string', 'max:20'],
            'event_location' => ['nullable', 'string', 'max:255'],
            'event_agenda' => ['nullable', 'string', 'max:5000'],
            'recipients' => ['nullable', 'array'],
            'recipients.*.name' => ['required_with:recipients', 'string', 'max:200'],
            'recipients.*.email' => ['nullable', 'email', 'max:150'],
            'recipients.*.institution' => ['nullable', 'string', 'max:200'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function rulesForType(?LetterType $type): array
    {
        $rules = self::baseRules();
        if (! $type) {
            return $rules;
        }

        $typeRules = config('sipepeng_letters.letter_type_rules.'.$type->code, []);

        if ($typeRules['requires_proposal_link'] ?? false) {
            $appliesTo = $typeRules['applies_to'] ?? $type->applies_to;
            if ($appliesTo === 'research') {
                $rules['research_proposal_id'] = ['required', 'integer', 'exists:lppm_research_proposals,id'];
            } elseif ($appliesTo === 'community_service') {
                $rules['community_service_proposal_id'] = ['required', 'integer', 'exists:lppm_community_service_proposals,id'];
            }
        }

        if ($typeRules['requires_partner_link'] ?? false) {
            $rules['partner_id'] = ['required', 'integer', 'exists:lppm_partners,id'];
        }

        if (($typeRules['applies_to'] ?? $type->applies_to) === 'reviewer') {
            $rules['reviewer_id'] = ['required', 'integer', 'exists:lppm_reviewers,id'];
        }

        if ($type->code === 'surat_permohonan_data') {
            $rules['recipient_external_name'] = ['required', 'string', 'max:200'];
            $rules['recipient_external_institution'] = ['required', 'string', 'max:200'];
        }

        if ($type->code === 'surat_undangan_seminar') {
            $rules['event_date'] = ['required', 'date'];
            $rules['event_location'] = ['required', 'string', 'max:255'];
            $rules['event_agenda'] = ['required', 'string', 'max:5000'];
        }

        if ($type->code === 'surat_keterangan_luaran') {
            $rules['publication_id'] = ['required_without:ip_registration_id', 'nullable', 'integer', 'exists:lppm_publications,id'];
            $rules['ip_registration_id'] = ['required_without:publication_id', 'nullable', 'integer', 'exists:lppm_ip_registrations,id'];
        }

        return $rules;
    }

    /**
     * @return array<string, mixed>
     */
    public static function signedScanRules(): array
    {
        $upload = config('sipepeng_letters.uploads.file_signed_scan', ['mimes' => ['pdf'], 'max_kb' => 5120]);

        return [
            'file_signed_scan' => [
                'required',
                'file',
                'mimes:'.implode(',', $upload['mimes'] ?? ['pdf']),
                'max:'.($upload['max_kb'] ?? 5120),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function approvalRules(): array
    {
        return [
            'decision' => ['required', 'in:approved,rejected,revision_required'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
