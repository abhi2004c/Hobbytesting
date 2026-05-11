<?php

declare(strict_types=1);

namespace App\Domain\Shared\DTOs;

abstract readonly class BaseDTO
{
    /**
     * Build the DTO from a validated request payload.
     *
     * @param  array<string, mixed>  $data
     */
    abstract public static function fromRequest(array $data): static;

    /**
     * Convert DTO to array (excluding nulls by default).
     *
     * @return array<string, mixed>
     */
    public function toArray(bool $includeNulls = false): array
    {
        $data = get_object_vars($this);

        return $includeNulls
            ? $data
            : array_filter($data, static fn ($v) => $v !== null);
    }
}