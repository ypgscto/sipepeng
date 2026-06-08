<?php

namespace App\Models\Research;

use App\Models\Lppm\FocusArea;
use App\Models\Lppm\ResearchScheme;
use App\Models\Lppm\ScienceCluster;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchProposal extends Model
{
    use SoftDeletes;

    protected $table = 'lppm_research_proposals';

    protected $fillable = [
        'proposal_number', 'tahun_akademik_id', 'tahun_akademik_nama_snapshot',
        'semester_id', 'semester_nama_snapshot', 'prodi_id', 'prodi_nama_snapshot',
        'skema_id', 'judul', 'ketua_dosen_id', 'ketua_dosen_nama_snapshot', 'ketua_user_id',
        'bidang_fokus_id', 'rumpun_ilmu_id', 'ringkasan', 'latar_belakang', 'rumusan_masalah',
        'tujuan', 'manfaat', 'metode', 'lokasi', 'jadwal_mulai', 'jadwal_selesai',
        'total_rab', 'target_luaran', 'file_proposal', 'file_pengesahan', 'file_pernyataan',
        'file_proposal_name', 'file_pengesahan_name', 'file_pernyataan_name',
        'status', 'current_stage', 'revision_count', 'submitted_at',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'jadwal_mulai' => 'date',
            'jadwal_selesai' => 'date',
            'total_rab' => 'decimal:2',
            'submitted_at' => 'datetime',
            'revision_count' => 'integer',
        ];
    }

    public function skema(): BelongsTo
    {
        return $this->belongsTo(ResearchScheme::class, 'skema_id');
    }

    public function bidangFokus(): BelongsTo
    {
        return $this->belongsTo(FocusArea::class, 'bidang_fokus_id');
    }

    public function rumpunIlmu(): BelongsTo
    {
        return $this->belongsTo(ScienceCluster::class, 'rumpun_ilmu_id');
    }

    public function ketuaUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ketua_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function budgetItems(): HasMany
    {
        return $this->hasMany(ResearchBudgetItem::class, 'research_proposal_id')->orderBy('sort_order');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ResearchProposalStatusHistory::class, 'research_proposal_id')->orderByDesc('acted_at');
    }

    public function adminVerifications(): HasMany
    {
        return $this->hasMany(ResearchAdminVerification::class, 'research_proposal_id')->orderByDesc('created_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ResearchReview::class, 'research_proposal_id');
    }

    public function latestAdminVerification(): HasOne
    {
        return $this->hasOne(ResearchAdminVerification::class, 'research_proposal_id')->latestOfMany();
    }

    public function publications(): HasMany
    {
        return $this->hasMany(\App\Models\Publication\Publication::class, 'research_proposal_id');
    }

    public function ipRegistrations(): HasMany
    {
        return $this->hasMany(\App\Models\IntellectualProperty\IpRegistration::class, 'research_proposal_id');
    }

    public function ethicsApplications(): HasMany
    {
        return $this->hasMany(\App\Models\ResearchEthics\ResearchEthicsApplication::class, 'research_proposal_id');
    }

    public function letters(): HasMany
    {
        return $this->hasMany(\App\Models\Letter\Letter::class, 'research_proposal_id');
    }

    public function isEditable(): bool
    {
        return (bool) (config('sipepeng_research.statuses.'.$this->status.'.editable', false));
    }

    public function statusLabel(): string
    {
        return (string) (config('sipepeng_research.statuses.'.$this->status.'.label', $this->status));
    }

    public function statusColor(): string
    {
        return (string) (config('sipepeng_research.statuses.'.$this->status.'.color', '#64748b'));
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(config('sipepeng_research.view_all_roles', []))) {
            return $query;
        }

        if ($user->hasRole('reviewer')) {
            return $query->whereHas('reviews', function (Builder $q) use ($user): void {
                $q->whereHas('reviewer', fn (Builder $r) => $r->where('user_id', $user->id));
            });
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
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
