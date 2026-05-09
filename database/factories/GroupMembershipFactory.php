<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MemberRole;
use App\Enums\MemberStatus;
use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupMembershipFactory extends Factory
{
    protected $model = GroupMembership::class;

    public function definition(): array
    {
        return [
            'group_id'  => Group::factory(),
            'user_id'   => User::factory(),
            'role'      => MemberRole::Member->value,
            'status'    => MemberStatus::Active->value,
            'joined_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => MemberStatus::Pending->value, 'joined_at' => null]);
    }

    public function admin(): static
    {
        return $this->state(fn () => ['role' => MemberRole::Admin->value]);
    }

    public function owner(): static
    {
        return $this->state(fn () => ['role' => MemberRole::Owner->value]);
    }

    public function banned(): static
    {
        return $this->state(fn () => [
            'status'     => MemberStatus::Banned->value,
            'ban_reason' => 'Violation of community guidelines',
        ]);
    }
}