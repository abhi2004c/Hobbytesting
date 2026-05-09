<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\GroupInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GroupInvitationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly GroupInvitation $invitation,
        public readonly ?string $personalMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You're invited to join {$this->invitation->group->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.group-invitation',
            with: [
                'group'     => $this->invitation->group,
                'inviter'   => $this->invitation->inviter,
                'message'   => $this->personalMessage,
                'acceptUrl' => route('invitations.accept', $this->invitation->token),
            ],
        );
    }
}