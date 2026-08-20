<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Payment $payment;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment->loadMissing(['registration.user.profile', 'registration.conference', 'registration.registrationFee']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $invoiceNumber = $this->payment->registration->invoice_number ?? 'Invoice';

        return new Envelope(
            from: new Address(config('mail.from.address', 'conference.icha10@gmail.com'), config('mail.from.name', 'ICHA Conference Committee')),
            replyTo: [
                new Address('conference.icha10@gmail.com', 'ICHA Conference Committee'),
            ],
            subject: "[ICHA] Action Required: Payment Proof Re-upload Needed (#{$invoiceNumber})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_rejected',
            with: [
                'payment'      => $this->payment,
                'registration' => $this->payment->registration,
                'user'         => $this->payment->registration->user,
                'conference'   => $this->payment->registration->conference,
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
