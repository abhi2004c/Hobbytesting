<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Domain\User\Repositories\Contracts\UserRepositoryInterface;
use App\Models\Interest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * GET /api/v1/users/me/groups
     */
    public function myGroups(Request $request): JsonResponse
    {
        $groups = $request->user()
            ->groups()
            ->with('category')
            ->withCount('memberships')
            ->orderBy('name')
            ->get();

        return $this->successResponse($groups);
    }

    /**
     * GET /api/v1/users/me/events
     */
    public function myEvents(Request $request): JsonResponse
    {
        $events = $request->user()
            ->attendingEvents()
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->get();

        return $this->successResponse($events);
    }

    /**
     * POST /api/v1/users/me/interests
     */
    public function syncInterests(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'interest_ids'   => ['required', 'array', 'min:1', 'max:20'],
            'interest_ids.*' => ['integer', 'exists:interests,id'],
        ]);

        $request->user()->interests()->sync($validated['interest_ids']);

        return $this->successResponse(
            $request->user()->fresh('interests'),
            'Interests updated.'
        );
    }

    /**
     * GET /api/v1/users/discover
     */
    public function discover(Request $request): JsonResponse
    {
        $user = $request->user()->load('interests');
        $interestIds = $user->interests->pluck('id')->toArray();

        if (empty($interestIds)) {
            return $this->successResponse([], 'Add interests to discover people.');
        }

        $result = $this->users->discoverByInterests(
            $interestIds,
            $user->id,
            (int) $request->query('per_page', 15),
        );

        return $this->paginatedResponse($result);
    }

    /**
     * GET /api/v1/users/{user}
     */
    public function show(int $userId): JsonResponse
    {
        $user = $this->users->findById($userId, ['interests', 'groups']);

        if (! $user) {
            return $this->errorResponse('User not found.', 404);
        }

        return $this->successResponse($user);
    }

    /**
     * GET /api/v1/interests
     */
    public function interests(): JsonResponse
    {
        $interests = Interest::query()
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return $this->successResponse($interests);
    }
}
