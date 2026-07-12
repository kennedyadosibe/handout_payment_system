# Paid Handout Filter

## Purpose

Adds filtering and summary totals so admins can see which students have paid for each handout, such as Database Systems or Computer Networking.

## User Flow

Admins open `Orders` from the dashboard. They can filter by handout and payment status, or click a handout card in the `Paid by handout` summary to jump directly to paid orders for that handout.

## Files Changed

- `admin/orders/index.php`

## Testing Notes

The Orders page now uses prepared statement parameters for the handout and payment filters. Paid totals are grouped by handout using SQL `GROUP BY`.

Official documentation checked:

- https://www.php.net/manual/en/pdo.prepare.php
- https://dev.mysql.com/doc/refman/8.4/en/group-by-handling.html
