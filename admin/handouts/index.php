<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/layout.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $handoutId = (int) ($_POST['handout_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = db()->prepare('SELECT h.*, COUNT(o.order_id) AS order_count
        FROM handouts h
        LEFT JOIN orders o ON o.handout_id = h.handout_id
        WHERE h.handout_id = ?
        GROUP BY h.handout_id');
    $stmt->execute([$handoutId]);
    $handout = $stmt->fetch();

    if (!$handout) {
        flash('Handout not found.', 'warning');
        redirect('/Handout%20Payment%20System/admin/handouts/index.php');
    }
    if (!is_super_admin($admin) && !manageable_course_for_admin($admin, (int) $handout['course_id'])) {
        flash('You can only manage handouts for your assigned courses.', 'warning');
        redirect('/Handout%20Payment%20System/admin/handouts/index.php');
    }

    if ($action === 'delete') {
        if ((int) $handout['order_count'] > 0) {
            flash('This handout already has orders, so it was not deleted. Archive it to remove it from active class use while keeping records.', 'warning');
            redirect('/Handout%20Payment%20System/admin/handouts/index.php');
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE FROM handouts WHERE handout_id = ?');
            $stmt->execute([$handoutId]);

            $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$admin['admin_id'], 'delete handout', 'handouts', (string) $handoutId]);

            $pdo->commit();
            flash('Handout deleted.');
        } catch (Throwable $exception) {
            $pdo->rollBack();
            flash('Handout could not be deleted. Please try again.', 'danger');
        }

        redirect('/Handout%20Payment%20System/admin/handouts/index.php');
    }

    if ($action === 'archive') {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE handouts SET status = "archived" WHERE handout_id = ?');
            $stmt->execute([$handoutId]);

            $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$admin['admin_id'], 'archive handout', 'handouts', (string) $handoutId]);

            $pdo->commit();
            flash('Handout archived. Existing order history is preserved.');
        } catch (Throwable $exception) {
            $pdo->rollBack();
            flash('Handout could not be archived. Please try again.', 'danger');
        }

        redirect('/Handout%20Payment%20System/admin/handouts/index.php');
    }
}

$handoutSql = 'SELECT h.*, c.title AS campus_course_title, d.code AS department_code, l.name AS level_name, COUNT(o.order_id) AS order_count
    FROM handouts h
    LEFT JOIN courses c ON c.course_id = h.course_id
    LEFT JOIN departments d ON d.department_id = h.department_id
    LEFT JOIN academic_levels l ON l.level_id = h.level_id
    LEFT JOIN orders o ON o.handout_id = h.handout_id';
$handoutParams = [];
if (!is_super_admin($admin)) {
    $handoutSql .= ' JOIN admin_course_assignments aca ON aca.course_id = h.course_id AND aca.admin_id = ?';
    $handoutParams[] = (int) $admin['admin_id'];
}
$handoutSql .= ' GROUP BY h.handout_id ORDER BY h.created_at DESC';
$stmt = db()->prepare($handoutSql);
$stmt->execute($handoutParams);
$handouts = $stmt->fetchAll();

page_header('Manage Handouts');
?>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Manage handouts</h1>
        <a class="btn btn-primary" href="/Handout%20Payment%20System/admin/handouts/edit.php">Add handout</a>
    </div>
    <div class="bg-white border rounded-2 p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Course</th>
                        <th>Title</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Orders</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($handouts as $handout): ?>
                        <tr>
                            <td>
                                <?= h($handout['course_code']) ?><br>
                                <span class="text-muted small">
                                    <?= h($handout['campus_course_title'] ?? 'No campus course') ?>
                                    <?php if (!empty($handout['department_code']) || !empty($handout['level_name'])): ?>
                                        · <?= h(trim(($handout['department_code'] ?? '') . ' ' . ($handout['level_name'] ?? ''))) ?>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td><?= h($handout['title']) ?></td>
                            <td><?= money($handout['current_price']) ?></td>
                            <td><?= status_badge($handout['status']) ?></td>
                            <td><?= (int) $handout['order_count'] ?></td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <a class="btn btn-sm btn-outline-primary" href="/Handout%20Payment%20System/admin/handouts/edit.php?id=<?= (int) $handout['handout_id'] ?>">Edit</a>
                                    <?php if ((int) $handout['order_count'] === 0): ?>
                                        <form method="post" onsubmit="return confirm('Delete this handout permanently?');">
                                            <input type="hidden" name="handout_id" value="<?= (int) $handout['handout_id'] ?>">
                                            <button class="btn btn-sm btn-outline-danger" name="action" value="delete" type="submit">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" onsubmit="return confirm('Archive this handout and preserve its order history?');">
                                            <input type="hidden" name="handout_id" value="<?= (int) $handout['handout_id'] ?>">
                                            <button class="btn btn-sm btn-outline-secondary" name="action" value="archive" type="submit">Archive</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php page_footer(); ?>
