<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'user_id'         => User::factory(),
            'type'            => 'text',
            'content'         => fake()->sentence(),
            'attachments'     => null,
            'parent_id'       => null,
            'is_edited'       => false,
            'edited_at'       => null,
        ];
    }

    public function image(): static
    {
        return $this->state(['type' => 'image']);
    }

    public function edited(): static
    {
        return $this->state([
            'is_edited' => true,
            'edited_at' => now(),
        ]);
    }
}
