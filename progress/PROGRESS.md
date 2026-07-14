# Progress Log

## 2026-07-12

### Completed

- Built the initial PHP/MySQL MVP for the Student Handout Payment and Ordering System.
- Added student handout browsing, order creation, price snapshots, simulated payment, receipt lookup, admin login, dashboard, handout management, order filtering, and collection status updates.
- Added project agent rules in `AGENTS.md`.
- Added feature documentation structure in `docs/features/`.
- Added progress tracking structure in `progress/`.
- Added a blue visual theme with subtle star spotting across the site.
- Replaced the plain navbar brand text with a logo treatment.
- Added handout delete/archive controls for class changes.
- Added a paid-list delete action for admins after handout collection.
- Added paid-order filtering and paid totals grouped by handout.
- Simplified order payment status to only `paid` or `not_paid`.
- Changed admin order views so the main list shows only paid students while incomplete payment details remain recorded.
- Replaced simulated payment with Paystack transaction initialization and verification.
- Grouped paid students on the admin dashboard by handout.
- Added delete action for paid students directly inside dashboard handout groups.
- Added dashboard `Given` action that strikes through collected students without reducing revenue.
- Added dashboard revenue totals grouped by handout.
- Added dashboard student-name search for paid handout groups.
- Added per-handout student search boxes inside every paid handout list.
- Added sidebar navigation so dashboard sections load one at a time.
- Styled the dashboard sidebar and cards with a stronger blue theme.
- Added course sub-navigation under Paid students for direct handout-list access.
- Moved Manage handouts and View orders into dashboard panels.
- Added an in-dashboard handout edit panel for Manage handouts.
- Added complete handout deletion beside Edit in the dashboard Manage handouts panel.
- Added an Incomplete details dashboard panel for saved unpaid student records.

### Checks Performed

- Ran PHP syntax checks across the project with `C:\xampp\php\php.exe`.
- Confirmed setup page handles database connection failure without a fatal crash.
- Refreshed the local site in the in-app browser and confirmed the blue theme and star spotting are visible.
- Refreshed the local site in the in-app browser and confirmed the navbar logo appears correctly.
- Ran PHP syntax checks for the handout delete/archive admin update.
- Logged into the local admin area and confirmed Manage Handouts shows Delete for unused handouts and Archive for handouts with existing orders.
- Ran PHP syntax checks for the paid-list delete update.
- Created a temporary paid order, confirmed the Orders page showed Delete, used the button to remove it from the paid list, and cleaned up the temporary student record.
- Ran PHP syntax checks for paid handout filtering.
- Created temporary paid orders for Database Systems and Computer Networking, confirmed the paid summary grouped them separately, confirmed the handout filter showed only the selected handout, and cleaned up the temporary records.
- Ran the database migration, confirmed the orders status enum is only `not_paid` and `paid`, and verified the dashboard/orders pages no longer show pending payment.
- Ran PHP syntax checks and verified in the browser that the admin paid list excludes incomplete-payment students and no longer shows a payment status filter or column.
- Ran PHP syntax checks, confirmed Paystack initialization returns a `checkout.paystack.com` URL using temporary fake data, cleaned up the temporary records, and verified the payment page shows `Continue to Paystack` instead of simulator buttons.
- Ran PHP syntax checks, created temporary paid rows for two handouts, verified the dashboard groups students under the correct handout sections, and cleaned up the temporary records.
- Ran PHP syntax checks, created a temporary paid row, verified dashboard Delete buttons are present, executed the dashboard delete handler, confirmed the temporary row was removed, and cleaned up the temporary student record.
- Ran PHP syntax checks, created a temporary paid row, marked it as Given, confirmed revenue stayed unchanged, verified the dashboard row uses strikethrough styling, and cleaned up the temporary record.
- Ran PHP syntax checks, created temporary paid rows for two handouts, confirmed dashboard revenue cards are split by handout and no global revenue card appears in the top stats, then cleaned up the temporary records.
- Ran PHP syntax checks, created temporary paid rows across two handouts, confirmed searching by student name keeps matches under their handout and hides non-matches, confirmed the no-match message, and cleaned up the temporary records.
- Ran PHP syntax checks, created temporary paid rows in one handout section, confirmed each handout list has its own search box, verified local search hides nonmatching rows only inside that section, and cleaned up the temporary records.
- Ran PHP syntax checks and verified the dashboard sidebar can switch between overview, revenue, and paid-student panels.
- Refreshed the admin dashboard in the browser and confirmed the sidebar and dashboard cards use the updated blue styling.
- Ran PHP syntax checks and verified the Paid students course submenu filters directly to one handout list and restores all lists.
- Ran PHP syntax checks and verified Manage handouts and View orders stay inside the dashboard as sidebar panels.
- Ran PHP syntax checks, verified Edit opens inside the dashboard with handout values loaded, and confirmed saving returns to Manage handouts.
- Ran PHP syntax checks, created a temporary handout with a paid order and payment, deleted it from Manage handouts, and confirmed the handout, order, and payment rows were removed.
- Ran PHP syntax checks and verified the Incomplete details dashboard panel shows saved unpaid student details.

