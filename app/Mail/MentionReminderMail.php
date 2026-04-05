<?php

namespace App\Mail;

use App\Models\ProjectDiscussion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MentionReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $discussion;
    public $user;

    /**
     * Create a new message instance.
     */
    public function __construct(ProjectDiscussion $discussion, User $user)
    {
        $this->discussion = $discussion;
        $this->user = $user;
    }

    /**
     * Get the message envelope.
     */
    public function Envelope(): Envelope
    {
        return new Envelope(
            subject: 'You were mentioned in a project discussion',
        );
    }

    /**
     * Get the message content definition.
     */
    public function Content(): Content
    {
        return new Content(
            view: 'emails.discussions.mention_reminder',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
