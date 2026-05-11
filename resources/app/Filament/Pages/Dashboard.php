<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            Widgets\StatsOverview::class,
            Widgets\RecentSignupsChart::class,
            Widgets\TopGroupsTable::class,
            Widgets\PlatformActivityFeed::class,
        ];
    }

    public function getColumns(): array|int
    {
        return 2;
    }
}