### Blockers

- MySQL rejected `root` with an empty password on this machine. Update `config/database.php` with the correct local database credentials before running setup.

### Next Steps

- Configure local MySQL credentials.
- Run `setup.php` from the browser.
- Test the full student order flow and admin workflow.

## 2026-07-13

### Completed

- Added the campus scope database foundation for departments, academic levels, courses, and course representative assignments.
- Set `super.user@example.test` as the local `super_admin` and `course.rep@example.test` as the local `course_rep`.
- Added auth helper functions for super-admin checks.
- Added a password show/hide toggle on the admin login form.
- Added a super-admin Campus setup dashboard panel for departments and levels.
- Added course representative course creation and course listing to the dashboard.
- Added course representative account creation in Campus setup.
- Connected handout creation and management to official campus course records.
- Added student-side department, level, and course filtering for available handouts.
- Added course representative account editing, password reset, assignment updates, and active/inactive status controls.
- Added department and level fields for scoping course representative accounts.
- Restricted handout publishing, pricing, and handout management tools to course representatives.
- Moved course creation from super admin to course representatives.
- Added super admin revenue oversight by department, class, and course.
- Removed level creation from the super admin dashboard because the system uses fixed Level 100, Level 200, Level 300, and Level 400 records.
- Added setup cleanup for unused non-standard level records from older local setup runs.
- Made paid order lists read-only for super admins while keeping order updates and deletes available to course reps.
- Added super admin revenue filters for department, level, and course with a filtered total.
- Changed super admin revenue so a specific course must be selected before revenue totals are shown.
- Moved Paid students and Incomplete details dashboard sections out of the super admin dashboard so they remain course-rep tools only.
- Changed selected-course revenue to start from the course record so a course still displays even when no paid orders exist yet.
- Removed order-list access from the super admin dashboard and redirected direct super admin orders-page access to revenue verification.
- Added a dedicated super admin Course reps panel listing every representative account with edit/password-reset entry points.
- Tightened public handout filtering so selected department/level scopes never fall back to showing other class handouts.
- Tightened the public course dropdown so selected department/level scopes do not show courses from other classes.

### Checks Performed

- Ran PHP syntax checks for `setup.php`, `app/auth.php`, and `admin/login.php`.
- Ran `setup.php` locally and verified the new campus tables and scope columns were created.
- Confirmed `super.user@example.test` logs in as `super_admin`.
- Confirmed `course.rep@example.test` logs in as `course_rep`.
- Ran PHP syntax checks for `admin/login.php`.
- Verified the password toggle changes the field between hidden and visible states.
- Ran PHP syntax checks for `admin/dashboard.php`.
- Verified the Campus setup panel appears for a super admin.
- Created and removed a temporary department through the Campus setup workflow and confirmed fixed level records were available for rep assignment.
- Created and removed a temporary course through the Campus setup workflow.
- Created and removed a temporary course representative account through the Campus setup workflow.
- Created and removed a temporary handout tied to a selected campus course.
- Created and removed a temporary course representative, then confirmed the handout editor only showed the assigned course.
- Filtered the public handout list by Computer Science, Level 200, and H001, then confirmed the order page shows the campus course context.
- Edited a temporary course representative from Campus setup, confirmed department/class/status updates, and tested password reset behavior.
- Confirmed the course representative form shows matching course options for Computer Science Level 200 and explains when no courses exist for another department/level.
- Confirmed super admin no longer sees handout publishing tools and direct handout management pages redirect back to Campus setup.
- Ran PHP syntax checks for `admin/dashboard.php` after moving course creation to course reps.
- Checked MySQL `GROUP BY` handling documentation for the super admin revenue table.
- Ran PHP syntax checks for `admin/dashboard.php` after removing the level creation form and handler.
- Ran PHP syntax checks for `setup.php` after adding fixed-level cleanup.
- Ran PHP syntax checks for `admin/dashboard.php` and `admin/orders/index.php` after removing super admin order-list access.
- Ran PHP syntax checks for `admin/dashboard.php` after adding super admin revenue filters.
- Ran PHP syntax checks for `admin/dashboard.php` after requiring course-level revenue lookup.
- Ran PHP syntax checks for `admin/dashboard.php` after hiding Paid students and Incomplete details from super admins.
- Ran PHP syntax checks for `admin/dashboard.php` after making selected-course revenue resilient to empty paid orders.
- Ran PHP syntax checks for `admin/dashboard.php` and `admin/orders/index.php` after making View orders course-rep only.
- Ran PHP syntax checks for `admin/dashboard.php` after adding the super admin Course reps panel.
- Ran PHP syntax checks for `handouts.php` and JavaScript syntax checks for `assets/js/handouts.js` after tightening public class filtering.
- Browser-tested an empty department/level and confirmed the course dropdown does not show unrelated courses.

### Next Steps

- Add dynamic dependent filtering so course choices narrow automatically after a student selects department and level.
