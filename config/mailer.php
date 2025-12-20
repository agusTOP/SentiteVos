<?php
// Centralized mail manager using PHPMailer and .env
// Provides simple helpers to send to users and owner(s)

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/helpers.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!function_exists('mailer_init')) {
    function mailer_init(): PHPMailer
    {
        // Ensure environment variables are loaded
        loadEnv(dirname(__DIR__));

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST', 'smtp.example.com');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME', 'defaultmail@gmail.com');
        $mail->Password = env('MAIL_PASSWORD', 'pass');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = (int) env('MAIL_PORT', 465);

        $fromEmail = env('MAIL_FROM', $mail->Username);
        $fromName = env('MAIL_FROM_NAME', 'Sentite Vos');
        $mail->setFrom($fromEmail, $fromName);

        return $mail;
    }
}

if (!function_exists('send_mail_simple')) {
    /**
     * Send an email to a primary recipient (and optional extra recipients).
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $htmlBody
     * @param string $altBody
     * @param array $extraRecipients Array of emails or [email, name] pairs
     * @return bool
     */
    function send_mail_simple(string $toEmail, string $toName, string $subject, string $htmlBody, string $altBody, array $extraRecipients = []): bool
    {
        try {
            $mail = mailer_init();
            $mail->addAddress($toEmail, $toName);
            foreach ($extraRecipients as $rcpt) {
                if (is_array($rcpt)) {
                    $mail->addAddress($rcpt[0], $rcpt[1] ?? $rcpt[0]);
                } else {
                    $mail->addAddress((string) $rcpt);
                }
            }
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->AltBody = $altBody;
            return $mail->send();
        } catch (\Throwable $e) {
            log_error('Mailer error: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('notify_owner')) {
    /**
     * Notify the owner (and optional secondary) using OWNER_EMAIL and OWNER_EMAIL_SECONDARY.
     * @param string $subject
     * @param string $htmlBody
     * @param string $altBody
     * @param bool $includeSecondary
     * @return bool
     */
    function notify_owner(string $subject, string $htmlBody, string $altBody, bool $includeSecondary = true): bool
    {
        $primary = env('OWNER_EMAIL', 'sentitevos2018@gmail.com');
        $secondary = $includeSecondary ? env('OWNER_EMAIL_SECONDARY', 'lorena@sentitevos.site') : null;
        try {
            if (!$primary) return false;
            $mail = mailer_init();
            $mail->addAddress($primary, 'Sentite Vos');
            if ($secondary && strcasecmp((string)$secondary, (string)$primary) !== 0) {
                $mail->addAddress($secondary, 'Sentite Vos');
            }
            $mail->Subject = $subject;
            $mail->isHTML(true);
            $mail->Body = $htmlBody;
            $mail->AltBody = $altBody;
            return $mail->send();
        } catch (\Throwable $e) {
            log_error('Owner mail error: ' . $e->getMessage());
            return false;
        }
    }
}
