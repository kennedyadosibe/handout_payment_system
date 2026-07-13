# Campus Course Creation

## Purpose

Super admins can now create courses inside the Campus setup dashboard panel. Courses connect departments and levels, and they are the next foundation for assigning course representatives and attaching handouts to the correct class scope.

## How To Use

1. Log in as a `super_admin`.
2. Open `Campus setup`.
3. Select a department.
4. Select a level.
5. Enter a course code and course title.
6. Click `Save course`.

## Files Changed

- `admin/dashboard.php`

## Database Notes

Courses are stored in the `courses` table. A course code must be unique within its department and level.

## Official Documentation Checked

- MySQL `INSERT` statement documentation: https://dev.mysql.com/doc/refman/8.4/en/insert.html

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified the course creation form appears in Campus setup.
- Created a temporary course through the dashboard.
- Confirmed the temporary course appeared in the course list.
- Removed the temporary course from the database.
