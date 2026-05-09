<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Group;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Active: ' . User::active()->count())
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->chart($this->getUserGrowth()),

            Stat::make('Total Groups', Group::count())
                ->description('Verified: ' . Group::where('is_verified', true)->count())
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('Total Events', Event::count())
                ->description('Upcoming: ' . Event::where('status', 'published')->where('starts_at', '>', now())->count())
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),

            Stat::make('Total Posts', Post::count())
                ->description('Today: ' . Post::whereDate('created_at', today())->count())
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
        ];
    }

    private function getUserGrowth(): array
    {
        return collect(range(6, 0, -1))->map(
            fn (int $daysAgo) => User::whereDate('created_at', now()->subDays($daysAgo))->count()
        )->toArray();
    }
}
