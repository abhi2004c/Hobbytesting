<?php

declare(strict_types=1);

namespace App\Domain\Event\Services;

use App\Domain\Event\DTOs\RsvpDTO;
use App\Domain\Event\Exceptions\EventAlreadyStartedException;
use App\Domain\Event\Exceptions\EventCancelledException;
use App\Domain\Event\Exceptions\EventCapacityExceededException;
use App\Enums\RsvpStatus;
use App\Events\Event\RsvpCancelled;
use App\Events\Event\RsvpCreated;
use App\Events\Event\WaitlistPromoted;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\EventWaitlist;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RsvpService
{
    /**
     * Submit / change an RSVP. If the event is full and user requests "going",
     * place them on the waitlist instead.
     *
     * @return array{rsvp: ?EventRsvp, waitlisted: bool, position: ?int}
     */
    public function rsvp(RsvpDTO $dto): array
    {
        return DB::transaction(function () use ($dto): array {
            /** @var Event $event */
            $event = Event::query()->lockForUpdate()->findOrFail($dto->eventId);

            throw_if(
                $event->isCancelled(),
                EventCancelledException::class
            );

            throw_if(
                $event->hasStarted(),
                EventAlreadyStartedException::class
            );

            // Capacity check only matters when user is going
            if ($dto->status === RsvpStatus::Going) {
                $existing = $event->rsvps()->where('user_id', $dto->userId)->first();
                $alreadyGoing = $existing?->status === RsvpStatus::Going;

                if (! $alreadyGoing && ! $event->hasCapacityFor(1)) {
                    // Event is full → waitlist
                    $position = $this->addToWaitlist($event, $dto->userId);

                    // If they had a previous "maybe" / "not_going" RSVP, remove it
                    $existing?->delete();

                    return ['rsvp' => null, 'waitlisted' => true, 'position' => $position];
                }
            }

            /** @var EventRsvp $rsvp */
            $rsvp = EventRsvp::query()->updateOrCreate(
                [
                    'event_id' => $dto->eventId,
                    'user_id' => $dto->userId,
                ],
                [
                    'status' => $dto->status->value,
                    'note' => $dto->note,
                ]
            );

            // If they joined the waitlist previously and now switch off "going",
            // remove from waitlist (no need anymore).
            if ($dto->status !== RsvpStatus::Going) {
                EventWaitlist::query()
                    ->where('event_id', $event->id)
                    ->where('user_id', $dto->userId)
                    ->delete();
                $this->resequenceWaitlist($event);
            }

            $event->refreshRsvpCount();

            RsvpCreated::dispatch($event->fresh(), $rsvp);

            return ['rsvp' => $rsvp, 'waitlisted' => false, 'position' => null];
        });
    }

    public function cancelRsvp(Event $event, User $user): void
    {
        DB::transaction(function () use ($event, $user): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);

            $rsvp = $event->rsvps()->where('user_id', $user->id)->first();
            $waitlist = EventWaitlist::query()
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            $wasGoing = $rsvp?->status === RsvpStatus::Going;

            $rsvp?->delete();
            $waitlist?->delete();

            if ($waitlist) {
                $this->resequenceWaitlist($event);
            }

            $event->refreshRsvpCount();

            if ($rsvp) {
                RsvpCancelled::dispatch($event->fresh(), $user->id);
            }

            if ($wasGoing) {
                $this->promoteFromWaitlist($event->fresh());
            }
        });
    }

    /**
     * Promote next person on the waitlist to "going" if capacity allows.
     */
    public function promoteFromWaitlist(Event $event): ?EventRsvp
    {
        return DB::transaction(function () use ($event): ?EventRsvp {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);

            if (! $event->hasCapacityFor(1)) {
                return null;
            }

            /** @var EventWaitlist|null $next */
            $next = EventWaitlist::query()
                ->where('event_id', $event->id)
                ->orderBy('position')
                ->first();

            if (! $next) {
                return null;
            }

            /** @var EventRsvp $rsvp */
            $rsvp = EventRsvp::query()->updateOrCreate(
                ['event_id' => $event->id, 'user_id' => $next->user_id],
                ['status' => RsvpStatus::Going->value]
            );

            $userId = $next->user_id;
            $next->delete();
            $this->resequenceWaitlist($event);
            $event->refreshRsvpCount();

            WaitlistPromoted::dispatch($event->fresh(), $userId);

            return $rsvp;
        });
    }

    public function getAttendees(Event $event): Collection
    {
        return $event->attendees()->orderBy('event_rsvps.created_at')->get();
    }

    public function getWaitlistPosition(Event $event, User $user): ?int
    {
        $entry = EventWaitlist::query()
            ->where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        return $entry?->position;
    }

    private function addToWaitlist(Event $event, int $userId): int
    {
        throw_if(
            $event->capacity === null,
            EventCapacityExceededException::class,
            'Event has unlimited capacity; waitlist not applicable.'
        );

        $existing = EventWaitlist::query()
            ->where('event_id', $event->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return $existing->position;
        }

        $next = ((int) EventWaitlist::query()
            ->where('event_id', $event->id)
            ->max('position')) + 1;

        EventWaitlist::query()->create([
            'event_id' => $event->id,
            'user_id' => $userId,
            'position' => $next,
        ]);

        return $next;
    }

    private function resequenceWaitlist(Event $event): void
    {
        $entries = EventWaitlist::query()
            ->where('event_id', $event->id)
            ->orderBy('position')
            ->get();

        $position = 1;
        foreach ($entries as $entry) {
            if ($entry->position !== $position) {
                $entry->forceFill(['position' => $position])->save();
            }
            $position++;
        }
    }
}