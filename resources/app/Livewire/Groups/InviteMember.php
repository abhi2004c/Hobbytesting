<?php

declare(strict_types=1);

namespace App\Livewire\Groups;

use App\Domain\Group\DTOs\InviteMemberDTO;
use App\Domain\Group\Services\InvitationService;
use App\Models\Group;
use Livewire\Component;

class InviteMember extends Component
{
    public Group $group;
    public string $email = '';
    public string $message = '';

    public function mount(Group $group): void
    {
        $this->group = $group;
    }

    public function send(InvitationService $service): void
    {
        $this->authorize('inviteMembers', $this->group);

        $data = $this->validate([
            'email'   => ['required', 'email'],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $service->invite($this->group, InviteMemberDTO::fromRequest($data), auth()->user());

        $this->reset(['email', 'message']);
        $this->dispatch('invitation-sent');
    }

    public function render()
    {
        return view('livewire.groups.invite-member');
    }
}