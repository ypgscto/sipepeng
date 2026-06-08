<?php

namespace App\Models\ResearchEthics;

use App\Models\Lppm\Reviewer;
use App\Models\Research\ResearchProposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchEthicsApplication extends Model
{
    use SoftDeletes;

    protected $table = 'lppm_research_ethics_applications';

    protected $fillable = [
        'application_number', 'research_proposal_id', 'proposal_number_snapshot', 'proposal_judul_snapshot',
        'ketua_dosen_id', 'ketua_dosen_nama_snapshot', 'ketua_user_id', 'prodi_id', 'prodi_nama_snapshot',
        'study_type', 'population_description', 'risk_level', 'data_collection_method',
        'informed_consent_required', 'conflict_of_interest_declared', 'valid_from', 'valid_until',
        'file_protocol', 'file_ethics_application', 'file_consent_form', 'file_approval_letter',
        'file_protocol_name', 'file_ethics_application_name', 'file_consent_form_name', 'file_approval_letter_name',
        'status', 'current_stage', 'revision_count', 'submitted_at', 'approved_at', 'committee_notes',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'informed_consent_required' => 'boolean',
            'conflict_of_interest_declared' => 'boolean',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'revision_count' => 'integer',
        ];
    }

    public function researchProposal(): BelongsTo
    {
        return $this->belongsTo(ResearchProposal::class, 'research_proposal_id');
    }

    public function ketuaUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ResearchEthicsReview::class, 'ethics_application_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ResearchEthicsStatusHistory::class, 'ethics_application_id')->orderByDesc('acted_at');
    }

    public function isEditable(): bool
    {
        return (bool) (config('sipepeng_ethics.statuses.'.$this->status.'.editable', false));
    }

    public function statusLabel(): string
    {
        return (string) (config('sipepeng_ethics.statuses.'.$this->status.'.label', $this->status));
    }

    public function statusColor(): string
    {
        return (string) (config('sipepeng_ethics.statuses.'.$this->status.'.color', '#64748b'));
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(config('sipepeng_ethics.view_all_roles', []))) {
            return $query;
        }

        if ($user->hasRole('reviewer')) {
            return $query->whereHas('reviews', fn (Builder $q) => $q->where('reviewer_user_id', $user->id));
        }

        if ($user->hasRole('dosen')) {
            return $query->where(function (Builder $q) use ($user): void {
                $q->where('ketua_user_id', $user->id);
                if (filled($user->siakad_login)) {
                    $q->orWhere('ketua_dosen_id', $user->siakad_login);
                }
                if (filled($user->siakad_user_id)) {
                    $q->orWhere('ketua_dosen_id', $user->siakad_user_id);
                }
                $q->orWhere('created_by', $user->id);
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
