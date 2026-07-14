# Dashboard Sidebar Navigation

## Purpose

The admin dashboard now uses a sidebar to keep the page clean. Admins can click a section in the sidebar and the matching dashboard data loads into the main workspace without showing every dashboard block at once.

## User Flow

1. Admin opens the dashboard.
2. The Overview panel shows the system counters.
3. Course reps click Revenue to view revenue grouped by handout.
4. Course reps click Paid students to view paid student groups, delete records, mark handouts as given, and search inside each handout list.
5. Super admins do not see Paid students or Incomplete details in their sidebar.

## Changed Files

- `admin/dashboard.php`
- `assets/js/dashboard.js`
- `assets/css/styles.css`

## Official Documentation Checked

- MDN `click` event documentation: https://developer.mozilla.org/en-US/docs/Web/API/Element/click_event
- MDN `hidden` global attribute documentation: https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Global_attributes/hidden

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified sidebar section buttons switch visible dashboard panels.
- Verified the paid-students search keeps the admin on the paid-students panel.
- Verified super admins only see oversight and setup dashboard sections.
