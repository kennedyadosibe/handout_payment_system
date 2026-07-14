# Super Admin Read-Only Orders

## Purpose

Super admins can review paid orders and collection status without changing order records. Course representatives remain responsible for updating collection status, marking handouts as given, and deleting paid records after issuing handouts.

## How To Use

1. Log in as a `super_admin`.
2. Open `View orders` from the dashboard.
3. Review paid students, contact details, handouts, amounts, and collection status.
4. No `Save`, `Given`, or `Delete` order controls are shown for super admins.

Course reps still see the editable controls for orders in their assigned courses. `Paid students` and `Incomplete details` belong to the course rep dashboard, not the super admin dashboard.

## Files Changed

- `admin/dashboard.php`
- `admin/orders/index.php`

## Security Notes

The restriction is enforced in both the UI and POST handlers. A direct super admin POST to update collection status, mark a student as given, or delete a paid order is rejected.

## Testing Notes

- Run PHP syntax checks for `admin/dashboard.php` and `admin/orders/index.php`.
- Confirm super admins can open `View orders` without editable order controls.
- Confirm super admins do not see `Paid students` or `Incomplete details` dashboard sections.
- Confirm course reps still see collection update, `Given`, and `Delete` controls for their assigned course orders.
