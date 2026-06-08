<?php

namespace App\Models\Publication;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'lppm_publication_status_histories';

    protected $fillable = [
        'publication_id', 'from_status', 'to_status', 'transition', 'notes', 'acted_by', 'acted_at', 'metadata',
    ];

    protected function casts(): array
    {
        return ['acted_at' => 'datetime', 'metadata' => 'array'];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
