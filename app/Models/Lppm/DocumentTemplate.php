<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentTemplate extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'template_code', 'name', 'description', 'document_category_id', 'module_type',
        'file_path', 'file_name', 'mime_type', 'file_size', 'version', 'is_default',
        'variables_schema', 'render_engine', 'blade_view',
        'sort_order', 'is_active', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'file_size' => 'integer',
            'variables_schema' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'document_category_id');
    }

    public function downloadUrl(): string
    {
        return route('admin.master.document-templates.download', $this);
    }
}
