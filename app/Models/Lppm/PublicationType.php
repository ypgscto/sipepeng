<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use Illuminate\Database\Eloquent\Model;

class PublicationType extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'code', 'name', 'description', 'indexing_type', 'requires_issn_isbn', 'feeder_code',
        'sort_order', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'requires_issn_isbn' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
