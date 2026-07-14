# Handout Course Linking

## Purpose

Handouts are now attached to official campus course records instead of relying on manually typed course codes. This makes each handout belong to a department, level, and course, and makes course representatives responsible for publishing available handouts and setting prices for their assigned courses.

## How To Use

1. Log in as a course representative.
2. Open `Manage courses` and create any missing course for the assigned department and class.
3. Open `Manage handouts`.
4. Click `Add handout` or edit an existing handout.
5. Select the campus course.
6. Enter the handout title, price, status, and description.
7. Save the handout.

The selected course automatically supplies the handout `department_id`, `level_id`, `course_id`, and display `course_code`.

## Files Changed

- `app/auth.php`
- `admin/dashboard.php`
- `admin/handouts/edit.php`
- `admin/handouts/index.php`
- `admin/orders/index.php`

## Database Notes

The feature uses the existing nullable campus scope columns on `handouts`:

- `department_id`
- `level_id`
- `course_id`
- `course_code`

Existing order snapshots are not rewritten when a handout course changes. Paid and unpaid order history keeps the course code and title captured at order time.

Course representatives only receive course options from `admin_course_assignments`. Creating a course automatically assigns it to the logged-in representative. Their handout lists, paid lists, revenue cards, incomplete details, and collection actions are scoped to those assigned courses.

Super admins manage campus setup and course representative accounts. They do not create courses, publish handouts, set handout prices, or alter paid orders.

## Official Documentation Checked

- MySQL `JOIN` clause documentation: https://dev.mysql.com/doc/refman/8.4/en/join.html
- PHP PDO prepared statements documentation: https://www.php.net/manual/en/pdo.prepare.php
- PHP PDO transactions documentation: https://www.php.net/manual/en/pdo.transactions.php

## Testing Notes

- Run PHP syntax checks for `app/auth.php`, `admin/dashboard.php`, `admin/handouts/edit.php`, `admin/handouts/index.php`, and `admin/orders/index.php`.
- Create a temporary course from the course representative dashboard.
- Create a temporary handout from the dashboard using the selected campus course.
- Confirm the handout stores the selected `department_id`, `level_id`, `course_id`, and `course_code`.
- Remove the temporary handout after testing.
- Create a temporary course representative and a temporary course from that representative dashboard.
- Confirm the representative sees only their created or assigned courses in the handout editor.
- Confirm super admins do not see course creation or handout publishing tools and cannot open the older handout management pages directly.
- Remove the temporary representative after testing.
