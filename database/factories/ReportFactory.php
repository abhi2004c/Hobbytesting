<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'reporter_id'     => User::factory(),
            'reportable_type' => 'App\\Models\\Post',
            'reportable_id'   => 1,
            'reason'          => fake()->randomElement(['spam', 'harassment', 'hate_speech', 'violence', 'other']),
            'description'     => fake()->optional()->sentence(),
            'status'          => 'pending',
        ];
    }

    public function resolved(): static
    {
        return $this->state([
            'status'      => 'resolved',
            'resolved_by' => User::factory(),
            'resolved_at' => now(),
        ]);
    }
}
