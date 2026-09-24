<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TrialExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $subscriptionUrl
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu período de prueba finalizó | AERIA Finance'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.trial-expired'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}