<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Enums\RsvpStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Event extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'group_id',
        'creator_id',
        'title',
        'slug',
        'description',
        'type',
        'location',
        'latitude',
        'longitude',
        'online_url',
        'starts_at',
        'ends_at',
        'capacity',
        'is_recurring',
        'recurrence_rule',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'rsvp_count_cache',
    ];

    protected function casts(): array
    {
        return [
            'type' => EventType::class,
            'status' => EventStatus::class,
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'is_recurring' => 'boolean',
            'capacity' => 'integer',
            'rsvp_count_cache' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'starts_at', 'ends_at', 'status', 'capacity'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // -------------------- Relationships --------------------

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(EventRsvp::class);
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_rsvps')
            ->wherePivot('status', RsvpStatus::Going->value)
            ->withPivot(['status', 'note'])
            ->withTimestamps();
    }

    public function maybeAttendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_rsvps')
            ->wherePivot('status', RsvpStatus::Maybe->value)
            ->withPivot(['status', 'note'])
            ->withTimestamps();
    }

    public function waitlist(): HasMany
    {
        return $this->hasMany(EventWaitlist::class)->orderBy('position');
    }

    // -------------------- Media --------------------

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('card')
            ->fit(Fit::Crop, 600, 300)
            ->format('webp')
            ->performOnCollections('cover');

        $this->addMediaConversion('hero')
            ->fit(Fit::Crop, 1600, 600)
            ->format('webp')
            ->performOnCollections('cover');
    }

    // -------------------- Scopes --------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', EventStatus::Published->value);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now())
            ->where('status', EventStatus::Published->value)
            ->orderBy('starts_at');
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->where('ends_at', '<', now())
            ->orderByDesc('starts_at');
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('starts_at', today())
            ->where('status', EventStatus::Published->value);
    }

    public function scopeByType(Builder $query, EventType $type): Builder
    {
        return $query->where('type', $type->value);
    }

    public function scopeHasCapacity(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('capacity')
                ->orWhereColumn('rsvp_count_cache', '<', 'capacity');
        });
    }

    public function scopeBetween(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->whereBetween('starts_at', [$from, $to]);
    }

    // -------------------- Accessors / Helpers --------------------

    public function getIsFullyBookedAttribute(): bool
    {
        if ($this->capacity === null) {
            return false;
        }

        return $this->rsvp_count_cache >= $this->capacity;
    }

    public function getSpotsRemainingAttribute(): ?int
    {
        if ($this->capacity === null) {
            return null;
        }

        return max(0, $this->capacity - $this->rsvp_count_cache);
    }

    public function getDurationInMinutesAttribute(): int
    {
        return (int) $this->starts_at->diffInMinutes($this->ends_at);
    }

    public function getFormattedDurationAttribute(): string
    {
        $minutes = $this->duration_in_minutes;

        if ($minutes < 60) {
            return "{$minutes} min";
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        return $remaining === 0 ? "{$hours}h" : "{$hours}h {$remaining}m";
    }

    public function getCoverUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('cover', 'card')
            ?: asset('images/event-placeholder.jpg');
    }

    public function isCancelled(): bool
    {
        return $this->status === EventStatus::Cancelled;
    }

    public function isPublished(): bool
    {
        return $this->status === EventStatus::Published;
    }

    public function hasStarted(): bool
    {
        return $this->starts_at->isPast();
    }

    public function hasEnded(): bool
    {
        return $this->ends_at->isPast();
    }

    public function hasCapacityFor(int $count = 1): bool
    {
        if ($this->capacity === null) {
            return true;
        }

        return ($this->rsvp_count_cache + $count) <= $this->capacity;
    }

    public function refreshRsvpCount(): void
    {
        $count = $this->rsvps()
            ->where('status', RsvpStatus::Going->value)
            ->count();

        $this->forceFill(['rsvp_count_cache' => $count])->saveQuietly();
    }

    public function getRsvpFor(User $user): ?EventRsvp
    {
        return $this->rsvps()->where('user_id', $user->id)->first();
    }

    public function isUserAttending(User $user): bool
    {
        return $this->rsvps()
            ->where('user_id', $user->id)
            ->where('status', RsvpStatus::Going->value)
            ->exists();
    }
}