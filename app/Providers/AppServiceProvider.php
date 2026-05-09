<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Comment;
use App\Models\Event;
use App\Models\Group;
use App\Models\Post;
use App\Models\User;
use App\Observers\CommentObserver;
use App\Observers\EventObserver;
use App\Observers\GroupObserver;
use App\Observers\PostObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');

        // Register observers
        if (class_exists(Group::class)) {
            Group::observe(GroupObserver::class);
        }

        Event::observe(EventObserver::class);
        Post::observe(PostObserver::class);
        Comment::observe(CommentObserver::class);

        // Filament admin access gate
        Gate::define('access-filament', function (User $user): bool {
            return $user->hasRole('platform_admin');
        });
    }
}