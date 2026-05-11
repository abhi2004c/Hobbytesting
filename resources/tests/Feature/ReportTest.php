<?php

use App\Models\Report;
use App\Models\Post;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->post = Post::factory()->create();
});

it('submits a report via web route', function () {
    $response = $this->actingAs($this->user)->post(route('reports.store'), [
        'reportable_type' => 'post',
        'reportable_id'   => $this->post->id,
        'reason'          => 'spam',
        'description'     => 'This is spam content',
    ]);

    $response->assertRedirect();
    expect(Report::where('reporter_id', $this->user->id)->exists())->toBeTrue();
});

it('prevents duplicate reports on the same content', function () {
    Report::create([
        'reporter_id'     => $this->user->id,
        'reportable_type' => Post::class,
        'reportable_id'   => $this->post->id,
        'reason'          => 'spam',
        'status'          => 'pending',
    ]);

    $response = $this->actingAs($this->user)->post(route('reports.store'), [
        'reportable_type' => 'post',
        'reportable_id'   => $this->post->id,
        'reason'          => 'spam',
    ]);

    $response->assertRedirect();
    expect(Report::where('reporter_id', $this->user->id)->count())->toBe(1);
});

it('rejects report for nonexistent content', function () {
    $response = $this->actingAs($this->user)->post(route('reports.store'), [
        'reportable_type' => 'post',
        'reportable_id'   => 99999,
        'reason'          => 'spam',
    ]);

    $response->assertRedirect();
    expect(Report::count())->toBe(0);
});
