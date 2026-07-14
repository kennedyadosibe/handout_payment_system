# Incomplete Details Dashboard Panel

## Purpose

Course reps can view students who entered their details but have not completed payment. This helps track students who may think they already paid even though Paystack has not confirmed payment.

## How To Use

1. Log in as a course rep and open the dashboard.
2. Click `Incomplete details` in the sidebar.
3. Review the saved student details, contact information, selected handout, price snapshot, order reference, and saved time.

Super admins do not see this panel.

## Files Changed

- `admin/dashboard.php`

## Database Notes

The panel lists orders where `orders.payment_status = "not_paid"`. These rows are not counted in paid students or revenue.

## Official Documentation Checked

- MySQL `SELECT` statement documentation: https://dev.mysql.com/doc/refman/8.4/en/select.html

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified the course rep dashboard sidebar shows the incomplete details count.
- Verified the panel opens inside the dashboard and displays saved unpaid student details.
- Verified super admins do not see the incomplete details panel.
