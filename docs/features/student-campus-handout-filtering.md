# Student Campus Handout Filtering

## Purpose

Students can filter available handouts by department, level, and course before ordering. This helps students find only the handouts for their class as the system grows beyond one programme.

## How To Use

1. Open `Browse Handouts`.
2. Select a department, level, course, or any combination of those filters.
3. Click `Filter`.
4. Review the matching handouts and click `Order handout`.
5. Confirm the department, level, and course on the order page before entering student details.

The homepage now starts students with a department and level finder instead of previewing recent handouts from across campus. It also uses styled blue step cards to explain the student flow: choose class, order handout, then pay and collect. Before a student filters the handout page, the public handout list shows an instruction to select a department and level instead of showing campus-wide courses or handouts. The handout page uses a blue header, styled filter panel, blue result cards, and a guided empty state so the student flow feels consistent with the homepage. When a department or level is selected, the page only shows handouts inside that selected scope. If no courses exist for that class yet, the page says no courses are currently available and shows no handouts from other departments or levels. If courses exist but no handouts have been published, it shows a handout-specific empty-state message.

## Files Changed

- `handouts.php`
- `app/layout.php`
- `assets/css/styles.css`
- `assets/js/handouts.js`
- `index.php`
- `order.php`

## Database Notes

The homepage class finder submits `department_id` and `level_id` to `handouts.php`. The filter uses the existing `handouts.department_id`, `handouts.level_id`, and `handouts.course_id` values. The handout query only loads results after a student chooses a department, level, or course filter, so handouts from other classes are not exposed on the default page.

The course dropdown is narrowed in PHP and in the browser after a department or level is selected. Courses from other departments or levels are not shown for the selected class. Before filtering, the course dropdown is disabled and tells the student to select a department and level first. If no courses exist, the course dropdown is disabled and displays that no courses are available.

## Official Documentation Checked

- PHP PDO prepared statements documentation: https://www.php.net/manual/en/pdo.prepare.php
- MySQL `JOIN` clause documentation: https://dev.mysql.com/doc/refman/8.4/en/join.html

## Testing Notes

- Run PHP syntax checks for `handouts.php`, `index.php`, and `order.php`.
- Run JavaScript syntax checks for `assets/js/handouts.js`.
- Open the handout listing and confirm department, level, and course controls appear.
- Open the default handout listing and confirm it shows filtering instructions instead of handout cards or all-course options.
- Confirm the handout page uses the styled blue header, filter panel, result cards, and empty state.
- Open the homepage and confirm it shows the department/level finder instead of featured handout cards.
- Confirm the homepage student-flow cards use the styled blue card design.
- Submit the homepage finder and confirm it opens the filtered handout page for that class.
- Filter by Computer Science, Level 200, and H001.
- Confirm the visible handouts match the selected course.
- Filter by a department/level combination that has no handouts and confirm no other handouts appear.
- Filter by a department/level combination that has no courses and confirm the main empty state says no courses are currently available.
- Confirm the course dropdown does not show courses from other departments or levels when a class filter is active.
- Open an order page and confirm the selected handout shows the campus course context.
