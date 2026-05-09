<?php

declare(strict_types=1);

namespace App\Domain\User\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;

final class UpdateProfileDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $bio = null,
        public readonly ?string $city = null,
        public readonly ?string $country = null,
        public readonly ?string $website = null,
        public readonly ?string $dateOfBirth = null,
        public readonly ?string $gender = null,
        public readonly ?array  $interestIds = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            name:        $data['name']          ?? null,
            bio:         $data['bio']           ?? null,
            city:        $data['city']          ?? null,
            country:     $data['country']       ?? null,
            website:     $data['website']       ?? null,
            dateOfBirth: $data['date_of_birth'] ?? null,
            gender:      $data['gender']        ?? null,
            interestIds: $data['interest_ids']  ?? null,
        );
    }
}
