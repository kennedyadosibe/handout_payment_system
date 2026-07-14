# Campus Scope Foundation

## Purpose

This is the first step toward making the system work for a whole campus instead of one class. The database now has a foundation for departments, academic levels, courses, super admins, and course representative assignments.

## How It Works

- `super.user@example.test` is seeded as the local `super_admin`.
- `course.rep@example.test` is seeded as a local `course_rep`.
- Departments are stored in `departments`.
- Fixed levels are stored in `academic_levels`: Level 100, Level 200, Level 300, and Level 400.
- Courses are stored in `courses`.
- Course representative course ownership will be stored in `admin_course_assignments`.
- Handouts can now be linked to department, level, and course records.

Existing handouts still work because the new campus scope fields are nullable. This lets us migrate step by step without breaking the current student ordering flow.

## Files Changed

- `database/schema.sql`
- `setup.php`
- `app/auth.php`
- `admin/login.php`
- `README.md`

## Setup Notes

Run `setup.php` after pulling this feature. It creates or upgrades the campus foundation tables, seeds the four fixed levels, removes unused non-standard levels, and sets the local super admin and course representative accounts.

## Official Documentation Checked

- MySQL `ALTER TABLE` documentation: https://dev.mysql.com/doc/refman/8.4/en/alter-table.html
- PHP `password_hash()` documentation: https://www.php.net/manual/en/function.password-hash.php

## Testing Notes

- Ran PHP syntax checks for `setup.php`, `app/auth.php`, and `admin/login.php`.
- Ran `setup.php` locally.
- Verified the new campus tables exist.
- Verified `admins` and `handouts` have the new scope columns.
- Verified `super.user@example.test` is a `super_admin`.
- Verified `course.rep@example.test` is a `course_rep`.
