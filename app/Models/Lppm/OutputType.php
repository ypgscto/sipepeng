<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use Illuminate\Database\Eloquent\Model;

class OutputType extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'code', 'name', 'description', 'applies_to', 'is_measurable', 'unit_label',
        'sort_order', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_measurable' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
