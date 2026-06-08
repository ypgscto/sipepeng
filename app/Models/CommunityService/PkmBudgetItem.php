<?php

namespace App\Models\CommunityService;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PkmBudgetItem extends Model
{
    protected $table = 'lppm_pkm_budget_items';

    protected $fillable = [
        'community_service_proposal_id', 'item_name', 'category', 'quantity',
        'unit', 'unit_price', 'subtotal', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(CommunityServiceProposal::class, 'community_service_proposal_id');
    }
}
