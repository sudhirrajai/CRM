<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AutomationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public EmailTemplate $template,
        public $enrollable
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template->subject,
        );
    }

    public function build()
    {
        $html = $this->template->html_content ?? '';

        // Replace merge tags based on enrollable type
        $name = 'there';
        $email = '';
        $company = '';

        if ($this->enrollable instanceof \App\Models\Lead) {
            $name = $this->enrollable->contact_name ?? $this->enrollable->title;
            $email = $this->enrollable->contact_email ?? '';
            $company = $this->enrollable->company ?? '';
        } elseif ($this->enrollable instanceof \App\Models\Client) {
            $name = $this->enrollable->name;
            $email = $this->enrollable->email ?? '';
            $company = $this->enrollable->company ?? '';
        } elseif ($this->enrollable instanceof \App\Models\Invoice) {
            $name = $this->enrollable->client->name ?? 'there';
            $email = $this->enrollable->client->email ?? '';
            $company = $this->enrollable->client->company ?? '';
        }

        $html = str_replace('{{name}}', $name, $html);
        $html = str_replace('{{email}}', $email, $html);
        $html = str_replace('{{company}}', $company, $html);

        return $this->html($html);
    }
}
