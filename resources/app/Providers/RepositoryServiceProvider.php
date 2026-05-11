<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Event\Repositories\Contracts\EventRepositoryInterface;
use App\Domain\Event\Repositories\EventRepository;
use App\Domain\Feed\Repositories\Contracts\PostRepositoryInterface;
use App\Domain\Feed\Repositories\PostRepository;
use App\Domain\Group\Repositories\Contracts\GroupRepositoryInterface;
use App\Domain\Group\Repositories\GroupRepository;
use App\Domain\Messaging\Repositories\Contracts\MessageRepositoryInterface;
use App\Domain\Messaging\Repositories\MessageRepository;
use App\Domain\User\Repositories\Contracts\UserRepositoryInterface;
use App\Domain\User\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Map of interface => concrete implementation.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        GroupRepositoryInterface::class   => GroupRepository::class,
        EventRepositoryInterface::class   => EventRepository::class,
        PostRepositoryInterface::class    => PostRepository::class,
        UserRepositoryInterface::class    => UserRepository::class,
        MessageRepositoryInterface::class => MessageRepository::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $interface => $concrete) {
            $this->app->bind($interface, $concrete);
        }
    }

    /**
     * @return array<int, class-string>
     */
    public function provides(): array
    {
        return array_keys($this->bindings);
    }
}