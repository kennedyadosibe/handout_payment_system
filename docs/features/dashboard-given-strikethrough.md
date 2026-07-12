# Dashboard Given Strikethrough

## Purpose

Allows admins to mark a paid student as having received the physical handout without deleting the payment/order record or reducing revenue.

## User Flow

Admins open the dashboard and click `Given` beside a paid student. The row remains under that handout section, but it is struck through to show the handout has been given.

`Delete` still removes the order and payment record. `Given` should be used when the admin wants to keep revenue and history.

## Files Changed

- `admin/dashboard.php`
- `assets/css/styles.css`

## Testing Notes

The `Given` action sets `orders.collection_status` to `collected`. Revenue remains unchanged because the order stays in the database.

Official documentation checked:

- https://www.php.net/manual/en/pdo.prepare.php
- https://www.php.net/manual/en/pdo.transactions.php
- https://developer.mozilla.org/en-US/docs/Web/CSS/text-decoration-line
