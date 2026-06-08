<?php

namespace App\Models\ResearchEthics;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResearchEthicsStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'lppm_research_ethics_status_histories';

    protected $fillable = [
        'ethics_application_id', 'from_status', 'to_status', 'transition', 'notes', 'acted_by', 'acted_at', 'metadata',
    ];

    protected function casts(): array
    {
        return ['acted_at' => 'datetime', 'metadata' => 'array'];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ResearchEthicsApplication::class, 'ethics_application_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
