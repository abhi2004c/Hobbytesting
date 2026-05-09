<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Event\DTOs\CreateEventDTO;
use App\Domain\Event\DTOs\RsvpDTO;
use App\Domain\Event\DTOs\UpdateEventDTO;
use App\Domain\Event\Repositories\Contracts\EventRepositoryInterface;
use App\Domain\Event\Services\EventService;
use App\Domain\Event\Services\RsvpService;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Event\CreateEventRequest;
use App\Http\Requests\Event\RsvpRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends BaseApiController
{
    public function __construct(
        private readonly EventService $events,
        private readonly RsvpService $rsvps,
        private readonly EventRepositoryInterface $repository,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);
        $events = $this->repository->getUpcomingEvents($perPage);

        return $this->paginatedResponse($events, 'Events retrieved.');
    }

    public function store(CreateEventRequest $request): JsonResponse
    {
        $dto = CreateEventDTO::fromRequest($request->validated());
        $event = $this->events->create($dto);

        if ($request->hasFile('cover')) {
            $event->addMediaFromRequest('cover')->toMediaCollection('cover');
        }

        return $this->successResponse(
            $event->load(['group', 'creator']),
            'Event created.',
            201
        );
    }

    public function show(Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        return $this->successResponse(
            $event->load(['group', 'creator']),
            'Event retrieved.'
        );
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $dto = UpdateEventDTO::fromRequest($request->validated());
        $event = $this->events->update($event, $dto);

        return $this->successResponse($event, 'Event updated.');
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('delete', $event);
        $this->events->delete($event);

        return $this->successResponse(null, 'Event deleted.');
    }

    public function cancel(Request $request, Event $event): JsonResponse
    {
        $this->authorize('cancel', $event);

        $event = $this->events->cancel($event, $request->input('reason'), $request->user());

        return $this->successResponse($event, 'Event cancelled.');
    }

    public function rsvp(RsvpRequest $request, Event $event): JsonResponse
    {
        $dto = RsvpDTO::fromRequest([
            ...$request->validated(),
            'event_id' => $event->id,
            'user_id' => $request->user()->id,
        ]);

        $result = $this->rsvps->rsvp($dto);

        return $this->successResponse(
            $result,
            $result['waitlisted'] ? 'Added to waitlist.' : 'RSVP confirmed.'
        );
    }

    public function cancelRsvp(Request $request, Event $event): JsonResponse
    {
        $this->rsvps->cancelRsvp($event, $request->user());

        return $this->successResponse(null, 'RSVP cancelled.');
    }

    public function attendees(Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        return $this->successResponse([
            'going' => $this->rsvps->getAttendees($event),
            'maybe' => $event->maybeAttendees()->get(),
            'waitlist' => $event->waitlist()->with('user')->get(),
            'rsvp_count' => $event->rsvp_count_cache,
            'spots_remaining' => $event->spots_remaining,
        ], 'Attendees retrieved.');
    }
}