<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Event\DTOs\CreateEventDTO;
use App\Domain\Event\DTOs\RsvpDTO;
use App\Domain\Event\DTOs\UpdateEventDTO;
use App\Domain\Event\Services\EventService;
use App\Domain\Event\Services\RsvpService;
use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\RsvpRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $events,
        private readonly RsvpService $rsvps,
    ) {
    }

    public function index(): View
    {
        return view('events.index');
    }

    public function create(Group $group): View
    {
        $this->authorize('createEvents', $group);

        return view('events.create', compact('group'));
    }

    public function store(CreateEventRequest $request): RedirectResponse
    {
        $dto = CreateEventDTO::fromRequest($request->validated());
        $event = $this->events->create($dto);

        if ($request->hasFile('cover')) {
            $event->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return redirect()
            ->route('events.show', $event->slug)
            ->with('success', 'Event created.');
    }

    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load(['group', 'creator']);

        return view('events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        return view('events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event): RedirectResponse
    {
        $dto = UpdateEventDTO::fromRequest($request->validated());
        $this->events->update($event, $dto);

        if ($request->hasFile('cover')) {
            $event->clearMediaCollection('cover');
            $event->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return redirect()
            ->route('events.show', $event->slug)
            ->with('success', 'Event updated.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $this->events->delete($event);

        return redirect()
            ->route('groups.show', $event->group->slug)
            ->with('success', 'Event deleted.');
    }

    public function cancel(Event $event): RedirectResponse
    {
        $this->authorize('cancel', $event);

        $reason = request()->input('reason');
        $this->events->cancel($event, $reason, request()->user());

        return back()->with('success', 'Event cancelled and attendees notified.');
    }

    public function rsvp(RsvpRequest $request, Event $event): RedirectResponse
    {
        $dto = RsvpDTO::fromRequest([
            ...$request->validated(),
            'event_id' => $event->id,
            'user_id' => $request->user()->id,
        ]);

        $result = $this->rsvps->rsvp($dto);

        $message = $result['waitlisted']
            ? "You're on the waitlist (position #{$result['position']})."
            : 'RSVP confirmed!';

        return back()->with('success', $message);
    }

    public function cancelRsvp(Event $event): RedirectResponse
    {
        $this->rsvps->cancelRsvp($event, request()->user());

        return back()->with('success', 'RSVP cancelled.');
    }
}