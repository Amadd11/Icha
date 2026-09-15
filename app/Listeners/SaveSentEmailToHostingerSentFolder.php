<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

class SaveSentEmailToHostingerSentFolder
{
    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        try {
            $mailer = config('mail.default');
            if ($mailer !== 'smtp') {
                return;
            }

            $host = (string) config('mail.mailers.smtp.host');
            $user = (string) config('mail.mailers.smtp.username');
            $pass = (string) config('mail.mailers.smtp.password');

            // Only proceed if Hostinger SMTP credentials are configured
            if (!str_contains($host, 'hostinger.com') || empty($user) || empty($pass)) {
                return;
            }

            $rawMsg = $event->sent->toString();
            if (empty($rawMsg)) {
                return;
            }

            $this->appendToHostingerSent($user, $pass, $rawMsg);
        } catch (\Throwable $e) {
            // Fail safely: never disrupt participant registration or email delivery
            Log::warning('Failed saving email to Hostinger Sent folder: ' . $e->getMessage());
        }
    }

    /**
     * Append the raw RFC822 message to Hostinger's INBOX.Sent folder via IMAP over SSL.
     */
    protected function appendToHostingerSent(string $user, string $pass, string $rawMsg): void
    {
        $fp = @fsockopen('ssl://imap.hostinger.com', 993, $errno, $errstr, 5);
        if (!$fp) {
            Log::warning("Hostinger IMAP connection failed: {$errstr} ({$errno})");
            return;
        }

        stream_set_timeout($fp, 5);

        // 1. Read greeting banner
        fgets($fp);

        // 2. Authenticate
        fputs($fp, "A1 LOGIN {$user} {$pass}\r\n");
        $loginRes = fgets($fp);
        if (!str_contains((string) $loginRes, 'A1 OK')) {
            fclose($fp);
            return;
        }

        // 3. Append to INBOX.Sent with \Seen flag
        $len = strlen($rawMsg);
        fputs($fp, "A2 APPEND \"INBOX.Sent\" (\\Seen) {{$len}}\r\n");
        $appendRes = fgets($fp);

        if (str_starts_with((string) $appendRes, '+')) {
            fputs($fp, $rawMsg . "\r\n");
            fgets($fp); // Consume A2 OK
        }

        // 4. Logout & close
        fputs($fp, "A3 LOGOUT\r\n");
        fclose($fp);
    }
}
