<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GroupCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GroupCategoryFactory extends Factory
{
    protected $model = GroupCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);

        return [
            'name'        => Str::title($name),
            'slug'        => Str::slug($name),
            'icon'        => 'tag',
            'color'       => $this->faker->hexColor(),
            'description' => $this->faker->sentence(),
            'is_active'   => true,
            'order'       => $this->faker->numberBetween(0, 100),
        ];
    }
}