<?php

namespace App\Models\IntellectualProperty;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IpInventor extends Model
{
    protected $table = 'lppm_ip_inventors';

    protected $fillable = [
        'ip_registration_id', 'inventor_order', 'dosen_id', 'dosen_nama_snapshot',
        'user_id', 'prodi_id', 'prodi_nama_snapshot', 'contribution_pct',
    ];

    protected function casts(): array
    {
        return [
            'inventor_order' => 'integer',
            'contribution_pct' => 'decimal:2',
        ];
    }

    public function ipRegistration(): BelongsTo
    {
        return $this->belongsTo(IpRegistration::class, 'ip_registration_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
