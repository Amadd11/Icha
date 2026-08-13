<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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
        $this->payment = $payment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $invoiceNumber = $this->payment->registration->invoice_number ?? 'Invoice';

        return new Envelope(
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
