<?php

declare(strict_types=1);

namespace App\Domain\Feed\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;

final class CreateCommentDTO extends BaseDTO
{
    public function __construct(
        public readonly int     $postId,
        public readonly int     $userId,
        public readonly string  $content,
        public readonly ?int    $parentId = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            postId:   $data['post_id'],
            userId:   $data['user_id'],
            content:  $data['content'],
            parentId: $data['parent_id'] ?? null,
        );
    }
}
