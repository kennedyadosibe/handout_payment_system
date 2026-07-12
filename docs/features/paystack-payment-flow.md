# Paystack Payment Flow

## Purpose

Replaces the prototype payment simulator with Paystack test-mode transaction initialization and server-side verification.

## User Flow

Students submit order details, review the payment summary, and click `Continue to Paystack`. The system initializes a Paystack transaction from the backend, redirects the student to Paystack checkout, and verifies the Paystack callback before marking the order as paid.

## Files Changed

- `config/payment.php`
- `config/payment.local.php`
- `app/paystack.php`
- `payment.php`
- `payment-result.php`
- `.gitignore`

## Testing Notes

The Paystack secret key is stored in `config/payment.local.php`, which is ignored by Git. Do not commit real or test secret keys.

Official documentation checked:

- https://paystack.com/docs/api/
- https://paystack.com/docs/api/transaction/
- https://paystack.com/docs/payments/accept-payments/
- https://paystack.com/docs/payments/verify-payments/
