<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Bus;
use App\Jobs\SendWelcomeEmailJob;

beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
});

it('registers a user with valid data', function () {
    Bus::fake();

    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Aria Khan',
        'email'                 => 'aria@example.com',
        'password'              => 'Secret123',
        'password_confirmation' => 'Secret123',
        'terms'                 => true,
    ]);

    $response->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure(['data' => ['user' => ['id', 'name', 'email'], 'token']]);

    expect(User::where('email', 'aria@example.com')->exists())->toBeTrue();
    Bus::assertDispatched(SendWelcomeEmailJob::class);
});

it('rejects duplicate emails', function () {
    User::factory()->create(['email' => 'taken@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Test',
        'email'                 => 'taken@example.com',
        'password'              => 'Secret123',
        'password_confirmation' => 'Secret123',
        'terms'                 => true,
    ])->assertStatus(422)->assertJsonValidationErrors(['email']);
});

it('requires a strong password', function () {
    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Test',
        'email'                 => 'a@b.com',
        'password'              => 'weak',
        'password_confirmation' => 'weak',
        'terms'                 => true,
    ])->assertStatus(422)->assertJsonValidationErrors(['password']);
});

it('requires accepted terms', function () {
    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Test',
        'email'                 => 'b@c.com',
        'password'              => 'Secret123',
        'password_confirmation' => 'Secret123',
    ])->assertStatus(422)->assertJsonValidationErrors(['terms']);
});