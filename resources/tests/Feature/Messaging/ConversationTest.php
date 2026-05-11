<?php

use App\Domain\Messaging\Services\ConversationService;
use App\Models\Conversation;
use App\Models\User;

beforeEach(function () {
    $this->userA = User::factory()->create();
    $this->userB = User::factory()->create();
    $this->service = app(ConversationService::class);
});

it('findOrCreateDirect returns same conversation on second call (dedup)', function () {
    $conv1 = $this->service->findOrCreateDirect($this->userA, $this->userB);
    $conv2 = $this->service->findOrCreateDirect($this->userA, $this->userB);

    expect($conv1->id)->toBe($conv2->id)
        ->and(Conversation::count())->toBe(1);
});

it('creates group conversation with all participants', function () {
    $users = User::factory()->count(4)->create();
    $conv = $this->service->createGroupConversation(
        $users->pluck('id')->toArray(),
        'Test Group',
        $this->userA,
    );

    expect($conv->type)->toBe('group')
        ->and($conv->name)->toBe('Test Group')
        ->and($conv->participants)->toHaveCount(5); // 4 users + creator
});

it('calculates unread count correctly', function () {
    $conv = $this->service->findOrCreateDirect($this->userA, $this->userB);

    // Simulate message from userB
    $conv->messages()->create([
        'user_id' => $this->userB->id,
        'type'    => 'text',
        'content' => 'Hello!',
    ]);
    $conv->touch();

    // UserA should have 1 unread conversation
    $unread = $this->service->getUnreadCount($this->userA);
    expect($unread)->toBeGreaterThanOrEqual(1);
});
