<?php

declare(strict_types=1);

namespace App\Domain\Group\Services;

use App\Domain\Group\DTOs\InviteMemberDTO;
use App\Enums\MemberRole;
use App\Mail\GroupInvitationEmail;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class InvitationService
{
    public function __construct(private readonly MembershipService $memberships) {}

    public function invite(Group $group, InviteMemberDTO $dto, User $invitedBy): GroupInvitation
    {
        return DB::transaction(function () use ($group, $dto, $invitedBy) {
            // Cancel any existing pending invitation for this email
            $group->invitations()
                ->where('email', $dto->email)
                ->where('status', 'pending')
                ->update(['status' => 'revoked']);

            $invitation = $group->invitations()->create([
                'invited_by' => $invitedBy->id,
                'email'      => $dto->email,
                'status'     => 'pending',
            ]);

            Mail::to($dto->email)->queue(
                new GroupInvitationEmail($invitation, $dto->message),
            );

            return $invitation;
        });
    }

    public function acceptInvitation(string $token, User $user): \App\Models\GroupMembership
    {
        return DB::transaction(function () use ($token, $user) {
            $invitation = GroupInvitation::where('token', $token)
                ->where('status', 'pending')
                ->firstOrFail();

            throw_if($invitation->isExpired(), \DomainException::class, 'Invitation has expired.');

            throw_if(
                strtolower($invitation->email) !== strtolower($user->email),
                \DomainException::class,
                'This invitation was not issued to your account.',
            );
            $invitation->update([
                'status'      => 'accepted',
                'accepted_at' => now(),
            ]);

            return $this->memberships->addMember($invitation->group, $user, MemberRole::Member);
        });
    }

    public function declineInvitation(string $token): void
    {
        GroupInvitation::where('token', $token)
            ->where('status', 'pending')
            ->update(['status' => 'declined']);
    }

    public function revokeInvitation(GroupInvitation $invitation): void
    {
        $invitation->update(['status' => 'revoked']);
    }

    public function cleanExpired(): int
    {
        return GroupInvitation::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }
}