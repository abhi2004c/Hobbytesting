<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Group\Services\InvitationService;
use Illuminate\Console\Command;

class CleanExpiredInvitations extends Command
{
    protected $signature = 'invitations:clean-expired';
    protected $description = 'Remove expired group invitations';

    public function handle(InvitationService $invitations): int
    {
        $count = $invitations->cleanExpired();

        $this->info("Cleaned {$count} expired invitation(s).");

        return self::SUCCESS;
    }
}
