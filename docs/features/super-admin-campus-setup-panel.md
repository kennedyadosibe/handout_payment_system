# Super Admin Campus Setup Panel

## Purpose

Super admins can create the first campus structure from inside the dashboard. This panel includes departments, academic levels, and course representative accounts. Course creation is handled by course representatives in their own dashboard.

## How To Use

1. Log in as a `super_admin`.
2. Open the dashboard.
3. Click `Campus setup` in the sidebar.
4. Add a department with a name and short code.
5. Add a level with a name and sort order.
6. Add a course representative by entering their account details, department, class, status, and temporary password.
7. The representative logs in later to create courses and publish handouts for that department and class.

## Files Changed

- `admin/dashboard.php`
- `assets/css/styles.css`

## Setup Notes

This feature depends on the campus foundation tables:

- `departments`
- `academic_levels`

Run `setup.php` first if those tables do not exist locally.

## Official Documentation Checked

- MySQL `INSERT` statement documentation: https://dev.mysql.com/doc/refman/8.4/en/insert.html
- PHP `password_hash()` documentation: https://www.php.net/manual/en/function.password-hash.php

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified the `Campus setup` panel appears for a super admin.
- Added a temporary department and level through the panel.
- Confirmed both records appeared in the dashboard.
- Removed the temporary test records from the database.
- Confirmed course creation controls are not shown in the super admin Campus setup panel.
- Added a temporary course representative through the panel, confirmed it appeared with the selected department and class, and removed the temporary account from the database.
