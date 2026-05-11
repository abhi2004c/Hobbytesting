<?php

declare(strict_types=1);

namespace App\Domain\Feed\Services;

use App\Enums\ReactionType;
use App\Events\Feed\PostReacted;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReactionService
{
    /**
     * Toggle: if user already reacted with same type → remove. Otherwise create/replace.
     *
     * @return array{reacted: bool, type: ?ReactionType, summary: array<string, int>}
     */
    public function react(Model $reactable, User $user, ReactionType $type): array
    {
        return DB::transaction(function () use ($reactable, $user, $type): array {
            $existingSame = Reaction::query()
                ->where('reactable_type', $reactable->getMorphClass())
                ->where('reactable_id', $reactable->getKey())
                ->where('user_id', $user->id)
                ->where('type', $type->value)
                ->first();

            if ($existingSame) {
                $existingSame->delete();
                $this->decrementCounter($reactable);

                return [
                    'reacted' => false,
                    'type' => null,
                    'summary' => $this->getReactionSummary($reactable),
                ];
            }

            // Remove any other reaction by this user on this entity (one reaction per user)
            $deleted = Reaction::query()
                ->where('reactable_type', $reactable->getMorphClass())
                ->where('reactable_id', $reactable->getKey())
                ->where('user_id', $user->id)
                ->delete();

            if ($deleted > 0) {
                $this->decrementCounter($reactable);
            }

            Reaction::query()->create([
                'reactable_type' => $reactable->getMorphClass(),
                'reactable_id' => $reactable->getKey(),
                'user_id' => $user->id,
                'type' => $type->value,
            ]);

            $this->incrementCounter($reactable);

            if ($reactable instanceof Post) {
                PostReacted::dispatch($reactable->fresh(), $user->id, $type);
            }

            return [
                'reacted' => true,
                'type' => $type,
                'summary' => $this->getReactionSummary($reactable),
            ];
        });
    }

    public function unreact(Model $reactable, User $user): bool
    {
        return DB::transaction(function () use ($reactable, $user): bool {
            $deleted = Reaction::query()
                ->where('reactable_type', $reactable->getMorphClass())
                ->where('reactable_id', $reactable->getKey())
                ->where('user_id', $user->id)
                ->delete();

            if ($deleted > 0) {
                $this->decrementCounter($reactable, $deleted);
            }

            return $deleted > 0;
        });
    }

    /**
     * @return array<string, int>
     */
    public function getReactionSummary(Model $reactable): array
    {
        $rows = Reaction::query()
            ->selectRaw('type, COUNT(*) as count')
            ->where('reactable_type', $reactable->getMorphClass())
            ->where('reactable_id', $reactable->getKey())
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $summary = [];
        foreach (ReactionType::cases() as $type) {
            $summary[$type->value] = (int) ($rows[$type->value] ?? 0);
        }

        return $summary;
    }

    private function incrementCounter(Model $reactable): void
    {
        if ($reactable instanceof Post || $reactable instanceof Comment) {
            $reactable->increment('likes_count');
        }
    }

    private function decrementCounter(Model $reactable, int $by = 1): void
    {
        if ($reactable instanceof Post || $reactable instanceof Comment) {
            $reactable->decrement('likes_count', $by);
        }
    }
}