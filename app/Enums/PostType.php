<?php

declare(strict_types=1);

namespace App\Enums;

enum PostType: string
{
    case Text  = 'text';
    case Image = 'image';
    case Video = 'video';
    case Link  = 'link';
    case Poll  = 'poll';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Text  => 'document-text',
            self::Image => 'photo',
            self::Video => 'film',
            self::Link  => 'link',
            self::Poll  => 'chart-bar',
        };
    }

    public function requiresMedia(): bool
    {
        return in_array($this, [self::Image, self::Video], true);
    }
}