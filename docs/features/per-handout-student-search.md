# Per-Handout Student Search

## Purpose

Adds a student-name search box inside every paid handout list on the dashboard.

## User Flow

Admins can search globally across paid handout groups with the dashboard search, or search within one specific handout section using that section's `Search this handout list` box. The per-handout search hides and shows rows only inside that handout section.

## Files Changed

- `admin/dashboard.php`
- `assets/js/dashboard.js`
- `assets/css/styles.css`

## Testing Notes

The per-handout search is client-side and uses scoped DOM selection inside each `.paid-group`.

Official documentation checked:

- https://developer.mozilla.org/en-US/docs/Web/API/Element/querySelectorAll
- https://developer.mozilla.org/en-US/docs/Web/API/HTMLElement/input_event
