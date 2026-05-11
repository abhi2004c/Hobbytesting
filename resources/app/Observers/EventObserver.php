<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;

class EventObserver
{
    public function created(Event $event): void
    {
        $this->invalidateCache($event);
    }

    public function updated(Event $event): void
    {
        $this->invalidateCache($event);
    }

    public function deleted(Event $event): void
    {
        $this->invalidateCache($event);
    }

    public function restored(Event $event): void
    {
        $this->invalidateCache($event);
    }

    private function invalidateCache(Event $event): void
    {
        Cache::forget("event:slug:{$event->slug}");
        Cache::forget("event:{$event->id}:details");
        Cache::forget("group:{$event->group_id}:events");
        Cache::forget("user:{$event->creator_id}:events");
    }
}