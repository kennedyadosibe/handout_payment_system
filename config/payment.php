<?php

declare(strict_types=1);

$localPaymentConfig = __DIR__ . '/payment.local.php';
if (file_exists($localPaymentConfig)) {
    require_once $localPaymentConfig;
}

if (!defined('PAYMENT_PROVIDER')) {
    define('PAYMENT_PROVIDER', 'Paystack');
}

if (!defined('PAYMENT_CURRENCY')) {
    define('PAYMENT_CURRENCY', 'GHS');
}

if (!defined('PAYSTACK_SECRET_KEY')) {
    define('PAYSTACK_SECRET_KEY', getenv('PAYSTACK_SECRET_KEY') ?: '');
}

if (!defined('PAYSTACK_BASE_URL')) {
    define('PAYSTACK_BASE_URL', 'https://api.paystack.co');
}
