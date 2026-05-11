<?php

declare(strict_types=1);

namespace App\Domain\Group\Services;

use App\Domain\Group\DTOs\CreateGroupDTO;
use App\Domain\Group\DTOs\UpdateGroupDTO;
use App\Domain\Group\Exceptions\GroupNotFoundException;
use App\Domain\Group\Repositories\Contracts\GroupRepositoryInterface;
use App\Enums\MemberRole;
use App\Events\Group\GroupCreated;
use App\Models\Group;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class GroupService
{
    public function __construct(
        private readonly GroupRepositoryInterface $repo,
        private readonly MembershipService $memberships,
    ) {}

    public function create(User $owner, CreateGroupDTO $dto): Group
    {
        return DB::transaction(function () use ($owner, $dto) {
            $group = $this->repo->create([
                'owner_id'    => $owner->id,
                'name'        => $dto->name,
                'slug'        => $this->generateUniqueSlug($dto->name),
                'description' => $dto->description,
                'category_id' => $dto->categoryId,
                'privacy'     => $dto->privacy->value,
                'location'    => $dto->location,
                'latitude'    => $dto->latitude,
                'longitude'   => $dto->longitude,
                'max_members' => $dto->maxMembers,
                'settings'    => $dto->settings,
            ]);

            $this->memberships->addMember($group, $owner, MemberRole::Owner);

            event(new GroupCreated($group, $owner));

            return $group->fresh(['category', 'owner']);
        });
    }

    public function update(Group $group, UpdateGroupDTO $dto): Group
    {
        return DB::transaction(function () use ($group, $dto) {
            $payload = collect($dto->toArray())
                ->mapWithKeys(fn ($v, $k) => [Str::snake($k) => $v])
                ->toArray();

            // Map dto's camelCase enum to scalar
            if (isset($payload['privacy']) && $payload['privacy'] instanceof \App\Enums\GroupPrivacy) {
                $payload['privacy'] = $payload['privacy']->value;
            }

            $group->update($payload);
            $this->invalidateCache($group);

            return $group->fresh(['category', 'owner']);
        });
    }

    public function delete(Group $group): void
    {
        DB::transaction(function () use ($group) {
            $group->delete();
            $this->invalidateCache($group);
        });
    }

    public function transferOwnership(Group $group, User $newOwner): void
    {
        DB::transaction(function () use ($group, $newOwner) {
            throw_unless(
                $group->isMember($newOwner),
                GroupNotFoundException::class,
                'Target user must already be a member.',
            );

            $oldOwnerId = $group->owner_id;

            // Demote old owner to admin
            $group->memberships()
                ->where('user_id', $oldOwnerId)
                ->update(['role' => MemberRole::Admin->value]);

            // Promote new owner
            $group->memberships()
                ->where('user_id', $newOwner->id)
                ->update(['role' => MemberRole::Owner->value]);

            $group->update(['owner_id' => $newOwner->id]);

            $this->invalidateCache($group);
        });
    }

    public function search(array $filters): LengthAwarePaginator
    {
        return $this->repo->searchByKeyword(
            keyword: (string) ($filters['q'] ?? ''),
            filters: $filters,
            perPage: (int) ($filters['per_page'] ?? 15),
        );
    }

    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base . '-' . Str::lower(Str::random(5));

        while (Group::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . Str::lower(Str::random(6));
        }

        return $slug;
    }

    private function invalidateCache(Group $group): void
    {
        Cache::forget("group.{$group->id}.member_count");
        Cache::forget("group.{$group->slug}.details");
    }
}