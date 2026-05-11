<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RsvpStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRsvp extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'note',
        'reminder_24h_sent_at',
        'reminder_1h_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => RsvpStatus::class,
            'reminder_24h_sent_at' => 'datetime',
            'reminder_1h_sent_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isGoing(): bool
    {
        return $this->status === RsvpStatus::Going;
    }
}