<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\ConversationController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\GroupController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\PollController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API (no auth)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function (): void {
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

/*
|--------------------------------------------------------------------------
| Protected API
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('v1')->group(function (): void {

    /* ── Auth ── */
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    /* ── Users ── */
    Route::get('users/me', [AuthController::class, 'me']);
    Route::put('users/me', [\App\Http\Controllers\ProfileController::class, 'update']);
    Route::get('users/me/groups', [UserController::class, 'myGroups']);
    Route::get('users/me/events', [UserController::class, 'myEvents']);
    Route::post('users/me/interests', [UserController::class, 'syncInterests']);
    Route::get('users/discover', [UserController::class, 'discover']);
    Route::get('users/{user}', [UserController::class, 'show']);
    Route::get('interests', [UserController::class, 'interests']);

    /* ── Reports ── */
    Route::post('reports', [ReportController::class, 'store']);

    /* ── Groups ── */
    Route::apiResource('groups', GroupController::class)->names('api.v1.groups');
    Route::post('groups/{group:slug}/join', [GroupController::class, 'join'])->name('api.v1.groups.join');
    Route::delete('groups/{group:slug}/leave', [GroupController::class, 'leave'])->name('api.v1.groups.leave');
    Route::get('groups/{group:slug}/members', [GroupController::class, 'members'])->name('api.v1.groups.members');

    /* ── Events ── */
    Route::apiResource('events', EventController::class)->names('api.v1.events');
    Route::post('events/{event:slug}/cancel', [EventController::class, 'cancel'])->name('api.v1.events.cancel');
    Route::post('events/{event:slug}/rsvp', [EventController::class, 'rsvp'])->name('api.v1.events.rsvp');
    Route::delete('events/{event:slug}/rsvp', [EventController::class, 'cancelRsvp'])->name('api.v1.events.cancelRsvp');
    Route::get('events/{event:slug}/attendees', [EventController::class, 'attendees'])->name('api.v1.events.attendees');

    /* ── Feed ── */
    Route::get('feed', [PostController::class, 'index']);

    /* ── Posts ── */
    Route::apiResource('posts', PostController::class)->except(['index']);
    Route::post('posts/{post}/react', [PostController::class, 'react']);
    Route::delete('posts/{post}/react', [PostController::class, 'unreact']);
    Route::post('posts/{post}/pin', [PostController::class, 'pin']);
    Route::post('posts/{post}/share', [PostController::class, 'share']);

    /* ── Comments ── */
    Route::get('posts/{post}/comments', [CommentController::class, 'index']);
    Route::post('posts/{post}/comments', [CommentController::class, 'store']);
    Route::delete('comments/{comment}', [CommentController::class, 'destroy']);

    /* ── Polls ── */
    Route::post('polls/{poll}/vote', [PollController::class, 'vote']);

    /* ── Conversations ── */
    Route::get('conversations', [ConversationController::class, 'index']);
    Route::post('conversations', [ConversationController::class, 'store']);
    Route::get('conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('conversations/{conversation}/read', [ConversationController::class, 'markRead']);

    /* ── Messages ── */
    Route::get('conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::put('messages/{message}', [MessageController::class, 'update']);
    Route::delete('messages/{message}', [MessageController::class, 'destroy']);

    /* ── Notifications ── */
    Route::get('notifications', function () {
        return response()->json(auth()->user()->notifications()->paginate(20));
    });
    Route::post('notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All read.']);
    });
    Route::put('notifications/{notification}/read', function ($id) {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();
        return response()->json(['message' => 'Read.']);
    });
});
