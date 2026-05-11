<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Domain\Feed\Services\ReactionService;
use App\Models\Post;
use Livewire\Component;
use App\Enums\ReactionType;

class PostCard extends Component
{
    public Post $post;
    public bool $expanded = false;

    public function toggleReaction(string $type, ReactionService $service): void
    {
        $service->react(
            $this->post,
            auth()->user(),
            ReactionType::from($type)
        );

        $this->post->refresh();
    }

    public function toggleExpanded(): void
    {
        $this->expanded = ! $this->expanded;
    }

    public function render()
    {
        return view('livewire.feed.post-card');
    }
}
