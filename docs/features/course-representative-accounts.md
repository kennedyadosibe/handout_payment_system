# Course Representative Accounts

## Purpose

Super admins can create and manage course representative accounts, set each rep's department and class, reset passwords, and activate or deactivate rep access. Course reps create and manage their own courses after logging in.

## How To Use

1. Log in as a `super_admin`.
2. Open `Campus setup`.
3. In `Add course rep`, enter the representative name, email, and temporary password.
4. Select the representative department and level.
5. Click `Save course rep`.
6. Open `Course reps` from the super admin sidebar to see all representative accounts.
7. To update a rep, click `Edit / reset password` beside the rep.
8. Update the name, email, department, level, or status.
9. Enter a new password only when the rep password should be reset.
10. Click `Update course rep`.

## Files Changed

- `admin/dashboard.php`
- `assets/css/styles.css`
- `app/helpers.php`

## Database Notes

Course reps are stored in the existing `admins` table with the `course_rep` role. Their created course ownership is stored in `admin_course_assignments`.

Passwords are hashed with PHP `password_hash()` before storage. The plain temporary password is not saved. Editing a rep with a blank password field keeps the current password hash unchanged.

Super admins no longer select course assignments from the rep form. The representative's department and class define where they can create courses.

## Official Documentation Checked

- PHP `password_hash()` documentation: https://www.php.net/manual/en/function.password-hash.php
- PHP PDO prepared statements documentation: https://www.php.net/manual/en/pdo.prepare.php
- PHP PDO transactions documentation: https://www.php.net/manual/en/pdo.transactions.php

## Testing Notes

- Run PHP syntax checks for `admin/dashboard.php`.
- Create a temporary course representative from Campus setup.
- Confirm the representative appears in the course rep list with the selected department and class.
- Confirm the representative appears in the dedicated `Course reps` super admin panel.
- Edit the temporary representative name, email, department, level, status, and password.
- Confirm inactive representatives cannot log in.
- Confirm resetting the password allows the edited representative to log in with the new password after reactivation.
- Confirm the super admin form does not require an existing course before creating a rep.
- Remove the temporary representative from the database after testing.
