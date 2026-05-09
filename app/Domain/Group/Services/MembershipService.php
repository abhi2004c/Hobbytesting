<?php

declare(strict_types=1);

namespace App\Domain\Group\Services;

use App\Domain\Group\Exceptions\MembershipLimitExceededException;
use App\Enums\MemberRole;
use App\Enums\MemberStatus;
use App\Events\Group\MemberApproved;
use App\Events\Group\MemberBanned;
use App\Events\Group\MemberJoined;
use App\Events\Group\MemberRemoved;
use App\Events\Group\MembershipRequested;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class MembershipService
{
    public function addMember(Group $group, User $user, MemberRole $role = MemberRole::Member): GroupMembership
    {
        return DB::transaction(function () use ($group, $user, $role) {
            throw_if(
                $group->hasReachedMemberLimit() && $role === MemberRole::Member,
                MembershipLimitExceededException::class,
                'This group has reached its member limit.',
            );

            $membership = GroupMembership::updateOrCreate(
                ['group_id' => $group->id, 'user_id' => $user->id],
                [
                    'role'      => $role->value,
                    'status'    => MemberStatus::Active->value,
                    'joined_at' => now(),
                ],
            );

            $group->refreshMemberCount();
            $this->invalidate($group, $user);

            event(new MemberJoined($group, $user, $role));

            return $membership;
        });
    }

    public function requestMembership(Group $group, User $user): GroupMembership
    {
        return DB::transaction(function () use ($group, $user) {
            $membership = GroupMembership::firstOrCreate(
                ['group_id' => $group->id, 'user_id' => $user->id],
                [
                    'role'   => MemberRole::Member->value,
                    'status' => MemberStatus::Pending->value,
                ],
            );

            event(new MembershipRequested($group, $user));

            return $membership;
        });
    }

    public function approveMembership(Group $group, User $user, User $approvedBy): void
    {
        DB::transaction(function () use ($group, $user, $approvedBy) {
            throw_if(
                $group->hasReachedMemberLimit(),
                MembershipLimitExceededException::class,
                'This group has reached its member limit.',
            );

            $membership = $group->memberships()
                ->where('user_id', $user->id)
                ->where('status', MemberStatus::Pending->value)
                ->firstOrFail();

            $membership->update([
                'status'    => MemberStatus::Active->value,
                'joined_at' => now(),
            ]);

            $group->refreshMemberCount();
            $this->invalidate($group, $user);

            event(new MemberApproved($group, $user, $approvedBy));
        });
    }

    public function rejectMembership(Group $group, User $user, User $rejectedBy): void
    {
        $group->memberships()
            ->where('user_id', $user->id)
            ->where('status', MemberStatus::Pending->value)
            ->delete();
    }

    public function removeMember(Group $group, User $user, User $removedBy): void
    {
        DB::transaction(function () use ($group, $user, $removedBy) {
            throw_if(
                $group->owner_id === $user->id,
                \DomainException::class,
                'Cannot remove the group owner.',
            );

            $group->memberships()->where('user_id', $user->id)->delete();
            $group->refreshMemberCount();
            $this->invalidate($group, $user);

            event(new MemberRemoved($group, $user, $removedBy));
        });
    }

    public function banMember(Group $group, User $user, User $bannedBy, string $reason): void
    {
        DB::transaction(function () use ($group, $user, $bannedBy, $reason) {
            throw_if(
                $group->owner_id === $user->id,
                \DomainException::class,
                'Cannot ban the group owner.',
            );

            $group->memberships()->updateOrCreate(
                ['group_id' => $group->id, 'user_id' => $user->id],
                [
                    'status'     => MemberStatus::Banned->value,
                    'ban_reason' => $reason,
                    'banned_by'  => $bannedBy->id,
                ],
            );

            $group->refreshMemberCount();
            $this->invalidate($group, $user);

            event(new MemberBanned($group, $user, $bannedBy, $reason));
        });
    }

    public function updateRole(Group $group, User $user, MemberRole $newRole, User $updatedBy): void
    {
        throw_if(
            $newRole === MemberRole::Owner,
            \DomainException::class,
            'Use transferOwnership() to assign owner role.',
        );

        $group->memberships()
            ->where('user_id', $user->id)
            ->update(['role' => $newRole->value]);

        $this->invalidate($group, $user);
    }

    public function leave(Group $group, User $user): void
    {
        DB::transaction(function () use ($group, $user) {
            throw_if(
                $group->owner_id === $user->id,
                \DomainException::class,
                'Owner cannot leave. Transfer ownership first.',
            );

            $group->memberships()->where('user_id', $user->id)->delete();
            $group->refreshMemberCount();
            $this->invalidate($group, $user);
        });
    }

    private function invalidate(Group $group, User $user): void
    {
        Cache::forget("group.{$group->id}.member_count");
        Cache::forget("user.{$user->id}.groups");
    }
}