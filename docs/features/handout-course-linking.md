# Handout Course Linking

## Purpose

Handouts are now attached to official campus course records instead of relying on manually typed course codes. This makes each handout belong to a department, level, and course, which prepares the system for course representatives to manage only their assigned course handouts.

## How To Use

1. Log in as an admin.
2. Open `Manage handouts`.
3. Click `Add handout` or edit an existing handout.
4. Select the official `Campus course`.
5. Enter the handout title, price, status, and description.
6. Save the handout.

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

Course representatives only receive course options from `admin_course_assignments`. Their handout lists, paid lists, revenue cards, incomplete details, and collection actions are scoped to those assigned courses.

## Official Documentation Checked

- MySQL `JOIN` clause documentation: https://dev.mysql.com/doc/refman/8.4/en/join.html
- PHP PDO prepared statements documentation: https://www.php.net/manual/en/pdo.prepare.php

## Testing Notes

- Run PHP syntax checks for `app/auth.php`, `admin/dashboard.php`, `admin/handouts/edit.php`, `admin/handouts/index.php`, and `admin/orders/index.php`.
- Create a temporary handout from the dashboard using a selected campus course.
- Confirm the handout stores the selected `department_id`, `level_id`, `course_id`, and `course_code`.
- Remove the temporary handout after testing.
- Create a temporary course representative assigned to one course.
- Confirm the representative sees only that course in the handout editor.
- Remove the temporary representative after testing.
