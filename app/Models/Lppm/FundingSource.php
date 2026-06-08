<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FundingSource extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'code', 'name', 'description', 'source_category', 'institution_name',
        'requires_contract', 'sort_order', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'requires_contract' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function researchSchemes(): BelongsToMany
    {
        return $this->belongsToMany(ResearchScheme::class, 'lppm_research_scheme_funding_sources');
    }
}
