<?php

declare(strict_types=1);

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function money(float|string|int $amount): string
{
    return PAYMENT_CURRENCY . ' ' . number_format((float) $amount, 2);
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function flash(?string $message = null, string $type = 'success'): ?array
{
    if ($message !== null) {
        $_SESSION['flash'] = ['message' => $message, 'type' => $type];
        return null;
    }

    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function status_badge(string $status): string
{
    $classes = [
        'available' => 'success',
        'unavailable' => 'secondary',
        'archived' => 'dark',
        'pending_payment' => 'warning text-dark',
        'paid' => 'success',
        'cancelled' => 'secondary',
        'payment_failed' => 'danger',
        'not_ready' => 'secondary',
        'ready_for_collection' => 'info text-dark',
        'collected' => 'success',
        'initialized' => 'warning text-dark',
        'successful' => 'success',
        'failed' => 'danger',
        'reversed' => 'dark',
    ];
    $class = $classes[$status] ?? 'secondary';
    return '<span class="badge bg-' . $class . '">' . h(str_replace('_', ' ', $status)) . '</span>';
}

function reference_code(string $prefix = 'ORD'): string
{
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(4))) . '-' . date('His');
}

function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }
}
