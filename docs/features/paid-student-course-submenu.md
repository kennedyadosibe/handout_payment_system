# Paid Student Course Submenu

## Purpose

The course rep dashboard shows a course submenu under `Paid students` so reps can open one paid handout list directly instead of scrolling through every course.

## User Flow

1. Course rep opens the dashboard.
2. Course rep clicks a course under `Paid students` in the sidebar.
3. The dashboard opens the paid-students panel.
4. Only the selected course handout list remains visible for editing, deleting, marking as given, or searching.
5. Course rep can click `All paid lists` to show every paid handout group again.

Super admins do not see the `Paid students` panel or submenu.

## Changed Files

- `admin/dashboard.php`
- `assets/js/dashboard.js`
- `assets/css/styles.css`

## Official Documentation Checked

- MDN `data-*` attributes documentation: https://developer.mozilla.org/en-US/docs/Web/HTML/How_to/Use_data_attributes
- MDN `scrollIntoView()` documentation: https://developer.mozilla.org/en-US/docs/Web/API/Element/scrollIntoView

## Testing Notes

- Ran PHP syntax checks for the dashboard.
- Verified the paid-students sidebar submenu appears with course buttons.
- Verified clicking a course shows only that paid handout group.
- Verified `All paid lists` restores every paid handout group.
- Verified super admins do not see the submenu.
