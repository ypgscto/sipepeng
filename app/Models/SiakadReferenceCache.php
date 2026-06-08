<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiakadReferenceCache extends Model
{
    protected $table = 'siakad_reference_cache';

    protected $fillable = [
        'resource_key',
        'payload',
        'record_count',
        'meta',
        'fetched_at',
        'expires_at',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'meta' => 'array',
            'fetched_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
