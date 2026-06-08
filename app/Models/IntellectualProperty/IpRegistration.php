<?php

namespace App\Models\IntellectualProperty;

use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\Lppm\IpType;
use App\Models\Research\ResearchProposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IpRegistration extends Model
{
    use SoftDeletes;

    protected $table = 'lppm_ip_registrations';

    protected $fillable = [
        'registration_number', 'ip_type_id', 'judul', 'description', 'registration_body',
        'application_number', 'certificate_number', 'application_date', 'registration_date', 'expiry_date',
        'ownership_type', 'prodi_id', 'prodi_nama_snapshot', 'source_type',
        'research_proposal_id', 'community_service_proposal_id', 'proposal_number_snapshot', 'proposal_judul_snapshot',
        'file_application', 'file_statement', 'file_certificate', 'file_supporting',
        'file_application_name', 'file_statement_name', 'file_certificate_name', 'file_supporting_name',
        'status', 'current_stage', 'revision_count', 'submitted_at', 'verified_at', 'notes_internal',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'application_date' => 'date',
            'registration_date' => 'date',
            'expiry_date' => 'date',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'revision_count' => 'integer',
        ];
    }

    public function ipType(): BelongsTo
    {
        return $this->belongsTo(IpType::class, 'ip_type_id');
    }

    public function researchProposal(): BelongsTo
    {
        return $this->belongsTo(ResearchProposal::class, 'research_proposal_id');
    }

    public function communityServiceProposal(): BelongsTo
    {
        return $this->belongsTo(CommunityServiceProposal::class, 'community_service_proposal_id');
    }

    public function inventors(): HasMany
    {
        return $this->hasMany(IpInventor::class, 'ip_registration_id')->orderBy('inventor_order');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(IpStatusHistory::class, 'ip_registration_id')->orderByDesc('acted_at');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(IpVerification::class, 'ip_registration_id')->orderByDesc('created_at');
    }

    public function isEditable(): bool
    {
        return (bool) (config('sipepeng_hki.statuses.'.$this->status.'.editable', false));
    }

    public function statusLabel(): string
    {
        return (string) (config('sipepeng_hki.statuses.'.$this->status.'.label', $this->status));
    }

    public function statusColor(): string
    {
        return (string) (config('sipepeng_hki.statuses.'.$this->status.'.color', '#64748b'));
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(config('sipepeng_hki.view_all_roles', []))) {
            return $query;
        }

        if ($user->hasRole('dosen')) {
            return $query->where(function (Builder $q) use ($user): void {
                $q->where('created_by', $user->id)
                    ->orWhereHas('inventors', function (Builder $i) use ($user): void {
                        $i->where('user_id', $user->id);
                        if (filled($user->siakad_login)) {
                            $i->orWhere('dosen_id', $user->siakad_login);
                        }
                        if (filled($user->siakad_user_id)) {
                            $i->orWhere('dosen_id', $user->siakad_user_id);
                        }
                    });
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
