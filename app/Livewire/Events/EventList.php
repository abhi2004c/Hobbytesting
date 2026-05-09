<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class EventList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'filter')]
    public string $filter = 'upcoming'; // upcoming | today | past

    #[Url(as: 'type')]
    public string $type = '';

    #[Url(as: 'group')]
    public ?int $groupId = null;

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['search', 'filter', 'type', 'groupId'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $query = Event::query()
            ->with(['group', 'creator'])
            ->where('status', EventStatus::Published->value);

        $this->applyFilter($query);
        $this->applyType($query);
        $this->applySearch($query);
        $this->applyGroup($query);

        return view('livewire.events.event-list', [
            'events' => $query->paginate(12),
        ]);
    }

    private function applyFilter(Builder $query): void
    {
        match ($this->filter) {
            'today' => $query->today(),
            'past' => $query->past(),
            default => $query->upcoming(),
        };
    }

    private function applyType(Builder $query): void
    {
        if ($this->type !== '' && ($enum = EventType::tryFrom($this->type))) {
            $query->byType($enum);
        }
    }

    private function applySearch(Builder $query): void
    {
        if ($this->search === '') {
            return;
        }

        $term = '%'.$this->search.'%';
        $query->where(function (Builder $q) use ($term): void {
            $q->where('title', 'like', $term)
                ->orWhere('description', 'like', $term);
        });
    }

    private function applyGroup(Builder $query): void
    {
        if ($this->groupId !== null) {
            $query->where('group_id', $this->groupId);
        }
    }
}