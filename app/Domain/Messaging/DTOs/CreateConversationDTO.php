<?php

declare(strict_types=1);

namespace App\Domain\Messaging\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;

final class CreateConversationDTO extends BaseDTO
{
    public function __construct(
        public readonly string  $type,           // direct|group
        public readonly array   $participantIds,
        public readonly ?string $name = null,
        public readonly ?int    $groupId = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            type:           $data['type'],
            participantIds: $data['participant_ids'],
            name:           $data['name']     ?? null,
            groupId:        $data['group_id'] ?? null,
        );
    }
}
