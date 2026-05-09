<?php

declare(strict_types=1);

namespace App\Livewire\Groups;

use App\Domain\Group\Services\MembershipService;
use App\Enums\MemberRole;
use App\Models\Group;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class MemberList extends Component
{
    use WithPagination;

    public Group $group;
    public string $roleFilter = 'all';

    public function mount(Group $group): void
    {
        $this->group = $group;
    }

    public function remove(int $userId): void
    {
        $this->authorize('manageMembers', $this->group);

        app(MembershipService::class)->removeMember(
            $this->group,
            User::findOrFail($userId),
            auth()->user(),
        );
    }

    public function ban(int $userId, string $reason = 'Violation of group rules'): void
    {
        $this->authorize('manageMembers', $this->group);

        app(MembershipService::class)->banMember(
            $this->group,
            User::findOrFail($userId),
            auth()->user(),
            $reason,
        );
    }

    public function changeRole(int $userId, string $role): void
    {
        $this->authorize('manageMembers', $this->group);

        app(MembershipService::class)->updateRole(
            $this->group,
            User::findOrFail($userId),
            MemberRole::from($role),
            auth()->user(),
        );
    }

    public function render()
    {
        $query = $this->group->members();

        if ($this->roleFilter !== 'all') {
            $query->wherePivot('role', $this->roleFilter);
        }

        return view('livewire.groups.member-list', [
            'members' => $query->paginate(20),
        ]);
    }
}