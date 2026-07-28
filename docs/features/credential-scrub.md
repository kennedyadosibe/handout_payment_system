# Credential Scrub

## Purpose

Remove real admin email/password combinations from setup instructions, seed data, and user-facing login copy. Seeded accounts now use placeholder `.test` email addresses and placeholder passwords that must be changed after local setup.

## Files Changed

- `README.md`
- `admin/login.php`
- `setup.php`
- `docs/features/campus-scope-foundation.md`
- `docs/features/course-rep-password-reset.md`
- `progress/PROGRESS.md`

## Setup Notes

Local setup now seeds placeholder accounts:

- Super admin placeholder: `super.user@example.test`
- Course rep placeholder: `course.rep@example.test`

The placeholder passwords are only for local setup and must be changed immediately. Do not commit real admin credentials.

## Security Notes

The exposed local passwords were rotated in the current local database to the new placeholder values. The next step is rewriting Git history and force-pushing the cleaned branches so the old strings are removed from previous commits on GitHub.

## Official Documentation Checked

- GitHub removing sensitive data documentation: https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/removing-sensitive-data-from-a-repository

## Testing Notes

- Ran PHP syntax checks for `setup.php` and `admin/login.php`.
- Searched the working tree for the exposed email/password strings.
- Verified the placeholder super admin and course representative logins work in the local database.
