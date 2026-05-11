<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PostType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'group_id',
        'user_id',
        'type',
        'content',
        'attachments',
        'is_pinned',
        'is_announcement',
        'visibility',
        'likes_count',
        'comments_count',
        'shares_count',
        'shared_post_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => PostType::class,
            'attachments' => 'array',
            'is_pinned' => 'boolean',
            'is_announcement' => 'boolean',
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'shares_count' => 'integer',
        ];
    }

    // -------------------- Relationships --------------------

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function rootComments(): HasMany
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class);
    }

    public function sharedPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }

    // -------------------- Media --------------------

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
        $this->addMediaCollection('video')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 400)
            ->format('webp')
            ->performOnCollections('images');

        $this->addMediaConversion('display')
            ->fit(Fit::Contain, 1200, 1200)
            ->format('webp')
            ->performOnCollections('images');
    }

    // -------------------- Scopes --------------------

    public function scopePinned(Builder $query): Builder
    {
        return $query->where('is_pinned', true);
    }

    public function scopeAnnouncements(Builder $query): Builder
    {
        return $query->where('is_announcement', true);
    }

    public function scopeByType(Builder $query, PostType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    public function scopeVisible(Builder $query, ?User $user = null): Builder
    {
        return $query->where(function (Builder $q) use ($user): void {
            $q->where('visibility', 'public');

            if ($user) {
                $q->orWhereHas('group.memberships', function (Builder $sub) use ($user): void {
                    $sub->where('user_id', $user->id)
                        ->where('status', 'active');
                });
            }
        });
    }

    public function scopeOrderForFeed(Builder $query, string $filter = 'latest'): Builder
    {
        return match ($filter) {
            'popular'       => $query->orderByDesc('is_pinned')->orderByDesc('likes_count')->orderByDesc('created_at'),
            'announcements' => $query->where('is_announcement', true)->orderByDesc('is_pinned')->orderByDesc('created_at'),
            default         => $query->orderByDesc('is_pinned')->orderByDesc('created_at'),
        };
    }

    // -------------------- Helpers --------------------

    public function isPoll(): bool
    {
        return $this->type === PostType::Poll;
    }

    public function isLikedBy(User $user): bool
    {
        return $this->userReacted($user, 'like');
    }

    public function userReacted(User $user, ?string $type = null): bool
    {
        $query = $this->reactions()->where('user_id', $user->id);

        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query->exists();
    }
}