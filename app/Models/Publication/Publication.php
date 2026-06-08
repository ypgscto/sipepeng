<?php

namespace App\Models\Publication;

use App\Models\CommunityService\CommunityServiceProposal;
use App\Models\Lppm\OutputType;
use App\Models\Lppm\PublicationType;
use App\Models\Research\ResearchProposal;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publication extends Model
{
    use SoftDeletes;

    protected $table = 'lppm_publications';

    protected $fillable = [
        'registration_number', 'publication_type_id', 'judul', 'abstract', 'journal_or_publisher',
        'issn', 'isbn', 'doi', 'url', 'indexing_label', 'publication_year', 'publication_date',
        'volume', 'issue', 'pages', 'prodi_id', 'prodi_nama_snapshot', 'source_type',
        'research_proposal_id', 'community_service_proposal_id', 'proposal_number_snapshot',
        'proposal_judul_snapshot', 'output_type_id',
        'file_manuscript', 'file_acceptance_letter', 'file_published', 'file_other',
        'file_manuscript_name', 'file_acceptance_letter_name', 'file_published_name', 'file_other_name',
        'status', 'current_stage', 'revision_count', 'submitted_at', 'verified_at', 'notes_internal',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'date',
            'publication_year' => 'integer',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'revision_count' => 'integer',
        ];
    }

    public function publicationType(): BelongsTo
    {
        return $this->belongsTo(PublicationType::class, 'publication_type_id');
    }

    public function researchProposal(): BelongsTo
    {
        return $this->belongsTo(ResearchProposal::class, 'research_proposal_id');
    }

    public function communityServiceProposal(): BelongsTo
    {
        return $this->belongsTo(CommunityServiceProposal::class, 'community_service_proposal_id');
    }

    public function outputType(): BelongsTo
    {
        return $this->belongsTo(OutputType::class, 'output_type_id');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(PublicationAuthor::class, 'publication_id')->orderBy('author_order');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(PublicationStatusHistory::class, 'publication_id')->orderByDesc('acted_at');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(PublicationVerification::class, 'publication_id')->orderByDesc('created_at');
    }

    public function leadAuthor(): ?PublicationAuthor
    {
        return $this->authors()->where('role', 'lead')->first()
            ?? $this->authors()->orderBy('author_order')->first();
    }

    public function isEditable(): bool
    {
        return (bool) (config('sipepeng_publication.statuses.'.$this->status.'.editable', false));
    }

    public function statusLabel(): string
    {
        return (string) (config('sipepeng_publication.statuses.'.$this->status.'.label', $this->status));
    }

    public function statusColor(): string
    {
        return (string) (config('sipepeng_publication.statuses.'.$this->status.'.color', '#64748b'));
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(config('sipepeng_publication.view_all_roles', []))) {
            return $query;
        }

        if ($user->hasRole('dosen')) {
            return $query->where(function (Builder $q) use ($user): void {
                $q->where('created_by', $user->id)
                    ->orWhereHas('authors', function (Builder $a) use ($user): void {
                        $a->where('user_id', $user->id);
                        if (filled($user->siakad_login)) {
                            $a->orWhere('dosen_id', $user->siakad_login);
                        }
                        if (filled($user->siakad_user_id)) {
                            $a->orWhere('dosen_id', $user->siakad_user_id);
                        }
                    });
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
