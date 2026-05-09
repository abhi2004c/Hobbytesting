<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'name', 'group_id', 'created_by'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /* ── Relationships ── */

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['role', 'last_read_at', 'is_muted', 'joined_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /* ── Accessors ── */

    public function getLastMessageAttribute(): ?Message
    {
        return $this->messages()->latest()->first();
    }

    /* ── Scopes ── */

    public function scopeDirect($q)
    {
        return $q->where('type', 'direct');
    }

    public function scopeGroup($q)
    {
        return $q->where('type', 'group');
    }

    /* ── Helpers ── */

    public function isParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function getUnreadCountFor(User $user): int
    {
        $participant = $this->participants()->where('user_id', $user->id)->first();

        if (! $participant || ! $participant->last_read_at) {
            return $this->messages()->where('user_id', '!=', $user->id)->count();
        }

        return $this->messages()
            ->where('user_id', '!=', $user->id)
            ->where('created_at', '>', $participant->last_read_at)
            ->count();
    }
}
