# Two-State Payment Status

## Purpose

Simplifies order payment status so students are either `paid` or `not_paid`. There is no separate pending payment status.

## User Flow

New orders start as `not_paid`. Successful payment changes the order to `paid`. Failed or incomplete payment keeps the order as `not_paid`.

Admins can filter Orders by `not_paid` or `paid`, and the dashboard shows a `Not paid` count instead of `Pending payment`.

## Files Changed

- `app/helpers.php`
- `admin/dashboard.php`
- `admin/orders/index.php`
- `payment.php`
- `database/schema.sql`
- `setup.php`

## Testing Notes

The setup script migrates older databases by converting `pending_payment`, `payment_failed`, and `cancelled` orders to `not_paid`, then narrows the enum to `not_paid` and `paid`.

Official documentation checked:

- https://dev.mysql.com/doc/refman/8.4/en/alter-table.html
- https://dev.mysql.com/doc/refman/8.4/en/enum.html
- https://www.php.net/manual/en/pdo.prepare.php
