# Revenue By Handout

## Purpose

Splits dashboard revenue by handout so income is not shown only as one combined total.

## User Flow

Course representatives open the dashboard and see a `Revenue by handout` section. Each card shows the course code, handout title, total revenue for that handout, and the number of paid students.

Super admins see `Revenue by course` instead. That oversight table groups paid order totals by department, class, and course so reps cannot report a different amount without the super admin being able to verify it.

## Files Changed

- `admin/dashboard.php`
- `assets/css/styles.css`

## Testing Notes

The dashboard uses a SQL `GROUP BY` query over paid orders to calculate each handout revenue total separately.

Official documentation checked:

- https://dev.mysql.com/doc/refman/8.4/en/group-by-handling.html
- https://www.php.net/manual/en/pdo.query.php
