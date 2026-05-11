<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [

        // ── Group events (Prompt 3) ──
        \App\Events\Group\MembershipRequested::class => [
            \App\Listeners\Group\SendMembershipRequestNotification::class,
        ],
        \App\Events\Group\MemberApproved::class => [
            \App\Listeners\Group\SendMemberApprovedNotification::class,
        ],
        \App\Events\Group\MemberJoined::class => [
            \App\Listeners\Group\SendMemberJoinedNotification::class,
        ],

        // ── Event module ──
        \App\Events\Event\EventCancelled::class => [
            \App\Listeners\Event\NotifyAttendeesOfCancellation::class,
        ],
        \App\Events\Event\WaitlistPromoted::class => [
            \App\Listeners\Event\NotifyUserOfWaitlistPromotion::class,
        ],

        // ── Feed module (Prompt 5) ──
        \App\Events\Feed\PostCreated::class => [
            // add listeners here when needed
        ],
        \App\Events\Feed\CommentCreated::class => [
            \App\Listeners\Feed\SendCommentNotification::class,
        ],
        \App\Events\Feed\PostReacted::class => [
            \App\Listeners\Feed\SendReactionNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}