# Dashboard Admin Panels

## Purpose

`Manage handouts` and `View orders` now open inside the admin dashboard instead of navigating away from it. Course reps use the dashboard workspace for handouts, paid students, incomplete details, and collection work. Super admins use it for campus setup, revenue checks, and read-only order review.

## User Flow

1. Admin opens the dashboard.
2. Course reps click `Manage handouts` in the sidebar to view handouts, edit handouts, delete unused handouts, or archive handouts with orders.
3. Course reps click `Paid students` or `Incomplete details` for student payment follow-up.
4. Admin clicks `View orders` in the sidebar to view paid orders.
5. Course reps can update collection status or delete paid records for their assigned courses.
6. Super admins can review orders only; edit and delete controls are hidden and POST actions are blocked.
7. After dashboard form actions, the admin is returned to the same dashboard panel.

## Changed Files

- `admin/dashboard.php`
- `assets/js/dashboard.js`

## Official Documentation Checked

- MDN `<button>` documentation: https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/button
- MDN hidden input documentation: https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/hidden

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified `Manage handouts` opens as a dashboard panel.
- Verified `View orders` opens as a dashboard panel.
- Verified only the selected dashboard panel is visible after each sidebar click.
- Verified super admins see `View orders` as read-only.
- Verified `Paid students` and `Incomplete details` are course-rep dashboard panels, not super-admin panels.
