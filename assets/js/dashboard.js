(function () {
    function activateDashboardPanel(target) {
        var panelName = target || 'overview';
        var panels = Array.from(document.querySelectorAll('[data-dashboard-panel]'));
        var buttons = Array.from(document.querySelectorAll('.dashboard-sidebar [data-dashboard-target]'));
        var matchingPanel = panels.find(function (panel) {
            return panel.dataset.dashboardPanel === panelName;
        });

        if (!matchingPanel) {
            panelName = 'overview';
        }

        panels.forEach(function (panel) {
            panel.hidden = panel.dataset.dashboardPanel !== panelName;
        });

        buttons.forEach(function (button) {
            var isActive = button.dataset.dashboardTarget === panelName;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-current', isActive ? 'page' : 'false');
        });

        var url = new URL(window.location.href);
        url.searchParams.set('panel', panelName);
        window.history.replaceState({}, '', url);
    }

    function filterPaidCourse(courseKey, updateUrl) {
        var selectedCourse = courseKey || 'all';
        var groups = Array.from(document.querySelectorAll('[data-course-group]'));
        var courseButtons = Array.from(document.querySelectorAll('[data-course-target]'));

        groups.forEach(function (group) {
            group.hidden = selectedCourse !== 'all' && group.dataset.courseGroup !== selectedCourse;
        });

        courseButtons.forEach(function (button) {
            var isActive = button.dataset.courseTarget === selectedCourse;
            button.classList.toggle('active', isActive);
            button.setAttribute('aria-current', isActive ? 'true' : 'false');
        });

        if (updateUrl) {
            var url = new URL(window.location.href);
            if (selectedCourse === 'all') {
                url.searchParams.delete('course');
            } else {
                url.searchParams.set('course', selectedCourse);
            }
            url.searchParams.set('panel', 'paid-students');
            window.history.replaceState({}, '', url);
        }
    }

    function updateGroupSearch(group, value) {
        var query = value.trim().toLowerCase();
        var rows = Array.from(group.querySelectorAll('tbody tr'));
        var visibleCount = 0;

        rows.forEach(function (row) {
            var firstCell = row.querySelector('td');
            var studentName = firstCell ? firstCell.textContent.toLowerCase() : '';
            var isVisible = query === '' || studentName.indexOf(query) !== -1;
            row.hidden = !isVisible;
            if (isVisible) {
                visibleCount += 1;
            }
        });

        var count = group.querySelector('[data-handout-search-count]');
        if (count) {
            if (query === '') {
                count.textContent = '';
            } else if (visibleCount === 0) {
                count.textContent = 'No students match in this handout list.';
            } else {
                count.textContent = visibleCount + ' matching student' + (visibleCount === 1 ? '' : 's');
            }
        }
    }

    function filterRepCourseOptions() {
        var departmentSelect = document.querySelector('#rep_department_id');
        var levelSelect = document.querySelector('#rep_level_id');
        var options = Array.from(document.querySelectorAll('[data-rep-course-option]'));
        var message = document.querySelector('[data-rep-course-message]');

        if (!departmentSelect || !levelSelect || !options.length) {
            return;
        }

        var departmentId = departmentSelect.value;
        var levelId = levelSelect.value;
        var shouldFilter = departmentId !== '' && levelId !== '';
        var visibleCount = 0;

        options.forEach(function (option) {
            var matches = !shouldFilter || (option.dataset.departmentId === departmentId && option.dataset.levelId === levelId);
            option.hidden = !matches;
            if (!matches) {
                var checkbox = option.querySelector('input[type="checkbox"]');
                if (checkbox) {
                    checkbox.checked = false;
                }
            } else {
                visibleCount += 1;
            }
        });

        if (message) {
            if (!shouldFilter) {
                message.textContent = 'Select a department and level to narrow the course list.';
            } else if (visibleCount === 0) {
                message.textContent = 'No courses exist for this department and level yet. Add a course first, then create the rep account.';
            } else {
                message.textContent = visibleCount + ' matching course' + (visibleCount === 1 ? '' : 's') + ' available.';
            }
        }
    }

    function filterRevenueCourseOptions() {
        var departmentSelect = document.querySelector('[data-revenue-department]');
        var levelSelect = document.querySelector('[data-revenue-level]');
        var courseSelect = document.querySelector('[data-revenue-course]');

        if (!departmentSelect || !levelSelect || !courseSelect) {
            return;
        }

        var departmentId = departmentSelect.value;
        var levelId = levelSelect.value;
        var selectedOption = courseSelect.options[courseSelect.selectedIndex];
        var selectedStillVisible = !selectedOption || selectedOption.value === '0';

        Array.from(courseSelect.options).forEach(function (option) {
            if (option.value === '0') {
                option.hidden = false;
                return;
            }

            var matchesDepartment = departmentId === '0' || option.dataset.departmentId === departmentId;
            var matchesLevel = levelId === '0' || option.dataset.levelId === levelId;
            var isVisible = matchesDepartment && matchesLevel;
            option.hidden = !isVisible;

            if (option.selected && isVisible) {
                selectedStillVisible = true;
            }
        });

        if (!selectedStillVisible) {
            courseSelect.value = '0';
        }
    }

    document.querySelectorAll('[data-handout-search]').forEach(function (input) {
        var group = input.closest('.paid-group');
        if (!group) {
            return;
        }

        input.addEventListener('input', function () {
            updateGroupSearch(group, input.value);
        });
    });

    ['#rep_department_id', '#rep_level_id'].forEach(function (selector) {
        var select = document.querySelector(selector);
        if (select) {
            select.addEventListener('change', filterRepCourseOptions);
        }
    });

    ['[data-revenue-department]', '[data-revenue-level]'].forEach(function (selector) {
        var select = document.querySelector(selector);
        if (select) {
            select.addEventListener('change', filterRevenueCourseOptions);
        }
    });

    document.querySelectorAll('[data-dashboard-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            activateDashboardPanel(button.dataset.dashboardTarget);
        });
    });

    document.querySelectorAll('[data-course-target]').forEach(function (button) {
        button.addEventListener('click', function () {
            activateDashboardPanel('paid-students');
            filterPaidCourse(button.dataset.courseTarget, true);

            var paidPanel = document.querySelector('[data-dashboard-panel="paid-students"]');
            if (paidPanel) {
                paidPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    var params = new URLSearchParams(window.location.search);
    var currentPanel = params.get('panel');
    var currentCourse = params.get('course') || 'all';
    if (currentCourse !== 'all') {
        currentPanel = 'paid-students';
    }
    if (document.querySelector('[data-dashboard-panel]')) {
        activateDashboardPanel(currentPanel || 'overview');
    }
    if (document.querySelector('[data-course-group]')) {
        filterPaidCourse(currentCourse, currentPanel === 'paid-students' || currentCourse !== 'all');
    }
    filterRepCourseOptions();
    filterRevenueCourseOptions();
}());
