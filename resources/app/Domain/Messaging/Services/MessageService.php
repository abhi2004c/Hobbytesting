<?php

declare(strict_types=1);

namespace App\Domain\Messaging\Services;

use App\Domain\Messaging\DTOs\SendMessageDTO;
use App\Domain\Messaging\Repositories\Contracts\MessageRepositoryInterface;
use App\Events\Messaging\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class MessageService
{
    public function __construct(
        private readonly MessageRepositoryInterface $repo,
    ) {}

    public function send(Conversation $conv, User $sender, SendMessageDTO $dto): Message
    {
        return DB::transaction(function () use ($conv, $sender, $dto) {
            $message = $this->repo->createMessage([
                'conversation_id' => $conv->id,
                'user_id'         => $sender->id,
                'type'            => $dto->type,
                'content'         => $dto->content,
                'parent_id'       => $dto->parentId,
                'attachments'     => $dto->attachments,
            ]);

            // Touch conversation for sorting
            $conv->touch();

            // Broadcast message
            MessageSent::dispatch($message->load('user'));

            // Clear unread caches for other participants
            $conv->participants()
                ->where('user_id', '!=', $sender->id)
                ->pluck('user_id')
                ->each(fn (int $uid) => Cache::forget("user:{$uid}:unread_messages"));

            return $message;
        });
    }

    public function edit(Message $message, string $newContent): Message
    {
        throw_unless(
            $message->isEditable(),
            \RuntimeException::class,
            'Message can no longer be edited.',
        );

        $message->update([
            'content'   => $newContent,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        return $message->fresh();
    }

    public function delete(Message $message): void
    {
        $message->delete(); // soft delete
    }

    public function getMessages(Conversation $conv, int $perPage = 30): LengthAwarePaginator
    {
        return $this->repo->getConversationMessages($conv->id, $perPage);
    }
}
