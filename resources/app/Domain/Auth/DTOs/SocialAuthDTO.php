<?php

declare(strict_types=1);

namespace App\Domain\Auth\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;

final class SocialAuthDTO extends BaseDTO
{
    public function __construct(
        public readonly string  $provider,
        public readonly string  $providerId,
        public readonly ?string $email,
        public readonly string  $name,
        public readonly ?string $avatar = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            provider:   $data['provider'],
            providerId: $data['provider_id'],
            email:      isset($data['email']) ? strtolower(trim($data['email'])) : null,
            name:       $data['name'],
            avatar:     $data['avatar'] ?? null,
        );
    }
}