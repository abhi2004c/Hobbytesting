<?php

declare(strict_types=1);

namespace App\Domain\Auth\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;

final class RegisterDTO extends BaseDTO
{
    public function __construct(
        public readonly string  $name,
        public readonly string  $email,
        public readonly string  $password,
        public readonly ?string $city = null,
        public readonly ?string $country = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            name:     $data['name'],
            email:    strtolower(trim($data['email'])),
            password: $data['password'],
            city:     $data['city']     ?? null,
            country:  $data['country']  ?? null,
        );
    }
}