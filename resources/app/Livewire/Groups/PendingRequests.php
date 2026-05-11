<?php

declare(strict_types=1);

namespace App\Livewire\Groups;

use App\Domain\Group\Services\MembershipService;
use App\Models\Group;
use App\Models\User;
use Livewire\Component;

class PendingRequests extends Component
{
    public Group $group;

    public function mount(Group $group): void
    {
        $this->group = $group;
    }

    public function approve(int $userId, MembershipService $service): void
    {
        $this->authorize('manageMembers', $this->group);
        $service->approveMembership($this->group, User::findOrFail($userId), auth()->user());
    }

    public function reject(int $userId, MembershipService $service): void
    {
        $this->authorize('manageMembers', $this->group);
        $service->rejectMembership($this->group, User::findOrFail($userId), auth()->user());
    }

    public function render()
    {
        return view('livewire.groups.pending-requests', [
            'requests' => $this->group->pendingMembers()->paginate(15),
        ]);
    }
}