<?php

declare(strict_types=1);

function app_base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/Handout%20Payment%20System';
}

function log_local_mail(string $recipient, string $subject, string $body): void
{
    $runtimeDir = __DIR__ . '/../runtime';
    if (!is_dir($runtimeDir)) {
        mkdir($runtimeDir, 0775, true);
    }

    $entry = '[' . date('Y-m-d H:i:s') . '] To: ' . $recipient . PHP_EOL
        . 'Subject: ' . $subject . PHP_EOL
        . $body . PHP_EOL
        . str_repeat('-', 72) . PHP_EOL;

    file_put_contents($runtimeDir . '/mail.log', $entry, FILE_APPEND | LOCK_EX);
}

function send_app_email(string $recipient, string $subject, string $body): bool
{
    $headers = [
        'From: HandoutPay <no-reply@localhost>',
        'Content-Type: text/plain; charset=UTF-8',
    ];

    $sent = false;
    if (function_exists('mail')) {
        $sent = @mail($recipient, $subject, $body, implode("\r\n", $headers));
    }

    log_local_mail($recipient, $subject . ($sent ? '' : ' [local copy]'), $body);
    return $sent;
}
