<?php

declare(strict_types=1);

namespace App\Domain\Event\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;
use App\Enums\RsvpStatus;

final readonly class RsvpDTO extends BaseDTO
{
    public function __construct(
        public int $eventId,
        public int $userId,
        public RsvpStatus $status,
        public ?string $note = null,
    ) {
    }

    public static function fromRequest(array $data): static
    {
        return new self(
            eventId: (int) $data['event_id'],
            userId: (int) $data['user_id'],
            status: $data['status'] instanceof RsvpStatus
                ? $data['status']
                : RsvpStatus::from($data['status']),
            note: $data['note'] ?? null,
        );
    }

    public function toArray(bool $includeNulls = false): array
    {
        $data = [
            'event_id' => $this->eventId,
            'user_id' => $this->userId,
            'status' => $this->status->value,
            'note' => $this->note,
        ];

        return $includeNulls
            ? $data
            : array_filter($data, fn ($v) => $v !== null);
    }
}