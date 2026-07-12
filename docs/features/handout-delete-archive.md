# Handout Delete

## Purpose

Allows admins to remove a handout when a class changes. Dashboard deletion now removes the handout completely with its related orders and payment records.

## User Flow

Admins open `Manage handouts` from the dashboard. Every handout row shows `Edit` and `Delete` beside each other. When Delete is confirmed, the system deletes related payments first, then related orders, then the handout.

## Files Changed

- `admin/handouts/index.php`
- `admin/dashboard.php`

## Testing Notes

The feature uses prepared statements and transactions for admin actions and records delete actions in `audit_logs`. PHP syntax checks should be run after editing.

Official PHP PDO documentation was checked for prepared statements and transaction behavior:

- https://www.php.net/manual/en/pdo.prepare.php
- https://www.php.net/manual/en/pdo.transactions.php
