<?php

declare(strict_types=1);

namespace App\Enums;

enum GroupPrivacy: string
{
    case Public  = 'public';
    case Private = 'private';
    case Secret  = 'secret';

    public function label(): string
    {
        return match ($this) {
            self::Public  => 'Public',
            self::Private => 'Private',
            self::Secret  => 'Secret',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Public  => 'green',
            self::Private => 'amber',
            self::Secret  => 'gray',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Public  => 'Anyone can find and join this group.',
            self::Private => 'Anyone can find this group, but membership requires approval.',
            self::Secret  => 'Only invited members can find or join this group.',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->all();
    }
}