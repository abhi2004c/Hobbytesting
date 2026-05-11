<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Group::class => \App\Policies\GroupPolicy::class,
        \App\Models\Event::class => \App\Policies\EventPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
