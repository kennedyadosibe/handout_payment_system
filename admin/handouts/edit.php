<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/layout.php';

$admin = require_admin();
if (is_super_admin($admin)) {
    flash('Course representatives are responsible for adding handouts and setting prices.', 'warning');
    redirect('/Handout%20Payment%20System/admin/dashboard.php?panel=campus-setup');
}

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$handout = null;
$manageableCourses = manageable_courses_for_admin($admin);

if ($id) {
    $stmt = db()->prepare('SELECT * FROM handouts WHERE handout_id = ?');
    $stmt->execute([$id]);
    $handout = $stmt->fetch();
    if (!$handout) {
        flash('Handout not found.', 'warning');
        redirect('/Handout%20Payment%20System/admin/handouts/index.php');
    }
    if (!is_super_admin($admin) && !manageable_course_for_admin($admin, (int) $handout['course_id'])) {
        flash('You can only edit handouts for your assigned courses.', 'warning');
        redirect('/Handout%20Payment%20System/admin/handouts/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $courseId = (int) ($_POST['course_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $currentPrice = (float) ($_POST['current_price'] ?? 0);
    $status = $_POST['status'] ?? 'available';

    if ($title === '' || $description === '' || $currentPrice <= 0 || !in_array($status, ['available', 'unavailable', 'archived'], true)) {
        flash('Please complete all fields with a valid price.', 'danger');
        redirect('/Handout%20Payment%20System/admin/handouts/edit.php' . ($id ? '?id=' . $id : ''));
    }

    $course = manageable_course_for_admin($admin, $courseId);
    if (!$course) {
        flash('Select a valid course you are allowed to manage.', 'danger');
        redirect('/Handout%20Payment%20System/admin/handouts/edit.php' . ($id ? '?id=' . $id : ''));
    }

    if ($id) {
        $stmt = db()->prepare('SELECT * FROM handouts WHERE handout_id = ?');
        $stmt->execute([$id]);
        $existingHandout = $stmt->fetch();
        if (!$existingHandout || (!is_super_admin($admin) && !manageable_course_for_admin($admin, (int) $existingHandout['course_id']))) {
            flash('You can only update handouts for your assigned courses.', 'warning');
            redirect('/Handout%20Payment%20System/admin/handouts/index.php');
        }

        $stmt = db()->prepare('UPDATE handouts
            SET department_id = ?, level_id = ?, course_id = ?, title = ?, course_code = ?, description = ?, current_price = ?, status = ?
            WHERE handout_id = ?');
        $stmt->execute([
            (int) $course['department_id'],
            (int) $course['level_id'],
            (int) $course['course_id'],
            $title,
            $course['course_code'],
            $description,
            $currentPrice,
            $status,
            $id,
        ]);
        flash('Handout updated.');
    } else {
        $stmt = db()->prepare('INSERT INTO handouts
            (department_id, level_id, course_id, title, course_code, description, current_price, status, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            (int) $course['department_id'],
            (int) $course['level_id'],
            (int) $course['course_id'],
            $title,
            $course['course_code'],
            $description,
            $currentPrice,
            $status,
            $admin['admin_id'],
        ]);
        flash('Handout added.');
    }

    redirect('/Handout%20Payment%20System/admin/handouts/index.php');
}

page_header($id ? 'Edit Handout' : 'Add Handout');
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="post" class="bg-white border rounded-2 p-4">
                <input type="hidden" name="id" value="<?= (int) $id ?>">
                <h1 class="h3"><?= $id ? 'Edit handout' : 'Add handout' ?></h1>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="title">Title</label>
                        <input class="form-control" id="title" name="title" value="<?= h($handout['title'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="course_id">Campus course</label>
                        <select class="form-select" id="course_id" name="course_id" required>
                            <option value="">Select campus course</option>
                            <?php foreach ($manageableCourses as $course): ?>
                                <option value="<?= (int) $course['course_id'] ?>" <?= (int) ($handout['course_id'] ?? 0) === (int) $course['course_id'] ? 'selected' : '' ?>>
                                    <?= h($course['course_code'] . ' - ' . $course['title'] . ' (' . $course['department_code'] . ', ' . $course['level_name'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!$manageableCourses): ?>
                            <div class="form-text text-danger">No campus courses are available for this admin yet.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="current_price">Current price</label>
                        <input class="form-control" id="current_price" name="current_price" type="number" step="0.01" min="0" value="<?= h($handout['current_price'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" id="status" name="status">
                            <?php foreach (['available', 'unavailable', 'archived'] as $status): ?>
                                <option value="<?= h($status) ?>" <?= ($handout['status'] ?? 'available') === $status ? 'selected' : '' ?>><?= h(str_replace('_', ' ', $status)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?= h($handout['description'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-4">
                    <button class="btn btn-primary" type="submit">Save handout</button>
                    <a class="btn btn-outline-secondary" href="/Handout%20Payment%20System/admin/handouts/index.php">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</main>
<?php page_footer(); ?>
