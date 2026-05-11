<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Group\DTOs\CreateGroupDTO;
use App\Domain\Group\DTOs\InviteMemberDTO;
use App\Domain\Group\DTOs\UpdateGroupDTO;
use App\Domain\Group\Repositories\Contracts\GroupRepositoryInterface;
use App\Domain\Group\Services\GroupService;
use App\Domain\Group\Services\InvitationService;
use App\Domain\Group\Services\MembershipService;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Group\CreateGroupRequest;
use App\Http\Requests\Group\InviteMemberRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupController extends BaseApiController
{
    public function __construct(
        private readonly GroupService $groups,
        private readonly MembershipService $memberships,
        private readonly InvitationService $invitations,
        private readonly GroupRepositoryInterface $repo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->groups->search($request->only([
            'q', 'category_id', 'city', 'verified', 'per_page',
        ]));

        return $this->paginatedResponse($paginator, 'Groups list');
    }

    public function store(CreateGroupRequest $request): JsonResponse
    {
        $group = $this->groups->create($request->user(), CreateGroupDTO::fromRequest($request->validated()));

        return $this->successResponse($group, 'Group created', 201);
    }

    public function show(Group $group): JsonResponse
    {
        $this->authorize('view', $group);
        return $this->successResponse($group->load(['owner', 'category']), 'Group details');
    }

    public function update(UpdateGroupRequest $request, Group $group): JsonResponse
    {
        $updated = $this->groups->update($group, UpdateGroupDTO::fromRequest($request->validated()));
        return $this->successResponse($updated, 'Group updated');
    }

    public function destroy(Group $group): JsonResponse
    {
        $this->authorize('delete', $group);
        $this->groups->delete($group);
        return $this->successResponse(null, 'Group deleted');
    }

    public function join(Group $group, Request $request): JsonResponse
    {
        $this->authorize('view', $group);

        if ($group->isMember($request->user())) {
            return $this->errorResponse('Already a member', 409);
        }

        if ($group->privacy->value === 'public') {
            $this->memberships->addMember($group, $request->user());
            return $this->successResponse(null, 'Joined group');
        }

        $this->memberships->requestMembership($group, $request->user());
        return $this->successResponse(null, 'Request sent');
    }

    public function leave(Group $group, Request $request): JsonResponse
    {
        $this->memberships->leave($group, $request->user());
        return $this->successResponse(null, 'Left group');
    }

    public function members(Group $group): JsonResponse
    {
        $this->authorize('view', $group);
        return $this->successResponse(
            $group->members()->paginate(30),
            'Members',
        );
    }

    public function invite(InviteMemberRequest $request, Group $group): JsonResponse
    {
        $invitation = $this->invitations->invite(
            $group,
            InviteMemberDTO::fromRequest($request->validated()),
            $request->user(),
        );

        return $this->successResponse($invitation, 'Invitation sent', 201);
    }
}