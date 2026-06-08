<?php

namespace App\Models\Publication;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublicationAuthor extends Model
{
    protected $table = 'lppm_publication_authors';

    protected $fillable = [
        'publication_id', 'author_order', 'role', 'dosen_id', 'dosen_nama_snapshot',
        'user_id', 'prodi_id', 'prodi_nama_snapshot', 'affiliation_snapshot',
    ];

    protected function casts(): array
    {
        return ['author_order' => 'integer'];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
