<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Domain\Feed\Services\PostService;
use App\Models\Group;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class PostFeed extends Component
{
    use WithPagination;

    #[Url(as: 'filter')]
    public string $filter = 'latest'; // latest|popular|announcements

    public ?int $groupId = null;

    #[On('post-created')]
    public function refreshFeed(): void {}

    public function updating(string $name, mixed $value): void
    {
        if ($name === 'filter') {
            $this->resetPage();
        }
    }

    public function render(PostService $service): View
    {
        $user = auth()->user();

        if ($this->groupId) {
            $group = Group::findOrFail($this->groupId);
            $posts = $service->getGroupFeed($group, $this->filter);
        } else {
            $posts = $service->getPersonalFeed($user, $this->filter);
        }

        return view('livewire.feed.post-feed', [
            'posts' => $posts,
        ]);
    }
}
