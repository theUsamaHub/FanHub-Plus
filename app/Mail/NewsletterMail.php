<?php

namespace App\Mail;

use App\Models\Newsletter;
use App\Models\Subscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Newsletter $newsletter,
        public Subscriber $subscriber
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->newsletter->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter',
            with: [
                'newsletter' => $this->newsletter,
                'subscriber' => $this->subscriber,
                'body' => $this->renderBody(),
                'unsubscribeUrl' => $this->subscriber->unsubscribeUrl(),
            ],
        );
    }

    private function renderBody(): string
    {
        return (string) str_replace(
            ['{name}', '{email}', '{unsubscribe_url}'],
            [
                $this->subscriber->name ?: 'there',
                $this->subscriber->email,
                $this->subscriber->unsubscribeUrl(),
            ],
            (string) $this->newsletter->body
        );
    }

    public function attachments(): array
    {
        return [];
    }
}