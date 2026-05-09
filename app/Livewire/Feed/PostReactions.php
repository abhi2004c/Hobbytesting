<?php

declare(strict_types=1);

namespace App\Livewire\Feed;

use App\Domain\Feed\Services\ReactionService;
use App\Models\Post;
use Livewire\Component;

class PostReactions extends Component
{
    public int $postId;
    public array $summary = ['like' => 0, 'love' => 0, 'wow' => 0, 'haha' => 0];

    public function mount(int $postId, ReactionService $service): void
    {
        $this->postId = $postId;
        $post = Post::findOrFail($postId);
        $this->summary = $service->getReactionSummary($post);
    }

    public function react(string $type, ReactionService $service): void
    {
        $post = Post::findOrFail($this->postId);
        $service->react($post, auth()->user(), $type);
        $this->summary = $service->getReactionSummary($post);
    }

    public function render()
    {
        return view('livewire.feed.post-reactions');
    }
}
