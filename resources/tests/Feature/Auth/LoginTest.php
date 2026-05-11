<?php

declare(strict_types=1);

use App\Models\User;

beforeEach(fn () => $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class));

it('logs in valid users and returns a Sanctum token', function () {
    $user = User::factory()->create(['password' => 'Secret123']);

    $this->postJson('/api/v1/auth/login', [
        'email'    => $user->email,
        'password' => 'Secret123',
    ])->assertOk()->assertJsonStructure(['data' => ['token', 'expires_at']]);
});

it('rejects bad credentials', function () {
    $user = User::factory()->create(['password' => 'Secret123']);

    $this->postJson('/api/v1/auth/login', [
        'email'    => $user->email,
        'password' => 'WrongPass1',
    ])->assertStatus(401);
});

it('rejects suspended accounts', function () {
    $user = User::factory()->create([
        'password' => 'Secret123',
        'status'   => 'suspended',
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email'    => $user->email,
        'password' => 'Secret123',
    ])->assertStatus(403);
});

it('rate limits brute-force attempts', function () {
    for ($i = 0; $i < 6; $i++) {
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'nope@example.com',
            'password' => 'Whatever1',
        ]);
    }

    $response->assertStatus(429);
});