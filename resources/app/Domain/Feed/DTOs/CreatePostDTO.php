<?php

declare(strict_types=1);

namespace App\Domain\Feed\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;
use App\Enums\PostType;

final readonly class CreatePostDTO extends BaseDTO
{
    /**
     * @param  array<int, string>|null  $pollOptions
     */
    public function __construct(
        public int $groupId,
        public int $userId,
        public PostType $type,
        public string $content,
        public bool $isPinned = false,
        public bool $isAnnouncement = false,
        public string $visibility = 'group',
        public ?string $pollQuestion = null,
        public ?array $pollOptions = null,
        public ?\DateTimeInterface $pollEndsAt = null,
        public bool $pollAllowMultiple = false,
        public ?int $sharedPostId = null,
    ) {
    }

    public static function fromRequest(array $data): static
    {
        return new self(
            groupId: (int) $data['group_id'],
            userId: (int) $data['user_id'],
            type: $data['type'] instanceof PostType
                ? $data['type']
                : PostType::from($data['type']),
            content: $data['content'],
            isPinned: (bool) ($data['is_pinned'] ?? false),
            isAnnouncement: (bool) ($data['is_announcement'] ?? false),
            visibility: $data['visibility'] ?? 'group',
            pollQuestion: $data['poll_question'] ?? null,
            pollOptions: $data['poll_options'] ?? null,
            pollEndsAt: isset($data['poll_ends_at'])
                ? new \DateTimeImmutable($data['poll_ends_at'])
                : null,
            pollAllowMultiple: (bool) ($data['poll_allow_multiple'] ?? false),
            sharedPostId: isset($data['shared_post_id']) ? (int) $data['shared_post_id'] : null,
        );
    }

    public function toArray(bool $includeNulls = false): array
    {
        $data = [
            'group_id' => $this->groupId,
            'user_id' => $this->userId,
            'type' => $this->type->value,
            'content' => $this->content,
            'is_pinned' => $this->isPinned,
            'is_announcement' => $this->isAnnouncement,
            'visibility' => $this->visibility,
            'shared_post_id' => $this->sharedPostId,
        ];

        return $includeNulls ? $data : array_filter($data, fn ($v) => $v !== null);
    }
}