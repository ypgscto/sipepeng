<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use Illuminate\Database\Eloquent\Model;

class PartnerType extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'code', 'name', 'description', 'requires_legal_document', 'icon',
        'sort_order', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'requires_legal_document' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
