<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\GroupPrivacy;
use App\Enums\MemberRole;
use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Group extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'owner_id', 'category_id', 'name', 'slug', 'description',
        'privacy', 'max_members', 'location', 'latitude', 'longitude',
        'is_verified', 'is_featured', 'settings', 'member_count_cache',
    ];

    protected function casts(): array
    {
        return [
            'privacy'     => GroupPrivacy::class,
            'settings'    => 'array',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'latitude'    => 'decimal:7',
            'longitude'   => 'decimal:7',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /* ── Relationships ── */

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(GroupCategory::class, 'category_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_memberships')
            ->withPivot(['role', 'status', 'joined_at'])
            ->wherePivot('status', MemberStatus::Active->value)
            ->withTimestamps();
    }

    public function pendingMembers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_memberships')
            ->withPivot(['role', 'status'])
            ->wherePivot('status', MemberStatus::Pending->value);
    }

    public function admins(): BelongsToMany
    {
        return $this->members()->wherePivotIn('role', [
            MemberRole::Owner->value,
            MemberRole::Admin->value,
        ]);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(GroupInvitation::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /* ── Scopes ── */

    public function scopePublic(Builder $q): Builder
    {
        return $q->where('privacy', GroupPrivacy::Public->value);
    }

    public function scopeVerified(Builder $q): Builder
    {
        return $q->where('is_verified', true);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        return $term
            ? $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%"))
            : $q;
    }

    public function scopeInCategory(Builder $q, ?int $categoryId): Builder
    {
        return $categoryId ? $q->where('category_id', $categoryId) : $q;
    }

    /* ── Media ── */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('card')
            ->fit(\Spatie\Image\Enums\Fit::Crop, 600, 300)
            ->format('webp')
            ->performOnCollections('cover');

        $this->addMediaConversion('hero')
            ->fit(\Spatie\Image\Enums\Fit::Crop, 1600, 600)
            ->format('webp')
            ->performOnCollections('cover');
    }

    /* ── Helpers ── */

    public function isMember(?User $user): bool
    {
        if (! $user) return false;

        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('status', MemberStatus::Active->value)
            ->exists();
    }

    public function getMemberRole(?User $user): ?MemberRole
    {
        if (! $user) return null;

        $role = $this->memberships()
            ->where('user_id', $user->id)
            ->where('status', MemberStatus::Active->value)
            ->value('role');

        return $role instanceof MemberRole ? $role : ($role ? MemberRole::from($role) : null);
    }

    public function isAdmin(?User $user): bool
    {
        $role = $this->getMemberRole($user);
        return $role && in_array($role, [MemberRole::Owner, MemberRole::Admin], true);
    }

    public function canPost(?User $user): bool
    {
        return $this->isMember($user);
    }

    public function hasReachedMemberLimit(): bool
    {
        if (! $this->max_members) return false;

        return $this->member_count_cache >= $this->max_members;
    }

    public function refreshMemberCount(): int
    {
        $count = $this->memberships()
            ->where('status', MemberStatus::Active->value)
            ->count();

        $this->forceFill(['member_count_cache' => $count])->save();

        return $count;
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('cover', 'card') ?: null;
    }
}