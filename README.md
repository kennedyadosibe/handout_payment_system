# Student Handout Payment and Ordering System

PHP/MySQL MVP for managing physical handout orders, test payments, and collection tracking.

## Local Setup

1. Start Apache and MySQL in XAMPP.
2. Check database credentials in `config/database.php`.
3. Open `http://localhost/Handout%20Payment%20System/setup.php`.
4. Open `http://localhost/Handout%20Payment%20System/`.

Local accounts after setup:

- Super admin: `super.user@example.test` / `change-me-super-admin`
- Course rep: `course.rep@example.test` / `change-me-course-rep`

## Main Features

- Student handout browsing with prices owned by the backend.
- Order form that stores a price snapshot at order time.
- Test payment screen that simulates gateway success or failure.
- Receipt lookup by order reference.
- Admin login, dashboard, order filters, and collection status updates.
- Super admin campus setup for departments, fixed levels, course representative accounts, and revenue oversight.
- Course representative course creation, handout management, pricing, and course-scoped revenue views.

## Database

The schema is in `database/schema.sql`. If your local MySQL root account uses a password, update `DB_PASS` in `config/database.php` before running setup.
