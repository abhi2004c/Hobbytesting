<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RsvpStatus;
use App\Models\Event;
use App\Models\EventRsvp;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventRsvp>
 */
class EventRsvpFactory extends Factory
{
    protected $model = EventRsvp::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'status' => RsvpStatus::Going->value,
            'note' => null,
        ];
    }

    public function going(): static
    {
        return $this->state(fn () => ['status' => RsvpStatus::Going->value]);
    }

    public function maybe(): static
    {
        return $this->state(fn () => ['status' => RsvpStatus::Maybe->value]);
    }

    public function notGoing(): static
    {
        return $this->state(fn () => ['status' => RsvpStatus::NotGoing->value]);
    }
}