<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;

class RecentSignupsChart extends ChartWidget
{
    protected ?string $heading = 'Signups (Last 30 Days)';
    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'line';
    }

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(fn (int $i) => now()->subDays($i));

        return [
            'datasets' => [
                [
                    'label'           => 'New Users',
                    'data'            => $days->map(fn ($day) => User::whereDate('created_at', $day)->count())->toArray(),
                    'borderColor'     => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.08)',
                    'fill'            => true,
                    'tension'         => 0.3,
                ],
            ],
            'labels' => $days->map(fn ($day) => $day->format('M j'))->toArray(),
        ];
    }
}
