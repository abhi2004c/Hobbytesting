<?php

declare(strict_types=1);

namespace App\Domain\Event\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;
use App\Enums\EventStatus;
use App\Enums\EventType;
use Carbon\Carbon;

final readonly class UpdateEventDTO extends BaseDTO
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?EventType $type = null,
        public ?Carbon $startsAt = null,
        public ?Carbon $endsAt = null,
        public ?EventStatus $status = null,
        public ?string $location = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public ?string $onlineUrl = null,
        public ?int $capacity = null,
        public ?bool $isRecurring = null,
        public ?string $recurrenceRule = null,
    ) {
    }

    public static function fromRequest(array $data): static
    {
        return new self(
            title: $data['title'] ?? null,
            description: $data['description'] ?? null,
            type: isset($data['type'])
                ? ($data['type'] instanceof EventType
                    ? $data['type']
                    : EventType::from($data['type']))
                : null,
            startsAt: isset($data['starts_at']) ? Carbon::parse($data['starts_at']) : null,
            endsAt: isset($data['ends_at']) ? Carbon::parse($data['ends_at']) : null,
            status: isset($data['status'])
                ? ($data['status'] instanceof EventStatus
                    ? $data['status']
                    : EventStatus::from($data['status']))
                : null,
            location: $data['location'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            onlineUrl: $data['online_url'] ?? null,
            capacity: array_key_exists('capacity', $data) && $data['capacity'] !== null
                ? (int) $data['capacity']
                : null,
            isRecurring: array_key_exists('is_recurring', $data)
                ? (bool) $data['is_recurring']
                : null,
            recurrenceRule: $data['recurrence_rule'] ?? null,
        );
    }

    public function toArray(bool $includeNulls = false): array
    {
        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type?->value,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'status' => $this->status?->value,
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