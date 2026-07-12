<?php

declare(strict_types=1);

function paystack_configured(): bool
{
    return PAYSTACK_SECRET_KEY !== '';
}

function paystack_amount_subunit(float|string|int $amount): int
{
    return (int) round(((float) $amount) * 100);
}

function paystack_callback_url(string $orderReference): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host . '/Handout%20Payment%20System/payment-result.php?order=' . urlencode($orderReference);
}

function paystack_request(string $method, string $path, ?array $payload = null): array
{
    if (!paystack_configured()) {
        throw new RuntimeException('Paystack secret key is not configured.');
    }

    if (!function_exists('curl_init')) {
        throw new RuntimeException('The PHP cURL extension is required for Paystack payments.');
    }

    $curl = curl_init(PAYSTACK_BASE_URL . $path);
    $headers = [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    curl_setopt_array($curl, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($payload !== null) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $raw = curl_exec($curl);
    $curlError = curl_error($curl);
    $statusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
    curl_close($curl);

    if ($raw === false) {
        throw new RuntimeException('Paystack request failed: ' . $curlError);
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Paystack returned an invalid response.');
    }

    if ($statusCode < 200 || $statusCode >= 300 || ($decoded['status'] ?? false) !== true) {
        $message = $decoded['message'] ?? 'Paystack request was not successful.';
        throw new RuntimeException($message);
    }

    return $decoded;
}

function paystack_initialize_transaction(array $order): array
{
    return paystack_request('POST', '/transaction/initialize', [
        'email' => $order['email'],
        'amount' => paystack_amount_subunit($order['price_snapshot']),
        'currency' => PAYMENT_CURRENCY,
        'reference' => $order['payment_reference'],
        'callback_url' => paystack_callback_url($order['order_reference']),
        'metadata' => [
            'order_reference' => $order['order_reference'],
            'student_name' => $order['full_name'],
            'handout' => $order['course_code_snapshot'] . ' - ' . $order['handout_title_snapshot'],
        ],
    ]);
}

function paystack_verify_transaction(string $reference): array
{
    return paystack_request('GET', '/transaction/verify/' . rawurlencode($reference));
}
