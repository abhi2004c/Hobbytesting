<?php

declare(strict_types=1);

namespace App\Domain\Feed\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;

final readonly class UpdatePostDTO extends BaseDTO
{
    public function __construct(
        public ?string $content = null,
        public ?bool $isPinned = null,
        public ?bool $isAnnouncement = null,
        public ?string $visibility = null,
    ) {
    }

    public static function fromRequest(array $data): static
    {
        return new self(
            content: $data['content'] ?? null,
            isPinned: array_key_exists('is_pinned', $data)
                ? (bool) $data['is_pinned']
                : null,
            isAnnouncement: array_key_exists('is_announcement', $data)
                ? (bool) $data['is_announcement']
                : null,
            visibility: $data['visibility'] ?? null,
        );
    }

    public function toArray(bool $includeNulls = false): array
    {
        $data = [
            'content' => $this->content,
            'is_pinned' => $this->isPinned,
            'is_announcement' => $this->isAnnouncement,
            'visibility' => $this->visibility,
        ];

        return $includeNulls ? $data : array_filter($data, fn ($v) => $v !== null);
    }
}