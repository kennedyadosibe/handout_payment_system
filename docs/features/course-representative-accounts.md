# Course Representative Accounts

## Purpose

Super admins can now create course representative accounts and assign each rep to the courses they manage. This lets the campus setup move from only defining departments, levels, and courses into assigning real users to those course responsibilities.

## How To Use

1. Log in as a `super_admin`.
2. Open `Campus setup`.
3. In `Add course rep`, enter the representative name, email, and temporary password.
4. Select the representative department and level.
5. Tick one or more courses the representative manages.
6. Click `Save course rep`.

## Files Changed

- `admin/dashboard.php`
- `assets/css/styles.css`

## Database Notes

Course reps are stored in the existing `admins` table with the `course_rep` role. Their course ownership is stored in `admin_course_assignments`.

Passwords are hashed with PHP `password_hash()` before storage. The plain temporary password is not saved.

## Official Documentation Checked

- PHP `password_hash()` documentation: https://www.php.net/manual/en/function.password-hash.php
- PHP PDO prepared statements documentation: https://www.php.net/manual/en/pdo.prepare.php

## Testing Notes

- Run PHP syntax checks for `admin/dashboard.php`.
- Create a temporary course representative from Campus setup.
- Confirm the representative appears in the course rep list with the selected course.
- Remove the temporary representative and assignment records from the database after testing.
