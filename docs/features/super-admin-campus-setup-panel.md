# Super Admin Campus Setup Panel

## Purpose

Super admins can now create the first campus structure from inside the dashboard. This panel includes departments, academic levels, courses, and course representative accounts.

## How To Use

1. Log in as a `super_admin`.
2. Open the dashboard.
3. Click `Campus setup` in the sidebar.
4. Add a department with a name and short code.
5. Add a level with a name and sort order.
6. Add a course by selecting a department and level, then entering the course code and title.
7. Add a course representative by entering their account details and selecting the courses they manage.

## Files Changed

- `admin/dashboard.php`
- `assets/css/styles.css`
- `assets/css/styles.css`

## Setup Notes

This feature depends on the campus foundation tables:

- `departments`
- `academic_levels`

Run `setup.php` first if those tables do not exist locally.

## Official Documentation Checked

- MySQL `INSERT` statement documentation: https://dev.mysql.com/doc/refman/8.4/en/insert.html

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified the `Campus setup` panel appears for a super admin.
- Added a temporary department and level through the panel.
- Confirmed both records appeared in the dashboard.
- Removed the temporary test records from the database.
- Added a temporary course through the panel, confirmed it appeared, and removed it from the database.
- Added a temporary course representative through the panel, confirmed the course assignment appeared, and removed the temporary account from the database.
