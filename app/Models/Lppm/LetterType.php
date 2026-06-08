<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterType extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'code', 'name', 'description', 'letter_prefix', 'document_template_id',
        'requires_approval', 'applies_to', 'number_format_pattern', 'requires_proposal_link',
        'requires_partner_link', 'min_proposal_status', 'allow_dosen_create',
        'sort_order', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'requires_approval' => 'boolean',
            'requires_proposal_link' => 'boolean',
            'requires_partner_link' => 'boolean',
            'allow_dosen_create' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function documentTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class);
    }
}
