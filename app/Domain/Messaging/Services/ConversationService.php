<?php

declare(strict_types=1);

namespace App\Domain\Messaging\Services;

use App\Domain\Messaging\DTOs\CreateConversationDTO;
use App\Domain\Messaging\Repositories\Contracts\MessageRepositoryInterface;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class ConversationService
{
    public function __construct(
        private readonly MessageRepositoryInterface $repo,
    ) {}

    public function findOrCreateDirect(User $userA, User $userB): Conversation
    {
        $existing = $this->repo->findDirectConversation($userA->id, $userB->id);

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($userA, $userB) {
            $conv = Conversation::create([
                'type'       => 'direct',
                'created_by' => $userA->id,
            ]);

            ConversationParticipant::insert([
                [
                    'conversation_id' => $conv->id,
                    'user_id'         => $userA->id,
                    'role'            => 'member',
                    'joined_at'       => now(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
                [
                    'conversation_id' => $conv->id,
                    'user_id'         => $userB->id,
                    'role'            => 'member',
                    'joined_at'       => now(),
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ],
            ]);

            return $conv->fresh(['users']);
        });
    }

    public function createGroupConversation(array $userIds, string $name, User $createdBy): Conversation
    {
        return DB::transaction(function () use ($userIds, $name, $createdBy) {
            $conv = Conversation::create([
                'type'       => 'group',
                'name'       => $name,
                'created_by' => $createdBy->id,
            ]);

            $allIds = array_unique(array_merge($userIds, [$createdBy->id]));

            $participants = collect($allIds)->map(fn (int $uid) => [
                'conversation_id' => $conv->id,
                'user_id'         => $uid,
                'role'            => $uid === $createdBy->id ? 'admin' : 'member',
                'joined_at'       => now(),
                'created_at'      => now(),
                'updated_at'      => now(),
            ])->all();

            ConversationParticipant::insert($participants);

            return $conv->fresh(['users']);
        });
    }

    public function getUserConversations(User $user): Collection
    {
        return $this->repo->getUserConversations($user->id);
    }

    public function getUnreadCount(User $user): int
    {
        return Cache::remember(
            "user:{$user->id}:unread_messages",
            config('community.cache_ttl.unread_messages', 30),
            fn () => $this->repo->getUnreadCountForUser($user->id),
        );
    }

    public function markAsRead(Conversation $conv, User $user): void
    {
        ConversationParticipant::where('conversation_id', $conv->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        Cache::forget("user:{$user->id}:unread_messages");
    }

    public function addParticipant(Conversation $conv, User $user): void
    {
        ConversationParticipant::firstOrCreate(
            ['conversation_id' => $conv->id, 'user_id' => $user->id],
            ['role' => 'member', 'joined_at' => now()],
        );
    }

    public function removeParticipant(Conversation $conv, User $user): void
    {
        ConversationParticipant::where('conversation_id', $conv->id)
            ->where('user_id', $user->id)
            ->delete();
    }
}
