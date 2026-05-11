<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\GroupPrivacy;
use App\Enums\MemberRole;
use App\Models\Group;
use App\Models\User;

class GroupPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Group $group): bool
    {
        return match ($group->privacy) {
            GroupPrivacy::Public  => true,
            GroupPrivacy::Private => true,                  // discoverable, but content gated
            GroupPrivacy::Secret  => $user && $group->isMember($user),
        };
    }

    public function viewContent(?User $user, Group $group): bool
    {
        if ($group->privacy === GroupPrivacy::Public) return true;

        return $user && $group->isMember($user);
    }

    public function create(User $user): bool
    {
        return $user->status === 'active';
    }

    public function update(User $user, Group $group): bool
    {
        return $group->isAdmin($user);
    }

    public function delete(User $user, Group $group): bool
    {
        return $group->owner_id === $user->id;
    }

    public function manageMembers(User $user, Group $group): bool
    {
        return $group->isAdmin($user);
    }

    public function inviteMembers(User $user, Group $group): bool
    {
        $role = $group->getMemberRole($user);
        return $role && $role->can('group.invite_members');
    }

    public function createEvents(User $user, Group $group): bool
    {
        $role = $group->getMemberRole($user);
        return $role && $role->can('group.create_events');
    }

    public function moderatePosts(User $user, Group $group): bool
    {
        $role = $group->getMemberRole($user);
        return $role && $role->can('group.moderate_posts');
    }

    public function transferOwnership(User $user, Group $group): bool
    {
        return $group->owner_id === $user->id;
    }
}