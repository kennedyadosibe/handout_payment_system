# Super Admin Course Revenue

## Purpose

Super admins can verify revenue by department, class, and course without managing the course records themselves. This helps confirm the amount each course representative should report for handouts sold through the system.

## How To Use

1. Log in as a `super_admin`.
2. Open the dashboard.
3. Click `Revenue`.
4. Optionally filter by department, level, and course.
5. Review the `Revenue by course` table and filtered total.
6. Compare totals by department, class, course, handout count, paid-student count, and total revenue.

## Files Changed

- `admin/dashboard.php`
- `assets/js/dashboard.js`

## Database Notes

The revenue table reads paid orders only:

- `orders.payment_status = "paid"`
- `orders.price_snapshot` is summed so historical revenue is not changed by later handout price edits.
- `handouts.department_id`, `handouts.level_id`, and `handouts.course_id` provide the campus grouping.

The filter submits these optional query parameters:

- `revenue_department_id`
- `revenue_level_id`
- `revenue_course_id`

The course dropdown is narrowed in the browser after a department or level is selected.

## Official Documentation Checked

- MySQL `GROUP BY` handling documentation: https://dev.mysql.com/doc/refman/8.4/en/group-by-handling.html
- PHP PDO transactions documentation: https://www.php.net/manual/en/pdo.transactions.php

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Confirm super admins see `Revenue by course`.
- Confirm super admins can filter revenue by department, level, and course.
- Confirm the filtered total changes with the selected filters.
- Confirm course representatives still see `Revenue by handout`.
- Confirm super admins do not see course creation controls.
