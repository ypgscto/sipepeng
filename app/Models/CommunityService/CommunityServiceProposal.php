<?php

namespace App\Models\CommunityService;

use App\Models\Lppm\CommunityServiceScheme;
use App\Models\Lppm\Partner;
use App\Models\Lppm\PartnerType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityServiceProposal extends Model
{
    use SoftDeletes;

    protected $table = 'lppm_community_service_proposals';

    protected $fillable = [
        'proposal_number', 'tahun_akademik_id', 'tahun_akademik_nama_snapshot',
        'semester_id', 'semester_nama_snapshot', 'prodi_id', 'prodi_nama_snapshot',
        'skema_id', 'judul', 'ketua_dosen_id', 'ketua_dosen_nama_snapshot', 'ketua_user_id',
        'mitra_id', 'mitra_nama_snapshot', 'jenis_mitra_id', 'jenis_mitra_nama_snapshot',
        'masalah_mitra', 'solusi_ditawarkan', 'target_capaian', 'metode_pelaksanaan',
        'lokasi_kegiatan', 'jadwal_mulai', 'jadwal_selesai', 'total_rab', 'target_luaran',
        'file_proposal', 'file_surat_mitra', 'file_pengesahan',
        'file_proposal_name', 'file_surat_mitra_name', 'file_pengesahan_name',
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
        return $this->belongsTo(CommunityServiceScheme::class, 'skema_id');
    }

    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'mitra_id');
    }

    public function jenisMitra(): BelongsTo
    {
        return $this->belongsTo(PartnerType::class, 'jenis_mitra_id');
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
        return $this->hasMany(PkmBudgetItem::class, 'community_service_proposal_id')->orderBy('sort_order');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(PkmStatusHistory::class, 'community_service_proposal_id')->orderByDesc('acted_at');
    }

    public function adminVerifications(): HasMany
    {
        return $this->hasMany(PkmAdminVerification::class, 'community_service_proposal_id')->orderByDesc('created_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(PkmReview::class, 'community_service_proposal_id');
    }

    public function latestAdminVerification(): HasOne
    {
        return $this->hasOne(PkmAdminVerification::class, 'community_service_proposal_id')->latestOfMany();
    }

    public function publications(): HasMany
    {
        return $this->hasMany(\App\Models\Publication\Publication::class, 'community_service_proposal_id');
    }

    public function ipRegistrations(): HasMany
    {
        return $this->hasMany(\App\Models\IntellectualProperty\IpRegistration::class, 'community_service_proposal_id');
    }

    public function letters(): HasMany
    {
        return $this->hasMany(\App\Models\Letter\Letter::class, 'community_service_proposal_id');
    }

    public function isEditable(): bool
    {
        return (bool) (config('sipepeng_community_service.statuses.'.$this->status.'.editable', false));
    }

    public function statusLabel(): string
    {
        return (string) (config('sipepeng_community_service.statuses.'.$this->status.'.label', $this->status));
    }

    public function statusColor(): string
    {
        return (string) (config('sipepeng_community_service.statuses.'.$this->status.'.color', '#64748b'));
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(config('sipepeng_community_service.view_all_roles', []))) {
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
