<?php

namespace App\Mail;

use App\Models\Participant;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Participant $participant,
        public Transaction $transaction,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftaran Diterima - ' . $this->participant->event->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-received',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
