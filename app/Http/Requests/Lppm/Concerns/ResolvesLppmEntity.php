<?php

namespace App\Http\Requests\Lppm\Concerns;

use Illuminate\Database\Eloquent\Model;

trait ResolvesLppmEntity
{
    protected function entityKey(): string
    {
        $routeName = (string) $this->route()?->getName();
        $parts = explode('.', $routeName);

        return $parts[2] ?? '';
    }

    protected function recordId(): ?int
    {
        $record = $this->route('record');

        if ($record instanceof Model) {
            return $record->getKey();
        }

        if ($record === null) {
            return null;
        }

        return (int) $record;
    }

    /**
     * @return list<string>
     */
    protected function booleanFields(): array
    {
        return match ($this->entityKey()) {
            'research-schemes' => ['is_active', 'requires_ethics_approval'],
            'community-service-schemes' => ['is_active', 'requires_partner'],
            'output-types' => ['is_active', 'is_measurable'],
            'funding-sources' => ['is_active', 'requires_contract'],
            'partner-types' => ['is_active', 'requires_legal_document'],
            'document-categories' => ['is_active', 'is_required'],
            'document-templates' => ['is_active', 'is_default'],
            'reviewers' => ['is_active'],
            'ip-types' => ['is_active'],
            'publication-types' => ['is_active', 'requires_issn_isbn'],
            'letter-types' => ['is_active', 'requires_approval'],
            'proposal-statuses' => ['is_active', 'is_terminal', 'is_editable_by_proposer'],
            default => ['is_active'],
        };
    }

    protected function prepareForValidation(): void
    {
        foreach ($this->booleanFields() as $field) {
            if (! $this->has($field)) {
                $this->merge([$field => false]);
            }
        }
    }
}
