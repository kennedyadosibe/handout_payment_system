# Super Admin Revenue-Only Oversight

## Purpose

Super admins focus on campus setup, course representative account creation, and revenue verification. Course representatives remain responsible for viewing orders, updating collection status, marking handouts as given, and deleting paid records after issuing handouts.

## How To Use

1. Log in as a `super_admin`.
2. Open `Campus setup` to add departments and create course representative accounts.
3. Open `Revenue`.
4. Select a department, level, and course to verify the course revenue before paying the representative.

Course reps still see `Paid students`, `Incomplete details`, `View orders`, and editable order controls for their assigned courses.

## Files Changed

- `admin/dashboard.php`
- `admin/orders/index.php`

## Security Notes

The super admin dashboard does not render `View orders`, `Paid students`, or `Incomplete details`. Direct super admin access to `/admin/orders/index.php` redirects back to the revenue dashboard.

## Testing Notes

- Run PHP syntax checks for `admin/dashboard.php` and `admin/orders/index.php`.
- Confirm super admins see campus setup and revenue verification only.
- Confirm super admins do not see `View orders`, `Paid students`, or `Incomplete details`.
- Confirm direct super admin access to `/admin/orders/index.php` redirects to `dashboard.php?panel=revenue`.
- Confirm course reps still see order lists and collection update controls.
