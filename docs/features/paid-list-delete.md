# Paid List Delete

## Purpose

Allows an admin to delete a paid student from the Orders list after the physical handout has been given.

## User Flow

Admins open `Orders` from the dashboard. Paid orders show a `Delete` button. When confirmed, the student's paid order is removed from the list.

## Files Changed

- `admin/orders/index.php`

## Testing Notes

The delete action is limited to paid orders. It deletes the related payment record first, then the order record, and writes an audit log entry.

Official PHP PDO documentation was checked for prepared statements and transaction behavior:

- https://www.php.net/manual/en/pdo.prepare.php
- https://www.php.net/manual/en/pdo.transactions.php
