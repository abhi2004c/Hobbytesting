<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Welcome to HobbyHub, {$this->user->name}! 🎉",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.welcome',
            with: [
                'name'        => $this->user->name,
                'profileUrl'  => route('profile.edit'),
                'discoverUrl' => route('groups.index'),
            ],
        );
    }
}