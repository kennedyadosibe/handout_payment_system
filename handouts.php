<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/layout.php';

$pdo = db();
$departments = $pdo->query('SELECT * FROM departments WHERE status = "active" ORDER BY name')->fetchAll();
$levels = $pdo->query('SELECT * FROM academic_levels WHERE status = "active" ORDER BY sort_order, name')->fetchAll();
$courses = $pdo->query('SELECT c.*, d.code AS department_code, l.name AS level_name
    FROM courses c
    JOIN departments d ON d.department_id = c.department_id
    JOIN academic_levels l ON l.level_id = c.level_id
    WHERE c.status = "active"
    ORDER BY d.name, l.sort_order, c.course_code')->fetchAll();

$selectedDepartmentId = (int) ($_GET['department_id'] ?? 0);
$selectedLevelId = (int) ($_GET['level_id'] ?? 0);
$selectedCourseId = (int) ($_GET['course_id'] ?? 0);
$hasClassFilter = $selectedDepartmentId > 0 || $selectedLevelId > 0 || $selectedCourseId > 0;

$sql = 'SELECT h.*, c.title AS campus_course_title, d.name AS department_name, d.code AS department_code, l.name AS level_name
    FROM handouts h
    LEFT JOIN courses c ON c.course_id = h.course_id
    LEFT JOIN departments d ON d.department_id = h.department_id
    LEFT JOIN academic_levels l ON l.level_id = h.level_id
    WHERE h.status = "available"';
$params = [];

if ($selectedDepartmentId > 0) {
    $sql .= ' AND h.department_id = ?';
    $params[] = $selectedDepartmentId;
}
if ($selectedLevelId > 0) {
    $sql .= ' AND h.level_id = ?';
    $params[] = $selectedLevelId;
}
if ($selectedCourseId > 0) {
    $sql .= ' AND h.course_id = ?';
    $params[] = $selectedCourseId;
}

$sql .= ' ORDER BY d.name, l.sort_order, h.course_code, h.title';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$handouts = $stmt->fetchAll();

page_header('Available Handouts', 'handouts');
?>
<main class="container py-5">
    <div class="mb-4">
        <h1 class="h2">Available handouts</h1>
        <p class="text-muted">Choose a handout to create an order. The amount is calculated by the system from the selected handout.</p>
    </div>

    <form class="bg-white border rounded-2 p-4 mb-4" method="get" data-handout-filter-form>
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="department_id">Department</label>
                <select class="form-select" id="department_id" name="department_id" data-handout-department>
                    <option value="">All departments</option>
                    <?php foreach ($departments as $department): ?>
                        <option value="<?= (int) $department['department_id'] ?>" <?= $selectedDepartmentId === (int) $department['department_id'] ? 'selected' : '' ?>>
                            <?= h($department['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="level_id">Level</label>
                <select class="form-select" id="level_id" name="level_id" data-handout-level>
                    <option value="">All levels</option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?= (int) $level['level_id'] ?>" <?= $selectedLevelId === (int) $level['level_id'] ? 'selected' : '' ?>>
                            <?= h($level['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="course_id">Course</label>
                <select class="form-select" id="course_id" name="course_id" data-handout-course>
                    <option value="">All courses</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= (int) $course['course_id'] ?>" data-department-id="<?= (int) $course['department_id'] ?>" data-level-id="<?= (int) $course['level_id'] ?>" <?= $selectedCourseId === (int) $course['course_id'] ? 'selected' : '' ?>>
                            <?= h($course['course_code'] . ' - ' . $course['title'] . ' (' . $course['department_code'] . ', ' . $course['level_name'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text" data-handout-course-message></div>
            </div>
            <div class="col-md-2 d-grid gap-2">
                <button class="btn btn-primary" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="/Handout%20Payment%20System/handouts.php">Clear</a>
            </div>
        </div>
    </form>

    <div class="row g-4">
        <?php foreach ($handouts as $handout): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card handout-card border-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="badge text-bg-secondary"><?= h($handout['course_code']) ?></span>
                            <span class="price-pill"><?= money($handout['current_price']) ?></span>
                        </div>
                        <h2 class="h5 mt-3"><?= h($handout['title']) ?></h2>
                        <div class="text-muted small mb-2">
                            <?= h($handout['department_name'] ?? 'Department not set') ?> | <?= h($handout['level_name'] ?? 'Level not set') ?>
                            <?php if (!empty($handout['campus_course_title'])): ?>
                                <br><?= h($handout['campus_course_title']) ?>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted flex-grow-1"><?= h($handout['description']) ?></p>
                        <a class="btn btn-primary" href="/Handout%20Payment%20System/order.php?handout_id=<?= (int) $handout['handout_id'] ?>">Order handout</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (!$handouts): ?>
        <div class="alert alert-info"><?= $hasClassFilter ? 'No handouts are available for the selected department, level, or course yet.' : 'No handouts are available yet.' ?></div>
    <?php endif; ?>
</main>
<script src="/Handout%20Payment%20System/assets/js/handouts.js?v=20260714"></script>
<?php page_footer(); ?>
