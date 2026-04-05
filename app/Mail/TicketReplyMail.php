<?php

namespace App\Mail;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $ticket;
    public $message;

    /**
     * Create a new message instance.
     */
    public function __construct(Ticket $ticket, TicketMessage $message)
    {
        $this->ticket = $ticket;
        $this->message = $message;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Reply - ' . $this->ticket->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tickets.reply',
            with: [
                'ticket_id' => $this->ticket->id,
                'subject' => $this->ticket->subject,
                'user_name' => $this->message->user->name,
                'content' => $this->message->message,
                'url' => route('tickets.show', $this->ticket->id),
            ],
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
