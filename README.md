# Student Handout Payment and Ordering System

PHP/MySQL MVP for managing physical handout orders, test payments, and collection tracking.

## Local Setup

1. Start Apache and MySQL in XAMPP.
2. Check database credentials in `config/database.php`.
3. Open `http://localhost/Handout%20Payment%20System/setup.php`.
4. Open `http://localhost/Handout%20Payment%20System/`.

Local accounts after setup:

- Super admin placeholder: `super.user@example.test` / `change-me-super-admin`
- Course rep placeholder: `course.rep@example.test` / `change-me-course-rep`

Change these placeholder credentials immediately after local setup. Do not commit real production admin emails or passwords.

## Main Features

- Student handout browsing with prices owned by the backend.
- Order form that stores a price snapshot at order time.
- Test payment screen that simulates gateway success or failure.
- Receipt lookup by order reference.
- Admin login, dashboard, order filters, and collection status updates.
- Super admin campus setup for departments, fixed levels, course representative accounts, and revenue oversight.
- Course representative course creation, handout management, pricing, and course-scoped revenue views.
- Course representative password reset with an emailed link plus verification code.

## Email Testing

Password reset emails use PHP `mail()` when available. During local XAMPP testing, reset email content is also written to `runtime/mail.log`, which is ignored by Git.

## Database

The schema is in `database/schema.sql`. If your local MySQL root account uses a password, update `DB_PASS` in `config/database.php` before running setup.
