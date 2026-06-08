<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiakadApiLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'purpose',
        'http_method',
        'endpoint',
        'request_query',
        'request_body',
        'response_status',
        'response_body',
        'duration_ms',
        'is_success',
        'error_message',
        'triggered_by',
        'correlation_id',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'request_query' => 'array',
            'request_body' => 'array',
            'is_success' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}
