<?php

declare(strict_types=1);

namespace App\Domain\Messaging\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;

final class SendMessageDTO extends BaseDTO
{
    public function __construct(
        public readonly int     $conversationId,
        public readonly int     $userId,
        public readonly string  $content,
        public readonly string  $type = 'text',    // text|image|file|system
        public readonly ?int    $parentId = null,
        public readonly ?array  $attachments = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            conversationId: $data['conversation_id'],
            userId:         $data['user_id'],
            content:        $data['content'],
            type:           $data['type']        ?? 'text',
            parentId:       $data['parent_id']   ?? null,
            attachments:    $data['attachments']  ?? null,
        );
    }
}
