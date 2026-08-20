<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Registration $registration;

    /**
     * Create a new message instance.
     */
    public function __construct(Registration $registration)
    {
        $this->registration = $registration->loadMissing(['user.profile', 'conference', 'registrationFee']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $invoiceNumber = $this->registration->invoice_number ?? 'Invoice';
        $confTitle = $this->registration->conference?->title ?? 'ICHA';

        return new Envelope(
            from: new Address(config('mail.from.address', 'conference.icha10@gmail.com'), config('mail.from.name', 'ICHA Conference Committee')),
            replyTo: [
                new Address('conference.icha10@gmail.com', 'ICHA Conference Committee'),
            ],
            subject: "[ICHA] Official Conference Registration Invoice #{$invoiceNumber} - {$confTitle}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'registration' => $this->registration,
                'user'         => $this->registration->user,
                'conference'   => $this->registration->conference,
                'fee'          => $this->registration->registrationFee,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
