<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Group\DTOs\CreateGroupDTO;
use App\Domain\Group\DTOs\UpdateGroupDTO;
use App\Domain\Group\Services\GroupService;
use App\Domain\Group\Services\MembershipService;
use App\Http\Requests\Group\CreateGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Models\Group;
use App\Models\GroupCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function __construct(
        private readonly GroupService $groups,
        private readonly MembershipService $memberships,
    ) {}

    public function index(): View
    {
        return view('groups.index', [
            'categories' => GroupCategory::active()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Group::class);

        return view('groups.create', [
            'categories' => GroupCategory::active()->get(),
        ]);
    }

    public function store(CreateGroupRequest $request): RedirectResponse
    {
        $group = $this->groups->create(
            owner: $request->user(),
            dto: CreateGroupDTO::fromRequest($request->validated()),
        );

        if ($request->hasFile('cover')) {
            $group->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return redirect()->route('groups.show', $group)->with('success', 'Group created!');
    }

    public function show(Group $group): View
    {
        $this->authorize('view', $group);

        $group->load(['owner', 'category']);

        return view('groups.show', compact('group'));
    }

    public function edit(Group $group): View
    {
        $this->authorize('update', $group);

        return view('groups.edit', [
            'group'      => $group,
            'categories' => GroupCategory::active()->get(),
        ]);
    }

    public function update(UpdateGroupRequest $request, Group $group): RedirectResponse
    {
        $this->groups->update($group, UpdateGroupDTO::fromRequest($request->validated()));

        if ($request->hasFile('cover')) {
            $group->clearMediaCollection('cover');
            $group->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return back()->with('success', 'Group updated.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        $this->authorize('delete', $group);
        $this->groups->delete($group);

        return redirect()->route('groups.index')->with('success', 'Group deleted.');
    }

    public function join(Group $group): RedirectResponse
    {
        $this->authorize('view', $group);

        if ($group->privacy->value === 'public') {
            $this->memberships->addMember($group, request()->user());
            $msg = 'Welcome to the group!';
        } else {
            $this->memberships->requestMembership($group, request()->user());
            $msg = 'Membership request sent.';
        }

        return back()->with('success', $msg);
    }

    public function leave(Group $group): RedirectResponse
    {
        $this->memberships->leave($group, request()->user());
        return redirect()->route('groups.index')->with('success', 'You left the group.');
    }
}