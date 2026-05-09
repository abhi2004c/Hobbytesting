<?php

declare(strict_types=1);

namespace App\Enums;

enum ReportStatus: string
{
    case Pending   = 'pending';
    case Reviewed  = 'reviewed';
    case Resolved  = 'resolved';
    case Dismissed = 'dismissed';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending   => 'amber',
            self::Reviewed  => 'blue',
            self::Resolved  => 'green',
            self::Dismissed => 'gray',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Pending, self::Reviewed], true);
    }
}