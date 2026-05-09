<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'type'       => 'direct',
            'name'       => null,
            'group_id'   => null,
            'created_by' => User::factory(),
        ];
    }

    public function group(): static
    {
        return $this->state([
            'type' => 'group',
            'name' => fake()->words(3, true),
        ]);
    }
}
