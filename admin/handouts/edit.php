<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/layout.php';

$admin = require_admin();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$handout = null;

if ($id) {
    $stmt = db()->prepare('SELECT * FROM handouts WHERE handout_id = ?');
    $stmt->execute([$id]);
    $handout = $stmt->fetch();
    if (!$handout) {
        flash('Handout not found.', 'warning');
        redirect('/Handout%20Payment%20System/admin/handouts/index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        trim($_POST['title'] ?? ''),
        trim($_POST['course_code'] ?? ''),
        trim($_POST['description'] ?? ''),
        (float) ($_POST['current_price'] ?? 0),
        $_POST['status'] ?? 'available',
    ];

    if ($data[0] === '' || $data[1] === '' || $data[2] === '' || $data[3] <= 0) {
        flash('Please complete all fields with a valid price.', 'danger');
        redirect('/Handout%20Payment%20System/admin/handouts/edit.php' . ($id ? '?id=' . $id : ''));
    }

    if ($id) {
        $stmt = db()->prepare('UPDATE handouts SET title = ?, course_code = ?, description = ?, current_price = ?, status = ? WHERE handout_id = ?');
        $stmt->execute([...$data, $id]);
        flash('Handout updated.');
    } else {
        $stmt = db()->prepare('INSERT INTO handouts (title, course_code, description, current_price, status, created_by) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([...$data, $admin['admin_id']]);
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
                    <div class="col-md-8">
                        <label class="form-label" for="title">Title</label>
                        <input class="form-control" id="title" name="title" value="<?= h($handout['title'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="course_code">Course code</label>
                        <input class="form-control" id="course_code" name="course_code" value="<?= h($handout['course_code'] ?? '') ?>" required>
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
