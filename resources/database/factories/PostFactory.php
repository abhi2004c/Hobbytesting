<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PostType;
use App\Models\Group;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'group_id'        => Group::factory(),
            'user_id'         => User::factory(),
            'type'            => PostType::Text->value,
            'content'         => fake()->paragraphs(2, true),
            'is_pinned'       => false,
            'is_announcement' => false,
            'visibility'      => 'public',
            'likes_count'     => 0,
            'comments_count'  => 0,
            'shares_count'    => 0,
        ];
    }

    public function text(): static
    {
        return $this->state(['type' => PostType::Text->value]);
    }

    public function withImage(): static
    {
        return $this->state(['type' => PostType::Image->value]);
    }

    public function withPoll(): static
    {
        return $this->state(['type' => PostType::Poll->value])
            ->has(\App\Models\Poll::factory()->has(
                \App\Models\PollOption::factory()->count(3),
                'options'
            ), 'poll');
    }

    public function pinned(): static
    {
        return $this->state(['is_pinned' => true]);
    }

    public function announcement(): static
    {
        return $this->state(['is_announcement' => true]);
    }
}
