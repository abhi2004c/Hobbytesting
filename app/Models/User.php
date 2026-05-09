<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasRoles, InteractsWithMedia, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'bio', 'city', 'country',
        'website', 'date_of_birth', 'gender', 'is_verified', 'status',
        'google_id', 'last_login_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'google_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at'     => 'datetime',
            'date_of_birth'     => 'date',
            'is_verified'       => 'boolean',
            'password'          => 'hashed',
        ];
    }

    /* ────────────────────────  Relationships  ──────────────────────── */

    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Interest::class, 'user_interests')->withTimestamps();
    }

    public function ownedGroups(): HasMany
    {
        return $this->hasMany(Group::class, 'owner_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'group_memberships')
            ->withPivot(['role', 'status', 'joined_at'])
            ->wherePivot('status', 'active')
            ->withTimestamps();
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'creator_id');
    }

    public function eventRsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function attendingEvents(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_rsvps')
            ->withPivot(['status', 'note'])
            ->wherePivot('status', 'going')
            ->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['role', 'last_read_at', 'is_muted', 'joined_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /* ────────────────────────  Scopes  ──────────────────────── */

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('status', 'active');
    }

    public function scopeVerified(Builder $q): Builder
    {
        return $q->where('is_verified', true)->whereNotNull('email_verified_at');
    }

    public function scopeByCity(Builder $q, string $city): Builder
    {
        return $q->where('city', $city);
    }

    /* ────────────────────────  Accessors  ──────────────────────── */

    public function getAvatarUrlAttribute(): string
    {
        $media = $this->getFirstMediaUrl('avatar', 'thumb');

        return $media
            ?: $this->avatar
            ?: 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=4f46e5&color=fff&size=200';
    }

    public function getFullLocationAttribute(): ?string
    {
        return collect([$this->city, $this->country])->filter()->implode(', ') ?: null;
    }

    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->diffInYears(now());
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->hasRole('platform_admin');
    }

    /* ────────────────────────  Media  ──────────────────────── */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(\Spatie\Image\Enums\Fit::Crop, 200, 200)
            ->format('webp')
            ->performOnCollections('avatar');

        $this->addMediaConversion('medium')
            ->fit(\Spatie\Image\Enums\Fit::Crop, 600, 600)
            ->format('webp')
            ->performOnCollections('avatar', 'cover');
    }

    /* ────────────────────────  Helpers  ──────────────────────── */

    public function recordLogin(): void
    {
        $this->forceFill(['last_login_at' => now()])->save();
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }
}