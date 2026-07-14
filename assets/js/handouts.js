(function () {
    function filterCourseOptions() {
        var departmentSelect = document.querySelector('[data-handout-department]');
        var levelSelect = document.querySelector('[data-handout-level]');
        var courseSelect = document.querySelector('[data-handout-course]');
        var message = document.querySelector('[data-handout-course-message]');

        if (!departmentSelect || !levelSelect || !courseSelect) {
            return;
        }

        var departmentId = departmentSelect.value;
        var levelId = levelSelect.value;
        var visibleCount = 0;
        var selectedStillVisible = courseSelect.value === '';

        Array.from(courseSelect.options).forEach(function (option) {
            if (option.value === '') {
                option.hidden = false;
                return;
            }

            var matchesDepartment = departmentId === '' || option.dataset.departmentId === departmentId;
            var matchesLevel = levelId === '' || option.dataset.levelId === levelId;
            var isVisible = matchesDepartment && matchesLevel;
            option.hidden = !isVisible;

            if (isVisible) {
                visibleCount += 1;
            }
            if (option.selected && isVisible) {
                selectedStillVisible = true;
            }
        });

        if (!selectedStillVisible) {
            courseSelect.value = '';
        }

        if (message) {
            if (departmentId === '' && levelId === '') {
                message.textContent = 'Select a department and level to narrow courses.';
            } else if (visibleCount === 0) {
                message.textContent = 'No courses exist for the selected department and level yet.';
            } else {
                message.textContent = visibleCount + ' matching course' + (visibleCount === 1 ? '' : 's') + '.';
            }
        }
    }

    ['[data-handout-department]', '[data-handout-level]'].forEach(function (selector) {
        var select = document.querySelector(selector);
        if (select) {
            select.addEventListener('change', filterCourseOptions);
        }
    });

    filterCourseOptions();
}());
