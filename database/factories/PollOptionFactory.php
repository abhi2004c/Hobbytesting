<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Poll;
use App\Models\PollOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class PollOptionFactory extends Factory
{
    protected $model = PollOption::class;

    public function definition(): array
    {
        return [
            'poll_id'     => Poll::factory(),
            'text'        => fake()->words(3, true),
            'votes_count' => 0,
            'order'       => 0,
        ];
    }
}
