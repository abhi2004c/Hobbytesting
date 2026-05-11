<?php

declare(strict_types=1);

namespace App\Enums;

enum ReactionType: string
{
    case Like = 'like';
    case Love = 'love';
    case Wow = 'wow';
    case Haha = 'haha';

    public function label(): string
    {
        return match ($this) {
            self::Like => 'Like',
            self::Love => 'Love',
            self::Wow => 'Wow',
            self::Haha => 'Haha',
        };
    }

    public function emoji(): string
    {
        return match ($this) {
            self::Like => '👍',
            self::Love => '❤️',
            self::Wow => '😮',
            self::Haha => '😂',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $c) => [$c->value => $c->label()])
            ->toArray();
    }
}