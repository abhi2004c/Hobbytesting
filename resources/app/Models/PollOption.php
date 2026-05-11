<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PollOption extends Model
{
    use HasFactory;

    protected $fillable = ['poll_id', 'text', 'votes_count', 'order'];

    protected function casts(): array
    {
        return [
            'votes_count' => 'integer',
            'order' => 'integer',
        ];
    }

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function percentage(int $total): float
    {
        if ($total === 0) {
            return 0.0;
        }

        return round(($this->votes_count / $total) * 100, 1);
    }
}