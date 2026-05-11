<?php

declare(strict_types=1);

namespace App\Events\Group;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Group $group,
        public readonly User $user,
        public readonly User $approvedBy,
    ) {}
}