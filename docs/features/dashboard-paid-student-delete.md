# Dashboard Paid Student Delete

## Purpose

Allows admins to delete a paid student directly from the handout section on the dashboard after giving the physical handout.

## User Flow

Admins open the dashboard, find the student under the handout they paid for, and click `Delete`. The system removes the payment record and order record, then writes an audit log entry.

## Files Changed

- `admin/dashboard.php`

## Testing Notes

The delete action only accepts paid order records. It runs inside a transaction so the payment delete, order delete, and audit log are handled together.

Official documentation checked:

- https://www.php.net/manual/en/pdo.prepare.php
- https://www.php.net/manual/en/pdo.transactions.php
