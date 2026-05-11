<?php

declare(strict_types=1);

namespace App\Enums;

enum MemberStatus: string
{
    case Pending = 'pending';
    case Active  = 'active';
    case Banned  = 'banned';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'amber',
            self::Active  => 'green',
            self::Banned  => 'red',
        };
    }
}