<?php

declare(strict_types=1);

use App\Domain\Auth\DTOs\SocialAuthDTO;
use App\Domain\Auth\Services\AuthService;
use App\Models\User;

beforeEach(fn () => $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class));

it('creates a new verified user from a Google login', function () {
    $service = app(AuthService::class);

    $result = $service->handleSocialLogin(new SocialAuthDTO(
        provider: 'google',
        providerId: 'google-123',
        email: 'new@gmail.com',
        name: 'New User',
        avatar: 'https://example.com/a.jpg',
    ));

    expect($result['is_new'])->toBeTrue()
        ->and($result['user']->email_verified_at)->not->toBeNull()
        ->and($result['user']->google_id)->toBe('google-123');
});

it('reuses an existing user when emails match', function () {
    $existing = User::factory()->create(['email' => 'me@gmail.com']);
    $service  = app(AuthService::class);

    $result = $service->handleSocialLogin(new SocialAuthDTO(
        provider: 'google',
        providerId: 'google-456',
        email: 'me@gmail.com',
        name: 'Me',
    ));

    expect($result['is_new'])->toBeFalse()
        ->and($result['user']->id)->toBe($existing->id)
        ->and($result['user']->fresh()->google_id)->toBe('google-456');
});