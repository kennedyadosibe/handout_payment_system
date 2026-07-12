# Dashboard Handout Edit Panel

## Purpose

The `Edit` and `Add handout` actions in Manage handouts now stay inside the dashboard. Admins can update handout details without leaving the dashboard workspace.

## User Flow

1. Admin opens `Manage handouts` in the dashboard.
2. Admin clicks `Edit` on a handout.
3. The dashboard opens the `Edit handout` panel with that handout loaded.
4. Admin saves the form.
5. The dashboard returns to `Manage handouts`.

## Changed Files

- `admin/dashboard.php`

## Official Documentation Checked

- MDN `<form>` documentation: https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/form
- MDN number input documentation: https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/input/number

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified clicking `Edit` opens the dashboard edit panel.
- Verified the selected handout fields load into the edit form.
- Verified saving the form returns to the Manage handouts dashboard panel.
