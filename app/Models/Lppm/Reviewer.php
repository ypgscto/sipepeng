<?php

namespace App\Models\Lppm;

use App\Models\Lppm\Concerns\LppmMasterTrait;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reviewer extends Model
{
    use LppmMasterTrait;

    protected $fillable = [
        'user_id', 'expertise_notes', 'science_cluster_id', 'focus_area_id',
        'max_active_reviews', 'is_active', 'appointed_at', 'appointed_by',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'appointed_at' => 'date',
            'max_active_reviews' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scienceCluster(): BelongsTo
    {
        return $this->belongsTo(ScienceCluster::class);
    }

    public function focusArea(): BelongsTo
    {
        return $this->belongsTo(FocusArea::class);
    }

    public function appointedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'appointed_by');
    }
}
