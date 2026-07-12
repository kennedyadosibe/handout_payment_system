# Dashboard Student Search

## Purpose

Adds student-name search to the dashboard paid handout lists while keeping students grouped under the handout they paid for.

## User Flow

Admins enter a student name in the `Search student name` box on the dashboard. Matching paid students remain grouped under their handout sections. The revenue cards remain unfiltered so each handout total stays complete.

## Files Changed

- `admin/dashboard.php`

## Testing Notes

The search uses a prepared `LIKE` query against `students.full_name` and only affects the paid-student grouped list.

Official documentation checked:

- https://www.php.net/manual/en/pdo.prepare.php
- https://dev.mysql.com/doc/refman/8.4/en/string-comparison-functions.html
