<?php

namespace App\Models\Research;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchBudgetItem extends Model
{
    protected $table = 'lppm_research_budget_items';

    protected $fillable = [
        'research_proposal_id', 'item_name', 'category', 'quantity', 'unit',
        'unit_price', 'subtotal', 'notes', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(ResearchProposal::class, 'research_proposal_id');
    }
}
