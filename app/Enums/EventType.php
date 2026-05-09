<?php

declare(strict_types=1);

namespace App\Enums;

enum EventType: string
{
    case Online   = 'online';
    case InPerson = 'in_person';
    case Hybrid   = 'hybrid';

    public function label(): string
    {
        return match ($this) {
            self::Online   => 'Online',
            self::InPerson => 'In Person',
            self::Hybrid   => 'Hybrid',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Online   => 'video-camera',
            self::InPerson => 'map-pin',
            self::Hybrid   => 'globe-alt',
        };
    }

    public function requiresLocation(): bool
    {
        return in_array($this, [self::InPerson, self::Hybrid], true);
    }

    public function requiresOnlineUrl(): bool
    {
        return in_array($this, [self::Online, self::Hybrid], true);
    }
}   