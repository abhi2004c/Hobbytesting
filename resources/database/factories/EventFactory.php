<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\Event;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+2 months');
        $end = (clone $start)->modify('+2 hours');
        $title = fake()->sentence(4);

        return [
            'group_id' => Group::factory(),
            'creator_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(5)),
            'description' => fake()->paragraphs(2, true),
            'type' => EventType::InPerson->value,
            'location' => fake()->address(),
            'starts_at' => $start,
            'ends_at' => $end,
            'capacity' => fake()->randomElement([null, 10, 25, 50, 100]),
            'status' => EventStatus::Published->value,
            'is_recurring' => false,
            'rsvp_count_cache' => 0,
        ];
    }

    public function upcoming(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->addDays(rand(1, 30)),
            'ends_at' => now()->addDays(rand(1, 30))->addHours(2),
            'status' => EventStatus::Published->value,
        ]);
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'starts_at' => now()->subDays(rand(1, 30)),
            'ends_at' => now()->subDays(rand(1, 30))->addHours(2),
            'status' => EventStatus::Published->value,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status' => EventStatus::Cancelled->value,
            'cancelled_at' => now(),
            'cancellation_reason' => 'Test cancellation',
        ]);
    }

    public function full(): static
    {
        return $this->state(fn () => [
            'capacity' => 5,
            'rsvp_count_cache' => 5,
        ]);
    }

    public function online(): static
    {
        return $this->state(fn () => [
            'type' => EventType::Online->value,
            'online_url' => fake()->url(),
            'location' => null,
        ]);
    }

    public function inPerson(): static
    {
        return $this->state(fn () => [
            'type' => EventType::InPerson->value,
            'location' => fake()->address(),
            'online_url' => null,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => EventStatus::Draft->value]);
    }
}