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

    document.querySelectorAll('[data-handout-search]').forEach(function (input) {
        var group = input.closest('.paid-group');
        if (!group) {
            return;
        }

        input.addEventListener('input', function () {
            updateGroupSearch(group, input.value);
        });
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
}());
