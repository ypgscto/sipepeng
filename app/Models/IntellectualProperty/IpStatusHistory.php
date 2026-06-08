<?php

namespace App\Models\IntellectualProperty;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'lppm_ip_status_histories';

    protected $fillable = [
        'ip_registration_id', 'from_status', 'to_status', 'transition', 'notes', 'acted_by', 'acted_at', 'metadata',
    ];

    protected function casts(): array
    {
        return ['acted_at' => 'datetime', 'metadata' => 'array'];
    }

    public function ipRegistration(): BelongsTo
    {
        return $this->belongsTo(IpRegistration::class, 'ip_registration_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
