<?php

namespace App\Models\Letter;

use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\IntellectualProperty\IpRegistration;
use App\Models\Lppm\LetterType;
use App\Models\Lppm\Partner;
use App\Models\Lppm\Reviewer;
use App\Models\Lppm\DocumentTemplate;
use App\Models\Publication\Publication;
use App\Models\Research\ResearchProposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Letter extends Model
{
    use SoftDeletes;

    protected $table = 'lppm_letters';

    protected $fillable = [
        'internal_number', 'letter_number', 'letter_type_id', 'document_template_id',
        'letter_prefix_snapshot', 'perihal', 'letter_date', 'place_of_issue', 'merge_variables',
        'research_proposal_id', 'community_service_proposal_id', 'partner_id', 'reviewer_id',
        'publication_id', 'ip_registration_id', 'proposal_number_snapshot', 'proposal_judul_snapshot',
        'ketua_dosen_id', 'ketua_dosen_nama_snapshot', 'prodi_id', 'prodi_nama_snapshot',
        'mitra_nama_snapshot', 'mitra_alamat_snapshot', 'reviewer_nama_snapshot',
        'recipient_external_name', 'recipient_external_institution', 'recipient_external_address',
        'event_date', 'event_time', 'event_location', 'event_agenda', 'body_content',
        'status', 'current_stage', 'revision_count', 'submitted_at', 'approved_at', 'issued_at',
        'file_pdf', 'file_pdf_name', 'file_signed_scan', 'file_signed_scan_name',
        'signed_uploaded_at', 'signed_uploaded_by', 'notes_internal', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'letter_date' => 'date',
            'event_date' => 'date',
            'merge_variables' => 'array',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'issued_at' => 'datetime',
            'signed_uploaded_at' => 'datetime',
            'revision_count' => 'integer',
        ];
    }

    public function letterType(): BelongsTo
    {
        return $this->belongsTo(LetterType::class, 'letter_type_id');
    }

    public function documentTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'document_template_id');
    }

    public function researchProposal(): BelongsTo
    {
        return $this->belongsTo(ResearchProposal::class, 'research_proposal_id');
    }

    public function communityServiceProposal(): BelongsTo
    {
        return $this->belongsTo(CommunityServiceProposal::class, 'community_service_proposal_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class, 'partner_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Reviewer::class, 'reviewer_id');
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    public function ipRegistration(): BelongsTo
    {
        return $this->belongsTo(IpRegistration::class, 'ip_registration_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(LetterRecipient::class, 'letter_id')->orderBy('sort_order');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LetterStatusHistory::class, 'letter_id')->orderByDesc('acted_at');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LetterApproval::class, 'letter_id')->orderByDesc('approved_at');
    }

    public function isEditable(): bool
    {
        return (bool) (config('sipepeng_letters.statuses.'.$this->status.'.editable', false));
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function statusLabel(): string
    {
        return (string) (config('sipepeng_letters.statuses.'.$this->status.'.label', $this->status));
    }

    public function statusColor(): string
    {
        return (string) (config('sipepeng_letters.statuses.'.$this->status.'.color', '#64748b'));
    }

    public function displayNumber(): string
    {
        return $this->letter_number ?? $this->internal_number;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(config('sipepeng_letters.view_all_roles', []))) {
            return $query;
        }

        if ($user->hasRole('dosen')) {
            return $query->where(function (Builder $q) use ($user): void {
                $q->where('created_by', $user->id)
                    ->orWhere('ketua_dosen_id', $user->siakad_login)
                    ->orWhere('ketua_dosen_id', $user->siakad_user_id)
                    ->orWhereHas('researchProposal', fn (Builder $rp) => $rp->where('ketua_user_id', $user->id))
                    ->orWhereHas('communityServiceProposal', fn (Builder $cp) => $cp->where('ketua_user_id', $user->id));
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
