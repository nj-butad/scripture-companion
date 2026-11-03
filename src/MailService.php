<?php

declare(strict_types=1);

namespace ScriptureCompanion;

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

final class MailService
{
    public static function inbox(): string
    {
        return Config::get('CONTACT_EMAIL', 'info@kristohanon.com');
    }

    public static function sendToInbox(string $subject, string $body, string $replyTo = ''): bool
    {
        return self::send(self::inbox(), $subject, $body, $replyTo);
    }

    public static function send(string $recipient, string $subject, string $body, string $replyTo = ''): bool
    {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $safeSubject = trim(str_replace(["\r", "\n"], ' ', $subject));

        if (class_exists(PHPMailer::class) && Config::get('SMTP_HOST') !== '') {
            return self::sendWithSmtp($recipient, $safeSubject, $body, $replyTo);
        }

        // Keep the site usable before Composer/SMTP is configured on a new host.
        // Production should use authenticated SMTP; PHP mail is only a fallback.
        $headers = [
            'From: Kristohanon <' . self::inbox() . '>',
            'Content-Type: text/plain; charset=UTF-8',
            'MIME-Version: 1.0',
            'X-Mailer: Kristohanon',
        ];

        if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . $replyTo;
        }

        return mail($recipient, $safeSubject, $body, implode("\r\n", $headers));
    }

    private static function sendWithSmtp(string $recipient, string $subject, string $body, string $replyTo): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = Config::get('SMTP_HOST');
            $mail->Port = (int) Config::get('SMTP_PORT', '465');
            $mail->SMTPAuth = true;
            $mail->Username = Config::get('SMTP_USERNAME', self::inbox());
            $mail->Password = Config::get('SMTP_PASSWORD');
            $encryption = strtolower(Config::get('SMTP_ENCRYPTION', 'ssl'));
            $mail->SMTPSecure = $encryption === 'tls'
                ? PHPMailer::ENCRYPTION_STARTTLS
                : PHPMailer::ENCRYPTION_SMTPS;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->Timeout = 15;

            $from = Config::get('SMTP_FROM_EMAIL', self::inbox());
            $mail->setFrom($from, Config::get('SMTP_FROM_NAME', 'Kristohanon'));
            $mail->addAddress($recipient);
            if (filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($replyTo);
            }

            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->AltBody = $body;
            return $mail->send();
        } catch (MailException) {
            error_log('Kristohanon SMTP delivery failed.');
            return false;
        }
    }
}
