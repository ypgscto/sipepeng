<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use Illuminate\Database\Eloquent\Model;

class ProposalStatus extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'code', 'name', 'description', 'proposal_type', 'stage', 'color',
        'is_terminal', 'is_editable_by_proposer', 'sort_order', 'is_active',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_terminal' => 'boolean',
            'is_editable_by_proposer' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
