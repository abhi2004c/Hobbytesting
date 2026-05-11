<?php

declare(strict_types=1);

namespace App\Domain\Messaging\Repositories\Contracts;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface MessageRepositoryInterface
{
    public function getConversationMessages(int $conversationId, int $perPage = 30): LengthAwarePaginator;
    public function getUserConversations(int $userId): Collection;
    public function findDirectConversation(int $userAId, int $userBId): ?Conversation;
    public function createMessage(array $data): Message;
    public function getUnreadCountForUser(int $userId): int;
}
