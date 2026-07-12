(function () {
    function activateDashboardPanel(target) {
        var panelName = target || 'overview';
        var panels = Array.from(document.querySelectorAll('[data-dashboard-panel]'));
        var buttons = Array.from(document.querySelectorAll('[data-dashboard-target]'));
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

    var currentPanel = new URLSearchParams(window.location.search).get('panel');
    if (document.querySelector('[data-dashboard-panel]')) {
        activateDashboardPanel(currentPanel || 'overview');
    }
}());
