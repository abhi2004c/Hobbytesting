<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Group;
use Illuminate\Support\Facades\Cache;

class GroupObserver
{
    public function created(Group $group): void
    {
        $this->bust($group);
    }

    public function updated(Group $group): void
    {
        $this->bust($group);
    }

    public function deleted(Group $group): void
    {
        $this->bust($group);
    }

    private function bust(Group $group): void
    {
        Cache::forget("group.{$group->id}.member_count");
        Cache::forget("group.{$group->slug}.details");
        Cache::forget("user.{$group->owner_id}.groups");
    }
}