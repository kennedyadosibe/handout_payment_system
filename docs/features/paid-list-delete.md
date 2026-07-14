# Paid List Delete

## Purpose

Allows a course representative to delete a paid student from the Orders list after the physical handout has been given.

## User Flow

Course reps open `Orders` from the dashboard. Paid orders for their assigned courses show a `Delete` button. When confirmed, the student's paid order is removed from the list. Super admins can view the list but cannot delete paid orders.

## Files Changed

- `admin/orders/index.php`

## Testing Notes

The delete action is limited to paid orders and course reps. It deletes the related payment record first, then the order record, and writes an audit log entry. Super admin POST attempts are rejected.

Official PHP PDO documentation was checked for prepared statements and transaction behavior:

- https://www.php.net/manual/en/pdo.prepare.php
- https://www.php.net/manual/en/pdo.transactions.php
