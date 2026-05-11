<?php

declare(strict_types=1);

namespace App\Domain\Messaging\Repositories;

use App\Domain\Messaging\Repositories\Contracts\MessageRepositoryInterface;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository implements MessageRepositoryInterface
{
    public function getConversationMessages(int $conversationId, int $perPage = 30): LengthAwarePaginator
    {
        return Message::where('conversation_id', $conversationId)
            ->with(['user'])
            ->latest()
            ->paginate($perPage);
    }

    public function getUserConversations(int $userId): Collection
    {
        return Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->with(['users', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('updated_at')
            ->get();
    }

    public function findDirectConversation(int $userAId, int $userBId): ?Conversation
    {
        return Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userAId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userBId))
            ->first();
    }

    public function createMessage(array $data): Message
    {
        return Message::create($data);
    }

    public function getUnreadCountForUser(int $userId): int
    {
        $participantConvos = ConversationParticipant::where('user_id', $userId)->get();

        $total = 0;
        foreach ($participantConvos as $cp) {
            $query = Message::where('conversation_id', $cp->conversation_id)
                ->where('user_id', '!=', $userId);

            if ($cp->last_read_at) {
                $query->where('created_at', '>', $cp->last_read_at);
            }

            $total += $query->count();
        }

        return $total;
    }
}
