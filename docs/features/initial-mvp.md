# Initial MVP

## Purpose

Provides the first working version of the Student Handout Payment and Ordering System for XAMPP.

## User Flow

Students can browse available handouts, enter order details, continue to a test payment screen, simulate payment success or failure, and look up their receipt by order reference.

Course representatives can log in, view dashboard totals, add or edit handouts, filter orders, and update collection status for paid orders.

## Files Changed

- `index.php`
- `handouts.php`
- `order.php`
- `payment.php`
- `payment-result.php`
- `receipt.php`
- `admin/`
- `app/`
- `config/`
- `database/schema.sql`
- `assets/css/styles.css`
- `setup.php`

## Testing Notes

PHP syntax checks passed for all PHP files using XAMPP PHP.

The setup page reached MySQL, but the local database rejected the current credentials. Update `config/database.php` with the correct MySQL password before running setup in the browser.
