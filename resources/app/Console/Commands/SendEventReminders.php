<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Event\Services\ReminderService;
use Illuminate\Console\Command;

class SendEventReminders extends Command
{
    protected $signature = 'events:send-reminders';

    protected $description = 'Dispatch 24h and 1h reminders for upcoming events.';

    public function handle(ReminderService $reminders): int
    {
        $count = $reminders->sendPendingReminders();

        $this->info("Dispatched {$count} reminder(s).");

        return self::SUCCESS;
    }
}