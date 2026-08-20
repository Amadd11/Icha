<?php

namespace App\Mail\Transports;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;

class ResendTransport extends AbstractTransport
{
    public function __construct(protected string $apiKey)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $fromList = $email->getFrom();
        $from = !empty($fromList) ? $fromList[0] : null;
        $fromAddress = config('mail.from.address', 'onboarding@resend.dev');
        $fromName = config('mail.from.name', 'ICHA Conference Committee');

        // Resend sandbox requires onboarding@resend.dev unless custom domain is verified
        if (str_contains($fromAddress, 'onboarding@resend.dev')) {
            $fromFormatted = "{$fromName} <onboarding@resend.dev>";
        } else {
            $fromFormatted = $from ? ($from->getName() ? "{$from->getName()} <{$from->getAddress()}>" : $from->getAddress()) : "{$fromName} <{$fromAddress}>";
        }

        $to = array_map(fn(Address $addr) => $addr->getAddress(), $email->getTo());
        $replyTo = array_map(fn(Address $addr) => $addr->getAddress(), $email->getReplyTo());
        $subject = $email->getSubject();

        // In Resend Sandbox (onboarding@resend.dev), Resend only allows sending to the registered account email.
        // If testing with dummy participant emails, redirect safely to conference.icha10@gmail.com so test never fails.
        if (str_contains($fromAddress, 'onboarding@resend.dev')) {
            $isSendingToOwner = in_array('conference.icha10@gmail.com', $to);
            if (!$isSendingToOwner) {
                $origTo = implode(', ', $to);
                $to = ['conference.icha10@gmail.com'];
                $subject = "[Sandbox Dev to: {$origTo}] " . $subject;
            }
        }

        $payload = [
            'from'    => $fromFormatted,
            'to'      => $to,
            'subject' => $subject,
            'html'    => $email->getHtmlBody() ?? $email->getTextBody(),
        ];

        if (!empty($replyTo)) {
            $payload['reply_to'] = $replyTo;
        }

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error || $httpCode >= 400) {
            throw new \RuntimeException("Resend API Error (HTTP {$httpCode}): " . ($error ?: $response));
        }
    }

    public function __toString(): string
    {
        return 'resend';
    }
}
