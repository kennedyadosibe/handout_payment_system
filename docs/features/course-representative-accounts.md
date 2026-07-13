# Course Representative Accounts

## Purpose

Super admins can create and manage course representative accounts, assign each rep to the courses they manage, reset passwords, and activate or deactivate rep access.

## How To Use

1. Log in as a `super_admin`.
2. Open `Campus setup`.
3. In `Add course rep`, enter the representative name, email, and temporary password.
4. Select the representative department and level.
5. Tick one or more matching courses the representative manages.
6. Click `Save course rep`.
7. To update a rep, click `Edit` beside the rep in the course representative list.
8. Update the name, email, department, level, assigned courses, or status.
9. Enter a new password only when the rep password should be reset.
10. Click `Update course rep`.

## Files Changed

- `admin/dashboard.php`
- `assets/css/styles.css`
- `app/helpers.php`

## Database Notes

Course reps are stored in the existing `admins` table with the `course_rep` role. Their course ownership is stored in `admin_course_assignments`.

Passwords are hashed with PHP `password_hash()` before storage. The plain temporary password is not saved. Editing a rep with a blank password field keeps the current password hash unchanged.

The course assignment list narrows after selecting a department and level. If no courses exist for that combination, create the course first before creating the rep account.

## Official Documentation Checked

- PHP `password_hash()` documentation: https://www.php.net/manual/en/function.password-hash.php
- PHP PDO prepared statements documentation: https://www.php.net/manual/en/pdo.prepare.php
- PHP PDO transactions documentation: https://www.php.net/manual/en/pdo.transactions.php

## Testing Notes

- Run PHP syntax checks for `admin/dashboard.php`.
- Create a temporary course representative from Campus setup.
- Confirm the representative appears in the course rep list with the selected course.
- Edit the temporary representative name, email, status, password, and assigned course.
- Confirm inactive representatives cannot log in.
- Confirm resetting the password allows the edited representative to log in with the new password after reactivation.
- Confirm selecting a department and level narrows the assignment list to matching courses.
- Remove the temporary representative and assignment records from the database after testing.
