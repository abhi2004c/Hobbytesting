<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PollFactory extends Factory
{
    protected $model = Poll::class;

    public function definition(): array
    {
        return [
            'post_id'        => Post::factory(),
            'question'       => fake()->sentence() . '?',
            'ends_at'        => fake()->optional(0.5)->dateTimeBetween('+1 day', '+7 days'),
            'allow_multiple' => false,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Poll $poll) {
            if ($poll->options()->count() === 0) {
                PollOption::factory()->count(3)->create(['poll_id' => $poll->id]);
            }
        });
    }

    public function withVotes(): static
    {
        return $this->afterCreating(function (Poll $poll) {
            // Create some votes on random options
            foreach ($poll->options->take(2) as $option) {
                $option->increment('votes_count', rand(1, 10));
            }
        });
    }
}
