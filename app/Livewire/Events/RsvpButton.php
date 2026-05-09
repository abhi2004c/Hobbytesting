<?php

declare(strict_types=1);

namespace App\Livewire\Events;

use App\Domain\Event\DTOs\RsvpDTO;
use App\Domain\Event\Services\RsvpService;
use App\Enums\RsvpStatus;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class RsvpButton extends Component
{
    public Event $event;
    public ?string $currentStatus = null;
    public ?int $waitlistPosition = null;
    public bool $isWaitlisted = false;

    public function mount(Event $event): void
    {
        $this->event = $event;
        $this->refreshState();
    }

    #[On('echo-private:event.{event.id},rsvp.created')]
    #[On('echo-private:event.{event.id},rsvp.cancelled')]
    public function refreshOnBroadcast(): void
    {
        $this->event->refresh();
    }

    public function setStatus(string $status): void
    {
        $this->authorize('rsvp', $this->event);

        $rsvpStatus = RsvpStatus::from($status);
        $service = app(RsvpService::class);

        $result = $service->rsvp(RsvpDTO::fromRequest([
            'event_id' => $this->event->id,
            'user_id' => auth()->id(),
            'status' => $rsvpStatus,
        ]));

        $this->event->refresh();
        $this->refreshState();

        if ($result['waitlisted']) {
            $this->dispatch('toast', message: "Added to waitlist (#{$result['position']}).");
        } else {
            $this->dispatch('toast', message: 'RSVP saved.');
        }
    }

    public function cancel(): void
    {
        $service = app(RsvpService::class);
        $service->cancelRsvp($this->event, auth()->user());

        $this->event->refresh();
        $this->refreshState();
        $this->dispatch('toast', message: 'RSVP cancelled.');
    }

    public function render(): View
    {
        return view('livewire.events.rsvp-button');
    }

    private function refreshState(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $rsvp = $this->event->getRsvpFor($user);
        $this->currentStatus = $rsvp?->status->value;

        $position = app(RsvpService::class)->getWaitlistPosition($this->event, $user);
        $this->waitlistPosition = $position;
        $this->isWaitlisted = $position !== null;
    }
}