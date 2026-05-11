<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domain\User\DTOs\UpdateProfileDTO;
use App\Domain\User\Services\ProfileService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ProfileService $profiles,
    ) {}

    public function show(Request $request): View
    {
        $user = $request->user()->load(['interests', 'groups', 'attendingEvents']);

        return view('profile.show', compact('user'));
    }

    public function edit(Request $request): View
    {
        $user = $request->user()->load('interests');

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'min:2', 'max:80'],
            'bio'          => ['nullable', 'string', 'max:500'],
            'city'         => ['nullable', 'string', 'max:100'],
            'country'      => ['nullable', 'string', 'size:2'],
            'website'      => ['nullable', 'url', 'max:255'],
            'interest_ids' => ['nullable', 'array'],
            'interest_ids.*' => ['integer', 'exists:interests,id'],
        ]);

        $this->profiles->update($request->user(), UpdateProfileDTO::fromRequest($validated));

        return back()->with('success', 'Profile updated!');
    }

    public function view(User $user): View
    {
        $user->load(['interests', 'groups']);

        return view('profile.show', compact('user'));
    }
}
