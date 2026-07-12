# Handout Delete And Archive

## Purpose

Allows admins to remove a handout when a class changes while preserving order history for handouts that already have student orders.

## User Flow

Admins open `Manage handouts` from the dashboard. A handout with no orders shows a `Delete` button. A handout with existing orders shows an `Archive` button instead, because deleting it would break historical order records.

## Files Changed

- `admin/handouts/index.php`

## Testing Notes

The feature uses prepared statements for admin actions and records delete/archive actions in `audit_logs`. PHP syntax checks should be run after editing.

Official PHP PDO documentation was checked for prepared statements and transaction behavior:

- https://www.php.net/manual/en/pdo.prepare.php
- https://www.php.net/manual/en/pdo.transactions.php
