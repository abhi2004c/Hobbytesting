<?php

declare(strict_types=1);

namespace App\Domain\Feed\Services;

use App\Domain\Feed\DTOs\CreatePostDTO;
use App\Domain\Feed\DTOs\UpdatePostDTO;
use App\Domain\Feed\Repositories\Contracts\PostRepositoryInterface;
use App\Enums\PostType;
use App\Events\Feed\PostCreated;
use App\Models\Group;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PostService
{
    public function __construct(
        private readonly PostRepositoryInterface $posts,
    ) {
    }

    public function getPersonalFeed(User $user, string $filter = 'latest'): LengthAwarePaginator
    {
        return $this->posts->getPersonalFeed($user, $filter);
    }

    public function getGroupFeed(Group $group, string $filter = 'latest'): LengthAwarePaginator
    {
        return $this->posts->getGroupFeed($group->id, $filter);
    }

    public function create(CreatePostDTO $dto): Post
    {
        return DB::transaction(function () use ($dto): Post {
            /** @var Post $post */
            $post = $this->posts->create($dto->toArray());

            if ($dto->type === PostType::Poll) {
                $this->createPollFor($post, $dto);
            }

            $post->load(['author', 'group', 'poll.options']);

            PostCreated::dispatch($post);

            $this->invalidateCache($post);

            return $post;
        });
    }

    public function update(Post $post, UpdatePostDTO $dto): Post
    {
        return DB::transaction(function () use ($post, $dto): Post {
            $payload = $dto->toArray();

            // If pinning, unpin all other pinned posts in the same group
            if (! empty($payload['is_pinned'])) {
                Post::query()
                    ->where('group_id', $post->group_id)
                    ->where('id', '!=', $post->id)
                    ->where('is_pinned', true)
                    ->update(['is_pinned' => false]);
            }

            $post->update($payload);
            $post->refresh();

            $this->invalidateCache($post);

            return $post;
        });
    }

    public function delete(Post $post): bool
    {
        return DB::transaction(function () use ($post): bool {
            $deleted = (bool) $post->delete();
            $this->invalidateCache($post);

            return $deleted;
        });
    }

    public function pin(Post $post): Post
    {
        return $this->update($post, UpdatePostDTO::fromRequest(['is_pinned' => true]));
    }

    public function unpin(Post $post): Post
    {
        return $this->update($post, UpdatePostDTO::fromRequest(['is_pinned' => false]));
    }

    public function share(Post $original, int $userId, int $groupId, ?string $content = null): Post
    {
        return DB::transaction(function () use ($original, $userId, $groupId, $content): Post {
            /** @var Post $shared */
            $shared = $this->posts->create([
                'group_id' => $groupId,
                'user_id' => $userId,
                'type' => PostType::Text->value,
                'content' => $content ?? '',
                'shared_post_id' => $original->id,
                'visibility' => 'group',
            ]);

            $original->increment('shares_count');

            $shared->load(['author', 'sharedPost.author']);
            PostCreated::dispatch($shared);

            $this->invalidateCache($shared);

            return $shared;
        });
    }

    private function createPollFor(Post $post, CreatePostDTO $dto): Poll
    {
        throw_if(
            empty($dto->pollQuestion) || empty($dto->pollOptions) || count($dto->pollOptions) < 2,
            \InvalidArgumentException::class,
            'Polls require a question and at least two options.'
        );

        /** @var Poll $poll */
        $poll = Poll::query()->create([
            'post_id' => $post->id,
            'question' => $dto->pollQuestion,
            'ends_at' => $dto->pollEndsAt,
            'allow_multiple' => $dto->pollAllowMultiple,
        ]);

        foreach (array_values($dto->pollOptions) as $index => $text) {
            PollOption::query()->create([
                'poll_id' => $poll->id,
                'text' => $text,
                'order' => $index,
            ]);
        }

        return $poll;
    }

    private function invalidateCache(Post $post): void
    {
        Cache::forget("group:{$post->group_id}:posts");
        Cache::forget("post:{$post->id}");

        // Invalidate personal feeds for all members of this group (best-effort)
        Cache::tags(['feed', "group:{$post->group_id}"])->flush();
    }
}