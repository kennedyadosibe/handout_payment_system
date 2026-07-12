(function () {
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
}());
