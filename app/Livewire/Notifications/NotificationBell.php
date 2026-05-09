<?php

declare(strict_types=1);

namespace App\Livewire\Notifications;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class NotificationBell extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    public function refreshCount(): void
    {
        $this->unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0;
    }

    public function render(): View
    {
        return view('livewire.notifications.notification-bell', [
            'notifications' => auth()->user()?->notifications()->take(10)->get() ?? collect(),
        ]);
    }
}
