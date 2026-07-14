# Campus Course Creation

## Purpose

Course representatives can now create courses from their dashboard. Courses are created inside the representative's assigned department and class, then automatically attached to that representative so they can publish handouts and set prices for those courses.

## How To Use

1. Log in as a `course_rep`.
2. Open `Manage courses`.
3. Enter a course code and course title.
4. Click `Save`.
5. The new course appears in the course list and becomes available when creating handouts.

## Files Changed

- `admin/dashboard.php`

## Database Notes

Courses are stored in the `courses` table. A course code must be unique within its department and level.

The dashboard saves the course inside the logged-in representative's assigned `department_id` and `level_id`. It also creates an `admin_course_assignments` row for the representative in the same transaction.

## Official Documentation Checked

- MySQL `INSERT` statement documentation: https://dev.mysql.com/doc/refman/8.4/en/insert.html
- PHP PDO transactions documentation: https://www.php.net/manual/en/pdo.transactions.php

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified the course creation form appears in the course representative dashboard.
- Created a temporary course through `Manage courses`.
- Confirmed the temporary course appeared in the representative course list.
- Confirmed super admins do not see course creation controls.
- Removed the temporary course and assignment from the database.
