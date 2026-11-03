<?php

declare(strict_types=1);

namespace ScriptureCompanion;

final class MailService
{
    public static function inbox(): string
    {
        return Config::get('CONTACT_EMAIL', 'info@kristohanon.com');
    }

    public static function sendToInbox(string $subject, string $body, string $replyTo = ''): bool
    {
        $safeSubject = trim(str_replace(["\r", "\n"], ' ', $subject));
        $headers = [
            'From: Kristohanon <' . self::inbox() . '>',
            'Content-Type: text/plain; charset=UTF-8',
            'MIME-Version: 1.0',
            'X-Mailer: Kristohanon',
        ];

        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        return mail(self::inbox(), $safeSubject, $body, implode("\r\n", $headers));
    }
}
