<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\Feed\DTOs\CreatePostDTO;
use App\Domain\Feed\DTOs\UpdatePostDTO;
use App\Domain\Feed\Services\PostService;
use App\Domain\Feed\Services\ReactionService;
use App\Enums\ReactionType;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private readonly PostService $posts,
        private readonly ReactionService $reactions,
    ) {}

    public function index(): \Illuminate\View\View
    {
        return view('feed.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Post::class);

        $validated = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'content'  => ['required', 'string', 'max:5000'],
            'type'     => ['sometimes', 'string', 'in:text,image,link,poll'],
        ]);

        $this->posts->create(
            CreatePostDTO::fromRequest(array_merge($validated, [
                'user_id' => $request->user()->id,
                'type'    => $validated['type'] ?? 'text',
            ])),
        );

        return back()->with('success', 'Post created!');
    }

    public function show(Post $post): \Illuminate\View\View
    {
        $this->authorize('view', $post);
        $post->load(['author', 'group', 'poll.options', 'comments.author']);

        return view('feed.show', compact('post'));
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);

        $validated = $request->validate([
            'content'         => ['sometimes', 'string', 'max:5000'],
            'is_pinned'       => ['sometimes', 'boolean'],
            'is_announcement' => ['sometimes', 'boolean'],
            'visibility'      => ['sometimes', 'string', 'in:public,group,private'],
        ]);

        $this->posts->update($post, UpdatePostDTO::fromRequest($validated));

        return back()->with('success', 'Post updated.');
    }

    public function pin(Post $post): RedirectResponse
    {
        $this->authorize('update', $post);
        $this->posts->pin($post);

        return back()->with('success', 'Post pinned.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->authorize('delete', $post);
        $this->posts->delete($post);

        return back()->with('success', 'Post deleted.');
    }

    public function react(Request $request, Post $post): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:like,love,wow,haha'],
        ]);

        $this->reactions->react($post, $request->user(), ReactionType::from($validated['type']));

        return back();
    }
}
