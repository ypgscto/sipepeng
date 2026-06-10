<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'siakad_user_id',
        'siakad_login',
        'user_category',
        'jenis_user',
        'is_active',
        'is_allowed_login',
        'synced_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_allowed_login' => 'boolean',
            'synced_at' => 'datetime',
        ];
    }

    public function roleMappings(): HasMany
    {
        return $this->hasMany(SipepengUserRoleMapping::class);
    }

    public function activeRoleMappings(): HasMany
    {
        return $this->roleMappings()->active()->whereHas('role', fn ($q) => $q->active());
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(SipepengRole::class, 'sipepeng_user_role_mappings', 'user_id', 'role_id')
            ->withPivot(['is_primary', 'is_active', 'assigned_by', 'assigned_at', 'notes', 'deleted_at'])
            ->withTimestamps();
    }

    public function activeRoles(): BelongsToMany
    {
        return $this->roles()
            ->wherePivot('is_active', true)
            ->whereNull('sipepeng_user_role_mappings.deleted_at')
            ->where('sipepeng_roles.is_active', true);
    }

    public function hasRole(string $code): bool
    {
        return $this->activeRoles()->where('code', $code)->exists();
    }

    public function hasAnyRole(array $codes): bool
    {
        if ($codes === []) {
            return false;
        }

        return $this->activeRoles()->whereIn('code', $codes)->exists();
    }

    /**
     * @return list<string>
     */
    public function roleCodes(): array
    {
        return $this->activeRoles()
            ->orderBy('sipepeng_roles.sort_order')
            ->pluck('code')
            ->all();
    }

    public function primaryRoleCode(): ?string
    {
        $primary = $this->activeRoleMappings()
            ->where('is_primary', true)
            ->whereHas('role', fn ($q) => $q->active())
            ->with('role')
            ->first();

        if ($primary?->role) {
            return $primary->role->code;
        }

        return $this->roleCodes()[0] ?? null;
    }

    public function canLoginToSipepeng(): bool
    {
        return $this->is_active && $this->is_allowed_login;
    }

    public function isSiakadSourced(): bool
    {
        return filled($this->siakad_user_id) || filled($this->siakad_login);
    }

    public function scopeSiakadSourced($query)
    {
        return $query->where(function ($inner): void {
            $inner->whereNotNull('siakad_user_id')
                ->where('siakad_user_id', '!=', '')
                ->orWhereNotNull('siakad_login')
                ->where('siakad_login', '!=', '');
        });
    }

    public function scopeLocalOnly($query)
    {
        return $query->where(function ($inner): void {
            $inner->whereNull('siakad_user_id')
                ->orWhere('siakad_user_id', '=', '');
        })->where(function ($inner): void {
            $inner->whereNull('siakad_login')
                ->orWhere('siakad_login', '=', '');
        });
    }

    public function lppmNotifications(): HasMany
    {
        return $this->hasMany(\App\Models\Notification\LppmNotification::class);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->lppmNotifications()->unread()->count();
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name)) ?: [];
        $initials = collect($parts)
            ->filter()
            ->take(2)
            ->map(fn (string $part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        return $initials !== '' ? $initials : 'U';
    }
}
