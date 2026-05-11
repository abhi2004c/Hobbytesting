<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReactionFactory extends Factory
{
    protected $model = Reaction::class;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'reactable_type' => 'App\\Models\\Post',
            'reactable_id'   => 1,
            'type'           => fake()->randomElement(['like', 'love', 'wow', 'haha']),
        ];
    }
}
