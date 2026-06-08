<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use Illuminate\Database\Eloquent\Model;

class IpType extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'code', 'name', 'description', 'registration_body', 'typical_duration_months',
        'sort_order', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'typical_duration_months' => 'integer',
        ];
    }
}
