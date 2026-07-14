# Dashboard Admin Panels

## Purpose

`Manage handouts` and `View orders` now open inside the admin dashboard instead of navigating away from it. This keeps admin work in one dashboard workspace alongside Overview, Revenue, and Paid students.

## User Flow

1. Admin opens the dashboard.
2. Admin clicks `Manage handouts` in the sidebar to view handouts, edit handouts, delete unused handouts, or archive handouts with orders.
3. Admin clicks `View orders` in the sidebar to view paid orders.
4. Course reps can update collection status or delete paid records for their assigned courses.
5. Super admins can review orders only; edit and delete controls are hidden and POST actions are blocked.
6. After dashboard form actions, the admin is returned to the same dashboard panel.

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
