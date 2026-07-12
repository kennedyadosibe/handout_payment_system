# Dashboard Complete Handout Delete

## Purpose

Admins can now permanently delete a handout directly beside the `Edit` action in the dashboard Manage handouts panel.

## User Flow

1. Admin opens `Manage handouts`.
2. Admin clicks `Delete` beside `Edit`.
3. The browser asks for confirmation.
4. The system deletes related payment records, related order records, and then the handout.
5. Admin remains in the dashboard Manage handouts panel.

## Changed Files

- `admin/dashboard.php`
- `docs/features/handout-delete-archive.md`

## Official Documentation Checked

- MySQL `DELETE` statement documentation: https://dev.mysql.com/doc/refman/8.4/en/delete.html
- MySQL foreign key constraints documentation: https://dev.mysql.com/doc/refman/8.4/en/innodb-foreign-key-constraints.html

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Created a temporary handout with a temporary paid order and payment.
- Deleted it from the dashboard Manage handouts panel.
- Confirmed the handout, order, and payment rows were removed from the database.
