<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceDownAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param array<string, mixed> $alert
     */
    public function __construct(public array $alert)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[ALERT] Service DOWN - '.$this->alert['service_name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.service-down-alert',
        );
    }
}
