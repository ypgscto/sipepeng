<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'code', 'name', 'description', 'module_type', 'is_required',
        'sort_order', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function templates(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class, 'document_category_id');
    }
}
