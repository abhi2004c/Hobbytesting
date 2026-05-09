<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'post_id'   => Post::factory(),
            'user_id'   => User::factory(),
            'parent_id' => null,
            'content'   => fake()->sentence(),
        ];
    }

    public function reply(int $parentId): static
    {
        return $this->state(['parent_id' => $parentId]);
    }
}
