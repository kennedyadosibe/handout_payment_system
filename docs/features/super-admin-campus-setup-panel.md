# Super Admin Campus Setup Panel

## Purpose

Super admins can create the first campus structure from inside the dashboard. This panel includes departments and course representative accounts. Academic levels are fixed as Level 100, Level 200, Level 300, and Level 400. Course creation is handled by course representatives in their own dashboard.

## How To Use

1. Log in as a `super_admin`.
2. Open the dashboard.
3. Click `Campus setup` in the sidebar.
4. Add a department with a name and short code.
5. Add a course representative by entering their account details, department, class, status, and temporary password.
6. The representative logs in later to create courses and publish handouts for that department and class.

## Files Changed

- `admin/dashboard.php`
- `assets/css/styles.css`

## Setup Notes

This feature depends on the campus foundation tables:

- `departments`
- `academic_levels`

Run `setup.php` first if those tables do not exist locally. Setup seeds the four fixed levels: Level 100, Level 200, Level 300, and Level 400. It also removes unused non-standard levels from older local setup runs.

## Official Documentation Checked

- MySQL `INSERT` statement documentation: https://dev.mysql.com/doc/refman/8.4/en/insert.html
- PHP `password_hash()` documentation: https://www.php.net/manual/en/function.password-hash.php

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified the `Campus setup` panel appears for a super admin.
- Added a temporary department through the panel.
- Confirmed the department appeared in the dashboard.
- Removed the temporary test records from the database.
- Confirmed level creation controls are not shown in the super admin Campus setup panel.
- Confirmed setup removes unused non-standard levels while keeping the fixed four levels.
- Confirmed course creation controls are not shown in the super admin Campus setup panel.
- Added a temporary course representative through the panel, confirmed it appeared with the selected department and class, and removed the temporary account from the database.
