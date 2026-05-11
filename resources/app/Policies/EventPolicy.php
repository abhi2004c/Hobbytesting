<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\MemberRole;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->status === 'active';
    }

    public function view(User $user, Event $event): bool
    {
        $group = $event->group;

        // Public groups → anyone can view
        if ($group->privacy->value === 'public') {
            return true;
        }

        // Private/Secret → must be a member
        return $group->isMember($user);
    }

    public function create(User $user): bool
    {
        return $user->status === 'active';
    }

    public function update(User $user, Event $event): bool
    {
        if ($event->creator_id === $user->id) {
            return true;
        }

        $role = $event->group->getMemberRole($user);

        return $role !== null && $role->can('manage_events');
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    public function cancel(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    public function rsvp(User $user, Event $event): bool
    {
        if ($event->isCancelled() || $event->hasStarted()) {
            return false;
        }

        $group = $event->group;

        if ($group->privacy->value === 'public') {
            return true;
        }

        return $group->isMember($user);
    }

    public function manageAttendees(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    public function duplicate(User $user, Event $event): bool
    {
        $role = $event->group->getMemberRole($user);

        return $role !== null && in_array($role, [MemberRole::Owner, MemberRole::Admin], true);
    }
}