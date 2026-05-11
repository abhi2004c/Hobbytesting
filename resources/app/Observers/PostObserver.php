<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    public function created(Post $post): void
    {
        $this->bustCaches($post);
    }

    public function updated(Post $post): void
    {
        $this->bustCaches($post);
    }

    public function deleted(Post $post): void
    {
        $this->bustCaches($post);
    }

    private function bustCaches(Post $post): void
    {
        Cache::forget("feed.group.{$post->group_id}");
        Cache::forget("feed.personal.{$post->user_id}");
    }
}
