<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Models\Event;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class AttendeeList extends Component
{
    public Event $event;
    public string $tab = 'going';

    public function mount(Event $event): void
    {
        $this->event = $event;
    }

    #[On('echo-private:event.{event.id},rsvp.created')]
    #[On('echo-private:event.{event.id},rsvp.cancelled')]
    #[On('echo-private:event.{event.id},waitlist.promoted')]
    public function refresh(): void
    {
        $this->event->refresh();
    }

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['going', 'maybe', 'waitlist'], true) ? $tab : 'going';
    }

    public function render(): View
    {
        $attendees = match ($this->tab) {
            'maybe' => $this->event->maybeAttendees()->paginate(20),
            'waitlist' => $this->event->waitlist()->with('user')->paginate(20),
            default => $this->event->attendees()->paginate(20),
        };

        return view('livewire.events.attendee-list', [
            'attendees' => $attendees,
            'goingCount' => $this->event->rsvp_count_cache,
            'maybeCount' => $this->event->maybeAttendees()->count(),
            'waitlistCount' => $this->event->waitlist()->count(),
        ]);
    }
}