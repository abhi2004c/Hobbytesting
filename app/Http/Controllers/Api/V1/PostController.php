<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\Feed\DTOs\CreatePostDTO;
use App\Domain\Feed\DTOs\UpdatePostDTO;
use App\Domain\Feed\Services\PostService;
use App\Domain\Feed\Services\ReactionService;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Group;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends BaseApiController
{
    public function __construct(
        private readonly PostService     $posts,
        private readonly ReactionService $reactions,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $posts = $this->posts->getPersonalFeed($request->user());
        return $this->paginatedResponse($posts);
    }

    public function store(CreatePostRequest $request): JsonResponse
    {
        $group = Group::findOrFail($request->validated('group_id'));
        $post  = $this->posts->create(
            $request->user(),
            $group,
            CreatePostDTO::fromRequest(array_merge($request->validated(), ['user_id' => $request->user()->id])),
        );

        return $this->successResponse($post->load(['user', 'group']), 'Post created.', 201);
    }

    public function show(Post $post): JsonResponse
    {
        $this->authorize('view', $post);
        return $this->successResponse($post->load(['user', 'group', 'comments.user', 'poll.options']));
    }

    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $post = $this->posts->update($post, UpdatePostDTO::fromRequest($request->validated()));
        return $this->successResponse($post);
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);
        $this->posts->delete($post);
        return $this->successResponse(null, 'Post deleted.');
    }

    public function pin(Post $post): JsonResponse
    {
        $this->authorize('pin', $post);
        $this->posts->pin($post, auth()->user());
        return $this->successResponse($post->fresh(), 'Post pinned.');
    }

    public function react(Request $request, Post $post): JsonResponse
    {
        $request->validate(['type' => 'required|string|in:like,love,wow,haha']);
        $this->reactions->react($post, $request->user(), $request->input('type'));
        return $this->successResponse(['summary' => $this->reactions->getReactionSummary($post)]);
    }

    public function unreact(Post $post): JsonResponse
    {
        $this->reactions->unreact($post, auth()->user());
        return $this->successResponse(null, 'Reaction removed.');
    }

    public function share(Request $request, Post $post): JsonResponse
    {
        $request->validate(['group_id' => 'required|integer|exists:groups,id']);
        $targetGroup = Group::findOrFail($request->input('group_id'));
        $newPost = $this->posts->share($post, $request->user(), $targetGroup);
        return $this->successResponse($newPost, 'Post shared.', 201);
    }
}
