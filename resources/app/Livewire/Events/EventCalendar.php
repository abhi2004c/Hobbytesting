<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Domain\Event\Repositories\Contracts\EventRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;

class EventCalendar extends Component
{
    #[Url(as: 'm')]
    public ?string $month = null;

    public function mount(): void
    {
        $this->month ??= now()->format('Y-m');
    }

    public function previous(): void
    {
        $this->month = Carbon::createFromFormat('Y-m', $this->month)
            ->subMonth()
            ->format('Y-m');
    }

    public function next(): void
    {
        $this->month = Carbon::createFromFormat('Y-m', $this->month)
            ->addMonth()
            ->format('Y-m');
    }

    public function render(): View
    {
        $cursor = Carbon::createFromFormat('Y-m', $this->month)->startOfMonth();
        $start = $cursor->copy()->startOfWeek();
        $end = $cursor->copy()->endOfMonth()->endOfWeek();

        $repo = app(EventRepositoryInterface::class);
        $events = $repo->getEventsByDateRange($start, $end);

        $eventsByDate = $events->groupBy(
            fn ($e) => $e->starts_at->format('Y-m-d')
        );

        $days = $this->buildDays($start, $end, $eventsByDate, $cursor);

        return view('livewire.events.event-calendar', [
            'days' => $days,
            'cursor' => $cursor,
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function buildDays(
        Carbon $start,
        Carbon $end,
        Collection $eventsByDate,
        Carbon $cursor,
    ): Collection {
        $days = collect();
        $current = $start->copy();

        while ($current->lte($end)) {
            $key = $current->format('Y-m-d');
            $days->push([
                'date' => $current->copy(),
                'inMonth' => $current->month === $cursor->month,
                'isToday' => $current->isToday(),
                'events' => $eventsByDate->get($key, collect()),
            ]);
            $current->addDay();
        }

        return $days;
    }
}