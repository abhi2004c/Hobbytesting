<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Messaging\DTOs\SendMessageDTO;
use App\Domain\Messaging\Services\MessageService;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Message\SendMessageRequest;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends BaseApiController
{
    public function __construct(
        private readonly MessageService $messages,
    ) {}

    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);
        $messages = $this->messages->getMessages($conversation, 30);
        return $this->paginatedResponse($messages);
    }

    public function store(SendMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('send', $conversation);

        $message = $this->messages->send(
            $conversation,
            $request->user(),
            SendMessageDTO::fromRequest($request->validated()),
        );

        return $this->successResponse($message->load('user'), 'Message sent.', 201);
    }

    public function update(Request $request, Message $message): JsonResponse
    {
        $this->authorize('edit', $message);
        $validated = $request->validate(['content' => 'required|string|max:4000']);

        $message = $this->messages->edit($message, $validated['content']);
        return $this->successResponse($message);
    }

    public function destroy(Message $message): JsonResponse
    {
        $this->authorize('delete', $message);
        $this->messages->delete($message);
        return $this->successResponse(null, 'Message deleted.');
    }
}
