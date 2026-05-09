<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id', 'user_id', 'type', 'content',
        'attachments', 'parent_id', 'is_edited', 'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'attachments' => 'array',
            'is_edited'   => 'boolean',
            'edited_at'   => 'datetime',
        ];
    }

    /* ── Relationships ── */

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /* ── Scopes ── */

    public function scopeInConversation($q, int $conversationId)
    {
        return $q->where('conversation_id', $conversationId);
    }

    public function scopeUnread($q, User $user)
    {
        return $q->whereHas('conversation.participants', function ($sub) use ($user) {
            $sub->where('user_id', $user->id)
                ->whereNotNull('last_read_at')
                ->whereColumn('last_read_at', '<', 'messages.created_at');
        });
    }

    /* ── Helpers ── */

    public function isEditable(): bool
    {
        $windowMinutes = config('community.limits.message_edit_window_minutes', 15);

        return $this->created_at->diffInMinutes(now()) <= $windowMinutes;
    }
}
