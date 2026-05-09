<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes (Scheduled Tasks)
|--------------------------------------------------------------------------
*/

Schedule::command('events:send-reminders')->hourly();
Schedule::command('invitations:clean-expired')->daily()->at('02:00');
Schedule::command('horizon:snapshot')->everyFiveMinutes();
