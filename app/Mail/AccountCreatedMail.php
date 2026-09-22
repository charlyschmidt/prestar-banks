<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recibimos tu registro | AERIA Finance'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-created'
        );
    }

    public function attachments(): array
    {
        return [];
    }
}