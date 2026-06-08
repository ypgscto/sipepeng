<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ResearchScheme extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'code', 'name', 'description', 'academic_year_label', 'max_budget',
        'min_team_members', 'max_team_members', 'requires_ethics_approval',
        'submission_deadline', 'guideline_url', 'sort_order', 'is_active',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'max_budget' => 'decimal:2',
            'requires_ethics_approval' => 'boolean',
            'is_active' => 'boolean',
            'submission_deadline' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function fundingSources(): BelongsToMany
    {
        return $this->belongsToMany(FundingSource::class, 'lppm_research_scheme_funding_sources');
    }
}
