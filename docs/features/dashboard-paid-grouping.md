# Dashboard Paid Grouping

## Purpose

Groups paid students on the admin dashboard under the handout they paid for, instead of showing all paid names in one mixed list.

## User Flow

Admins open the dashboard and see `Paid students by handout`. Each handout section shows its course code, handout title, total paid students, total money received, and the students who paid for that handout.

## Files Changed

- `admin/dashboard.php`
- `assets/css/styles.css`

## Testing Notes

The dashboard query loads paid orders ordered by course, handout, and student name, then groups them in PHP for display.

Official documentation checked:

- https://www.php.net/manual/en/pdo.prepare.php
- https://dev.mysql.com/doc/refman/8.4/en/order-by-optimization.html
