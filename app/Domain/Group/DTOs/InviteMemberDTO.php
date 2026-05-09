<?php

declare(strict_types=1);

namespace App\Domain\Group\DTOs;

use App\Domain\Shared\DTOs\BaseDTO;

final class InviteMemberDTO extends BaseDTO
{
    public function __construct(
        public readonly string  $email,
        public readonly ?string $message = null,
    ) {}

    public static function fromRequest(array $data): static
    {
        return new self(
            email:   strtolower(trim($data['email'])),
            message: $data['message'] ?? null,
        );
    }
}