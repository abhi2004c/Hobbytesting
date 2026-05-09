<?php

declare(strict_types=1);

namespace App\Livewire\Messaging;

use App\Domain\Messaging\Services\ConversationService;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ConversationList extends Component
{
    public bool $showNewModal = false;
    public string $search = '';

    public function startConversation(int $userId, ConversationService $service): void
    {
        $other = User::findOrFail($userId);
        $conv  = $service->findOrCreateDirect(auth()->user(), $other);

        $this->showNewModal = false;
        $this->search = '';

        $this->dispatch('open-conversation', id: $conv->id);
    }

    public function render(ConversationService $service): View
    {
        $conversations = auth()->check()
            ? $service->getUserConversations(auth()->user())
            : collect();

        $users = collect();
        if ($this->showNewModal && strlen($this->search) >= 1) {
            $users = User::where('id', '!=', auth()->id())
                ->where('name', 'like', '%' . $this->search . '%')
                ->take(8)
                ->get();
        }

        return view('livewire.messaging.conversation-list', [
            'conversations' => $conversations,
            'users'         => $users,
        ]);
    }
}
