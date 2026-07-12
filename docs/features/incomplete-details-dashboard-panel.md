# Incomplete Details Dashboard Panel

## Purpose

Admins can now view students who entered their details but have not completed payment. This helps track students who may think they already paid even though Paystack has not confirmed payment.

## How To Use

1. Open the admin dashboard.
2. Click `Incomplete details` in the sidebar.
3. Review the saved student details, contact information, selected handout, price snapshot, order reference, and saved time.

## Files Changed

- `admin/dashboard.php`

## Database Notes

The panel lists orders where `orders.payment_status = "not_paid"`. These rows are not counted in paid students or revenue.

## Official Documentation Checked

- MySQL `SELECT` statement documentation: https://dev.mysql.com/doc/refman/8.4/en/select.html

## Testing Notes

- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified the dashboard sidebar shows the incomplete details count.
- Verified the panel opens inside the dashboard and displays saved unpaid student details.
