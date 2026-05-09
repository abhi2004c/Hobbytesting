<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\GroupPrivacy;
use App\Models\Group;
use App\Models\GroupCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company() . ' ' . $this->faker->word();

        return [
            'owner_id'    => User::factory(),
            'category_id' => GroupCategory::factory(),
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . Str::lower(Str::random(5)),
            'description' => $this->faker->paragraph(3),
            'privacy'     => GroupPrivacy::Public->value,
            'location'    => $this->faker->city(),
            'latitude'    => $this->faker->latitude(),
            'longitude'   => $this->faker->longitude(),
            'is_verified' => false,
            'is_featured' => false,
            'settings'    => [],
        ];
    }

    public function public(): static
    {
        return $this->state(fn () => ['privacy' => GroupPrivacy::Public->value]);
    }

    public function private(): static
    {
        return $this->state(fn () => ['privacy' => GroupPrivacy::Private->value]);
    }

    public function secret(): static
    {
        return $this->state(fn () => ['privacy' => GroupPrivacy::Secret->value]);
    }

    public function verified(): static
    {
        return $this->state(fn () => ['is_verified' => true]);
    }

    public function withMembers(int $count = 10): static
    {
        return $this->afterCreating(function (Group $group) use ($count) {
            $users = User::factory()->count($count)->create();
            foreach ($users as $user) {
                $group->memberships()->create([
                    'user_id'   => $user->id,
                    'role'      => 'member',
                    'status'    => 'active',
                    'joined_at' => now(),
                ]);
            }
            $group->refreshMemberCount();
        });
    }
}