<?php

declare(strict_types=1);

namespace App\Domain\Group\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;
use App\Enums\GroupPrivacy;

final class UpdateGroupDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string       $name = null,
        public readonly ?string       $description = null,
        public readonly ?int          $categoryId = null,
        public readonly ?GroupPrivacy $privacy = null,
        public readonly ?string       $location = null,
        public readonly ?float        $latitude = null,
        public readonly ?float        $longitude = null,
        public readonly ?int          $maxMembers = null,
        public readonly ?array        $settings = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            name:        $data['name']        ?? null,
            description: $data['description'] ?? null,
            categoryId:  isset($data['category_id']) ? (int) $data['category_id'] : null,
            privacy:     isset($data['privacy']) ? GroupPrivacy::from($data['privacy']) : null,
            location:    $data['location']    ?? null,
            latitude:    isset($data['latitude'])  ? (float) $data['latitude']  : null,
            longitude:   isset($data['longitude']) ? (float) $data['longitude'] : null,
            maxMembers:  isset($data['max_members']) ? (int) $data['max_members'] : null,
            settings:    $data['settings']    ?? null,
        );
    }
}