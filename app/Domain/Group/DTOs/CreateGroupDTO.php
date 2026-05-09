<?php

declare(strict_types=1);

namespace App\Domain\Group\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;
use App\Enums\GroupPrivacy;

final class CreateGroupDTO extends BaseDTO
{
    public function __construct(
        public readonly string       $name,
        public readonly string       $description,
        public readonly int          $categoryId,
        public readonly GroupPrivacy $privacy,
        public readonly ?string      $location = null,
        public readonly ?float       $latitude = null,
        public readonly ?float       $longitude = null,
        public readonly ?int         $maxMembers = null,
        public readonly array        $settings = [],
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            name:        $data['name'],
            description: $data['description'],
            categoryId:  (int) $data['category_id'],
            privacy:     GroupPrivacy::from($data['privacy']),
            location:    $data['location']   ?? null,
            latitude:    isset($data['latitude'])  ? (float) $data['latitude']  : null,
            longitude:   isset($data['longitude']) ? (float) $data['longitude'] : null,
            maxMembers:  isset($data['max_members']) ? (int) $data['max_members'] : null,
            settings:    $data['settings'] ?? [],
        );
    }
}