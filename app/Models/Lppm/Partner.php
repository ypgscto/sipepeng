<?php

namespace App\Models\Lppm;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use SoftDeletes;

    protected $table = 'lppm_partners';

    protected $fillable = [
        'partner_code', 'name', 'partner_type_id', 'legal_name', 'address', 'city',
        'contact_person', 'contact_phone', 'contact_email', 'is_active',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function partnerType(): BelongsTo
    {
        return $this->belongsTo(PartnerType::class, 'partner_type_id');
    }

    public function communityServiceProposals(): HasMany
    {
        return $this->hasMany(\App\Models\CommunityService\CommunityServiceProposal::class, 'mitra_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
