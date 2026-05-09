<?php

declare(strict_types=1);

namespace App\Enums;

enum EventStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft     => 'gray',
            self::Published => 'green',
            self::Cancelled => 'red',
        };
    }
}