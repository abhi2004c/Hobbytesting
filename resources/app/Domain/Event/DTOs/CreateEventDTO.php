<?php

declare(strict_types=1);

namespace App\Domain\Event\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;
use App\Enums\EventStatus;
use App\Enums\EventType;
use Carbon\Carbon;

final readonly class CreateEventDTO extends BaseDTO
{
    public function __construct(
        public int $groupId,
        public int $creatorId,
        public string $title,
        public string $description,
        public EventType $type,
        public Carbon $startsAt,
        public Carbon $endsAt,
        public EventStatus $status = EventStatus::Published,
        public ?string $location = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $onlineUrl = null,
        public ?int $capacity = null,
        public bool $isRecurring = false,
        public ?string $recurrenceRule = null,
    ) {
    }

    public static function fromRequest(array $data): static
    {
        return new self(
            groupId: (int) $data['group_id'],
            creatorId: (int) $data['creator_id'],
            title: $data['title'],
            description: $data['description'],
            type: $data['type'] instanceof EventType
                ? $data['type']
                : EventType::from($data['type']),
            startsAt: Carbon::parse($data['starts_at']),
            endsAt: Carbon::parse($data['ends_at']),
            status: isset($data['status'])
                ? ($data['status'] instanceof EventStatus
                    ? $data['status']
                    : EventStatus::from($data['status']))
                : EventStatus::Published,
            location: $data['location'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            onlineUrl: $data['online_url'] ?? null,
            capacity: isset($data['capacity']) ? (int) $data['capacity'] : null,
            isRecurring: (bool) ($data['is_recurring'] ?? false),
            recurrenceRule: $data['recurrence_rule'] ?? null,
        );
    }

    public function toArray(bool $includeNulls = false): array
    {
        $data = [
            'group_id' => $this->groupId,
            'creator_id' => $this->creatorId,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type->value,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'status' => $this->status->value,
            'location' => $this->location,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'online_url' => $this->onlineUrl,
            'capacity' => $this->capacity,
            'is_recurring' => $this->isRecurring,
            'recurrence_rule' => $this->recurrenceRule,
        ];

        return $includeNulls
            ? $data
            : array_filter($data, fn ($v) => $v !== null);
    }
}