<?php

declare(strict_types=1);

use App\Http\Controllers\CommentController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Welcome (Guest) / Home (Auth)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('feed.index')
        : view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', fn () => redirect()->route('feed.index'))->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    /* ── Feed ── */
    Route::get('/feed', [PostController::class, 'index'])->name('feed.index');

    /* ── Groups ── */
    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group:slug}', [GroupController::class, 'show'])->name('groups.show');
    Route::get('/groups/{group:slug}/edit', [GroupController::class, 'edit'])->name('groups.edit');
    Route::put('/groups/{group}', [GroupController::class, 'update'])->name('groups.update');
    Route::patch('/groups/{group}', [GroupController::class, 'update']); // alias
    Route::delete('/groups/{group}', [GroupController::class, 'destroy'])->name('groups.destroy');
    Route::post('/groups/{group}/join', [GroupController::class, 'join'])->name('groups.join');
    Route::delete('/groups/{group}/leave', [GroupController::class, 'leave'])->name('groups.leave');

    /* ── Events ── */
    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/groups/{group}/events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('/events', [EventController::class, 'store'])->name('events.store');
    Route::get('/events/{event:slug}', [EventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/events/{event}/rsvp', [EventController::class, 'rsvp'])->name('events.rsvp');
    Route::delete('/events/{event}/rsvp', [EventController::class, 'cancelRsvp'])->name('events.cancelRsvp');
    Route::post('/events/{event}/cancel', [EventController::class, 'cancel'])->name('events.cancel');

    /* ── Posts & Comments ── */
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/pin', [PostController::class, 'pin'])->name('posts.pin');
    Route::post('/posts/{post}/react', [PostController::class, 'react'])->name('posts.react');
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    /* ── Reports ── */
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');

    /* ── Messages ── */
    Route::get('/messages', fn () => view('messages.index'))->name('messages.index');

    /* ── Profile ── */
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile.show');

    /* ── Notifications ── */
    Route::get('/notifications', function () {
        return view('notifications.index', [
            'notifications' => auth()->user()->notifications()->paginate(20),
        ]);
    })->name('notifications.index');
    Route::post('/notifications/read-all', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'All notifications marked as read.');
    })->name('notifications.readAll');

    /* ── Invitations ── */
    Route::get('/invitations/{token}/accept', [\App\Http\Controllers\Auth\InvitationController::class, 'accept'])
        ->name('invitations.accept')->middleware('signed');
    Route::get('/invitations/{token}/decline', [\App\Http\Controllers\Auth\InvitationController::class, 'decline'])
        ->name('invitations.decline');
});

require __DIR__.'/auth.php';