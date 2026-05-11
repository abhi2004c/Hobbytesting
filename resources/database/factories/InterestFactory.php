<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Interest;
use Illuminate\Database\Eloquent\Factories\Factory;

class InterestFactory extends Factory
{
    protected $model = Interest::class;

    public function definition(): array
    {
        return [
            'name'     => fake()->unique()->word(),
            'slug'     => fn (array $attrs) => str($attrs['name'])->slug()->toString(),
            'category' => fake()->randomElement(['sports', 'arts', 'technology', 'outdoors', 'music']),
            'icon'     => null,
        ];
    }
}
