<?php

use App\Domain\Messaging\DTOs\SendMessageDTO;
use App\Domain\Messaging\Services\ConversationService;
use App\Domain\Messaging\Services\MessageService;
use App\Models\Message;
use App\Models\User;

beforeEach(function () {
    $this->userA = User::factory()->create();
    $this->userB = User::factory()->create();
    $this->convService = app(ConversationService::class);
    $this->msgService = app(MessageService::class);
    $this->conversation = $this->convService->findOrCreateDirect($this->userA, $this->userB);
});

it('sends a message', function () {
    $message = $this->msgService->send(
        $this->conversation,
        $this->userA,
        SendMessageDTO::fromRequest(['content' => 'Hello!', 'type' => 'text']),
    );

    expect($message)->toBeInstanceOf(Message::class)
        ->and($message->content)->toBe('Hello!')
        ->and($message->user_id)->toBe($this->userA->id);
});

it('soft deletes a message', function () {
    $message = $this->msgService->send(
        $this->conversation,
        $this->userA,
        SendMessageDTO::fromRequest(['content' => 'Delete me', 'type' => 'text']),
    );

    $this->msgService->delete($message);

    expect(Message::find($message->id))->toBeNull()
        ->and(Message::withTrashed()->find($message->id))->not->toBeNull();
});

it('edits a message within 15 minutes', function () {
    $message = $this->msgService->send(
        $this->conversation,
        $this->userA,
        SendMessageDTO::fromRequest(['content' => 'Original', 'type' => 'text']),
    );

    $edited = $this->msgService->edit($message, 'Updated content');

    expect($edited->content)->toBe('Updated content')
        ->and($edited->is_edited)->toBeTrue()
        ->and($edited->edited_at)->not->toBeNull();
});

it('marks conversation as read', function () {
    $this->msgService->send(
        $this->conversation,
        $this->userB,
        SendMessageDTO::fromRequest(['content' => 'Hey!', 'type' => 'text']),
    );

    $this->convService->markAsRead($this->conversation, $this->userA);

    $participant = $this->conversation->participants()
        ->where('user_id', $this->userA->id)
        ->first();

    expect($participant->last_read_at)->not->toBeNull();
});
