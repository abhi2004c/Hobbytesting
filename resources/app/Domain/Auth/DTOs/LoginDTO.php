<?php

declare(strict_types=1);

namespace App\Domain\Auth\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;

final class LoginDTO extends BaseDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool   $remember = false,
        public readonly ?string $deviceName = null,
        public readonly ?string $ipAddress = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            email:      strtolower(trim($data['email'])),
            password:   $data['password'],
            remember:   (bool) ($data['remember'] ?? false),
            deviceName: $data['device_name'] ?? null,
            ipAddress:  $data['ip_address']  ?? null,
        );
    }
}