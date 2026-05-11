<?php

declare(strict_types=1);

namespace App\Livewire\Messaging;

use App\Domain\Messaging\DTOs\SendMessageDTO;
use App\Domain\Messaging\Services\ConversationService;
use App\Domain\Messaging\Services\MessageService;
use App\Events\Messaging\UserTyping;
use App\Models\Conversation;
use Livewire\Attributes\On;
use Livewire\Component;

class ChatWindow extends Component
{
    public ?int $conversationId = null;
    public string $newMessage = '';
    public bool $hasMore = true;
    public int $page = 1;

    #[On('open-conversation')]
    public function openConversation(int $id): void
    {
        $this->conversationId = $id;
        $this->page = 1;

        // Mark as read
        $conv = Conversation::findOrFail($id);
        app(ConversationService::class)->markAsRead($conv, auth()->user());
    }

    public function loadOlder(): void
    {
        $this->page++;
    }

    public function sendMessage(MessageService $service): void
    {
        if (blank($this->newMessage)) {
            return;
        }

        $conv = Conversation::findOrFail($this->conversationId);

        $service->send(
            $conv,
            auth()->user(),
            SendMessageDTO::fromRequest([
                'conversation_id' => $this->conversationId,
                'user_id' => auth()->id(),
                'content' => $this->newMessage,
                'type' => 'text',
            ]),
        );

        $this->newMessage = '';
    }

    public function startTyping(): void
    {
        if ($this->conversationId) {
            broadcast(new UserTyping(
                $this->conversationId,
                auth()->id(),
                auth()->user()->name,
            ))->toOthers();
        }
    }

    public function editMessage(int $id, string $content, MessageService $service): void
    {
        $message = \App\Models\Message::findOrFail($id);
        $this->authorize('edit', $message);
        $service->edit($message, $content);
    }

    public function deleteMessage(int $id, MessageService $service): void
    {
        $message = \App\Models\Message::findOrFail($id);
        $this->authorize('delete', $message);
        $service->delete($message);
    }

    public function render()
    {
        $messages = collect();
        $conversation = null;

        if ($this->conversationId) {
            $conversation = Conversation::with('participants.user')->findOrFail($this->conversationId);
            $messages = $conversation->messages()
                ->with('user')
                ->latest()
                ->paginate(30, ['*'], 'page', $this->page);

            $this->hasMore = $messages->hasMorePages();
        }

        return view('livewire.messaging.chat-window', [
            'messages'     => $messages,
            'conversation' => $conversation,
        ]);
    }
}
