<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Messaging\DTOs\CreateConversationDTO;
use App\Domain\Messaging\Services\ConversationService;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Message\CreateConversationRequest;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends BaseApiController
{
    public function __construct(
        private readonly ConversationService $conversations,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $conversations = $this->conversations->getUserConversations($request->user());
        return $this->successResponse($conversations);
    }

    public function store(CreateConversationRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($validated['type'] === 'direct' && count($validated['user_ids']) === 1) {
            $otherUser = User::findOrFail($validated['user_ids'][0]);
            $conversation = $this->conversations->findOrCreateDirect($request->user(), $otherUser);
        } else {
            $conversation = $this->conversations->createGroupConversation(
                userIds:   $validated['user_ids'],
                name:      $validated['name'] ?? 'Group Chat',
                createdBy: $request->user(),
            );
        }

        return $this->successResponse($conversation->load('participants.user'), 'Conversation created.', 201);
    }

    public function show(Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        return $this->successResponse($conversation->load(['participants.user', 'latestMessage.user']));
    }

    public function markRead(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        $this->conversations->markAsRead($conversation, $request->user());
        return $this->successResponse(null, 'Conversation marked as read.');
    }
}
