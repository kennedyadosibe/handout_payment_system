# Paid List Only

## Purpose

Keeps incomplete payment details recorded in the database while ensuring the admin list shows only students who have paid.

## User Flow

Students who start an order still have their details saved. If they do not complete payment, they do not appear on the admin paid list.

Admins open `Paid list` from the dashboard to see only paid students. The list can still be filtered by handout, and paid summary cards still group paid students by course handout.

## Files Changed

- `admin/dashboard.php`
- `admin/orders/index.php`

## Testing Notes

The paid list query always filters `orders.payment_status = "paid"`. The payment status column and payment status filter were removed from the admin list UI.

Official documentation checked:

- https://www.php.net/manual/en/pdo.prepare.php
- https://dev.mysql.com/doc/refman/8.4/en/select.html
