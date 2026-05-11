<?php

use App\Domain\Messaging\Services\ConversationService;
use App\Domain\Messaging\Services\MessageService;
use App\Domain\Messaging\DTOs\SendMessageDTO;
use App\Models\User;

beforeEach(function () {
    $this->userA = User::factory()->create();
    $this->userB = User::factory()->create();
    $this->convService = app(ConversationService::class);
    $this->msgService = app(MessageService::class);
});

it('returns unread count of zero for fresh user', function () {
    expect($this->convService->getUnreadCount($this->userA))->toBe(0);
});

it('conversation service returns conversations ordered by latest message', function () {
    $conv1 = $this->convService->findOrCreateDirect($this->userA, $this->userB);
    $this->msgService->send($conv1, $this->userB, SendMessageDTO::fromRequest(['content' => 'Old', 'type' => 'text']));

    $userC = User::factory()->create();
    $conv2 = $this->convService->findOrCreateDirect($this->userA, $userC);
    $this->msgService->send($conv2, $userC, SendMessageDTO::fromRequest(['content' => 'New', 'type' => 'text']));

    $conversations = $this->convService->getUserConversations($this->userA);

    // Most recent conversation should come first
    expect($conversations->first()->id)->toBe($conv2->id);
});

it('marks a conversation as read and resets unread', function () {
    $conv = $this->convService->findOrCreateDirect($this->userA, $this->userB);
    $this->msgService->send($conv, $this->userB, SendMessageDTO::fromRequest(['content' => 'Hey!', 'type' => 'text']));

    $this->convService->markAsRead($conv, $this->userA);

    $participant = $conv->participants()->where('user_id', $this->userA->id)->first();
    expect($participant->last_read_at)->not->toBeNull();
});

it('message edit fails after 15 minutes', function () {
    $conv = $this->convService->findOrCreateDirect($this->userA, $this->userB);
    $msg = $this->msgService->send($conv, $this->userA, SendMessageDTO::fromRequest(['content' => 'Original', 'type' => 'text']));

    // Manually set created_at to 20 minutes ago
    $msg->update(['created_at' => now()->subMinutes(20)]);

    expect(fn () => $this->msgService->edit($msg, 'Too late'))
        ->toThrow(\RuntimeException::class);
});
