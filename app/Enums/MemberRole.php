<?php

declare(strict_types=1);

namespace App\Enums;

enum MemberRole: string
{
    case Owner     = 'owner';
    case Admin     = 'admin';
    case Moderator = 'moderator';
    case Member    = 'member';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /** @return array<int, string> */
    public function permissions(): array
    {
        return match ($this) {
            self::Owner => [
                'group.edit', 'group.delete', 'group.transfer',
                'group.manage_members', 'group.invite_members',
                'group.create_events', 'group.cancel_events',
                'group.moderate_posts', 'group.pin_posts',
                'group.manage_settings', 'group.ban_members',
            ],
            self::Admin => [
                'group.edit', 'group.manage_members', 'group.invite_members',
                'group.create_events', 'group.cancel_events',
                'group.moderate_posts', 'group.pin_posts', 'group.ban_members',
            ],
            self::Moderator => [
                'group.invite_members', 'group.create_events',
                'group.moderate_posts', 'group.pin_posts',
            ],
            self::Member => [
                'group.create_posts', 'group.create_comments', 'group.rsvp_events',
            ],
        };
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::Owner, self::Admin, self::Moderator], true);
    }

    public function rank(): int
    {
        return match ($this) {
            self::Owner     => 4,
            self::Admin     => 3,
            self::Moderator => 2,
            self::Member    => 1,
        };
    }
}