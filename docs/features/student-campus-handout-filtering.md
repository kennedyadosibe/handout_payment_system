# Student Campus Handout Filtering

## Purpose

Students can filter available handouts by department, level, and course before ordering. This helps students find only the handouts for their class as the system grows beyond one programme.

## How To Use

1. Open `Browse Handouts`.
2. Select a department, level, course, or any combination of those filters.
3. Click `Filter`.
4. Review the matching handouts and click `Order handout`.
5. Confirm the department, level, and course on the order page before entering student details.

When a department or level is selected, the page only shows handouts inside that selected scope. If no courses exist for that class yet, the page says no courses are currently available and shows no handouts from other departments or levels. If courses exist but no handouts have been published, it shows a handout-specific empty-state message.

## Files Changed

- `handouts.php`
- `assets/js/handouts.js`
- `index.php`
- `order.php`

## Database Notes

The filter uses the existing `handouts.department_id`, `handouts.level_id`, and `handouts.course_id` values. Handouts without campus links can still appear when no filter is selected, but they will not appear under a specific department, level, or course filter.

The course dropdown is narrowed in PHP and in the browser after a department or level is selected. Courses from other departments or levels are not shown for the selected class. If no courses exist, the course dropdown is disabled and displays that no courses are available.

## Official Documentation Checked

- PHP PDO prepared statements documentation: https://www.php.net/manual/en/pdo.prepare.php
- MySQL `JOIN` clause documentation: https://dev.mysql.com/doc/refman/8.4/en/join.html

## Testing Notes

- Run PHP syntax checks for `handouts.php`, `index.php`, and `order.php`.
- Run JavaScript syntax checks for `assets/js/handouts.js`.
- Open the handout listing and confirm department, level, and course controls appear.
- Filter by Computer Science, Level 200, and H001.
- Confirm the visible handouts match the selected course.
- Filter by a department/level combination that has no handouts and confirm no other handouts appear.
- Filter by a department/level combination that has no courses and confirm the main empty state says no courses are currently available.
- Confirm the course dropdown does not show courses from other departments or levels when a class filter is active.
- Open an order page and confirm the selected handout shows the campus course context.
