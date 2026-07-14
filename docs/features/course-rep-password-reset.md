# Course Rep Password Reset

## Purpose

Course representatives can recover access if they forget their password. Because reps manage handout payments and collection records, the reset requires two verification pieces:

- A reset link sent to the course rep email.
- A separate 6-digit verification code from the same email.

The reset request response is always generic so the page does not reveal whether an email exists.

## How To Use

1. Open `Admin Login`.
2. Click `Forgot password?`.
3. Enter the course representative email address.
4. Open the reset email.
5. Follow the reset link.
6. Enter the 6-digit verification code.
7. Set a new password with at least 8 characters.
8. Log in with the new password.

## Files Changed

- `admin/forgot-password.php`
- `admin/reset-password.php`
- `admin/login.php`
- `app/auth.php`
- `app/bootstrap.php`
- `app/mailer.php`
- `database/schema.sql`
- `setup.php`
- `.gitignore`

## Database Notes

Reset records are stored in `admin_password_resets`. The table stores the reset token hash, verification code hash, expiry time, used time, and failed attempt count. Raw reset tokens and raw codes are not stored in the database.

Reset links and codes expire after 30 minutes. Failed code checks are limited to 5 attempts per reset record. Successful resets mark all open reset records for that representative as used.

## Email Notes

The app tries to send the reset email with PHP `mail()`. For local XAMPP testing, the same reset email is also written to `runtime/mail.log`. The `runtime/` folder is ignored by Git because it can contain reset links and verification codes.

Before production use, configure a real mail provider or SMTP delivery path and protect access to server logs.

## Official Documentation Checked

- PHP `random_bytes()` documentation: https://www.php.net/manual/en/function.random-bytes.php
- PHP `password_hash()` documentation: https://www.php.net/manual/en/function.password-hash.php
- PHP `mail()` documentation: https://www.php.net/manual/en/function.mail.php

## Testing Notes

- Run PHP syntax checks for the changed auth, mailer, setup, login, forgot-password, and reset-password files.
- Request a reset for `course.rep@example.test`.
- Confirm the page gives a generic success message.
- Confirm `runtime/mail.log` contains the reset link and verification code in local testing.
- Confirm an invalid code does not reset the password.
- Confirm the correct link and code allow a new password.
- Confirm the course rep can log in with the new password.
