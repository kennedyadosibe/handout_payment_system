# Super Admin Course Revenue

## Purpose

Super admins can verify revenue by department, class, and course without managing the course records themselves. This helps confirm the amount each course representative should report for handouts sold through the system.

## How To Use

1. Log in as a `super_admin`.
2. Open the dashboard.
3. Click `Revenue`.
4. Review the `Revenue by course` table.
5. Compare totals by department, class, course, handout count, paid-student count, and total revenue.

## Files Changed

- `admin/dashboard.php`

## Database Notes

The revenue table reads paid orders only:

- `orders.payment_status = "paid"`
- `orders.price_snapshot` is summed so historical revenue is not changed by later handout price edits.
- `handouts.department_id`, `handouts.level_id`, and `handouts.course_id` provide the campus grouping.

## Official Documentation Checked

- MySQL `GROUP BY` handling documentation: https://dev.mysql.com/doc/refman/8.4/en/group-by-handling.html
- PHP PDO transactions documentation: https://www.php.net/manual/en/pdo.transactions.php

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Confirm super admins see `Revenue by course`.
- Confirm course representatives still see `Revenue by handout`.
- Confirm super admins do not see course creation controls.
