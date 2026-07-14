<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/layout.php';

$admin = require_admin();

if (is_super_admin($admin)) {
    flash('Order lists are managed by course reps. Use Revenue to verify course totals.', 'warning');
    redirect('/Handout%20Payment%20System/admin/dashboard.php?panel=revenue');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'update_collection';
    $orderId = (int) ($_POST['order_id'] ?? 0);

    if ($action === 'delete_paid_order') {
        $stmt = db()->prepare('SELECT o.*, s.full_name, h.course_id
            FROM orders o
            JOIN students s ON s.student_id = o.student_id
            JOIN handouts h ON h.handout_id = o.handout_id
            WHERE o.order_id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order) {
            flash('Order not found.', 'warning');
            redirect('/Handout%20Payment%20System/admin/orders/index.php');
        }

        if ($order['payment_status'] !== 'paid') {
            flash('Only paid students can be deleted from the paid list.', 'warning');
            redirect('/Handout%20Payment%20System/admin/orders/index.php');
        }
        if (!manageable_course_for_admin($admin, (int) $order['course_id'])) {
            flash('You can only update orders for your assigned courses.', 'warning');
            redirect('/Handout%20Payment%20System/admin/orders/index.php');
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE FROM payments WHERE order_id = ?');
            $stmt->execute([$orderId]);

            $stmt = $pdo->prepare('DELETE FROM orders WHERE order_id = ?');
            $stmt->execute([$orderId]);

            $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$admin['admin_id'], 'delete paid student from orders list', 'orders', $order['order_reference']]);

            $pdo->commit();
            flash($order['full_name'] . ' has been deleted from the paid list.');
        } catch (Throwable $exception) {
            $pdo->rollBack();
            flash('Paid student could not be deleted from the list. Please try again.', 'danger');
        }

        redirect('/Handout%20Payment%20System/admin/orders/index.php');
    }

    $collectionStatus = $_POST['collection_status'] ?? '';
    if (!in_array($collectionStatus, ['not_ready', 'ready_for_collection', 'collected'], true)) {
        flash('Collection status is invalid.', 'warning');
        redirect('/Handout%20Payment%20System/admin/orders/index.php');
    }

    $stmt = db()->prepare('SELECT h.course_id
        FROM orders o
        JOIN handouts h ON h.handout_id = o.handout_id
        WHERE o.order_id = ? AND o.payment_status = "paid"');
    $stmt->execute([$orderId]);
    $orderCourseId = (int) $stmt->fetchColumn();
    if ($orderCourseId <= 0 || !manageable_course_for_admin($admin, $orderCourseId)) {
        flash('You can only update orders for your assigned courses.', 'warning');
        redirect('/Handout%20Payment%20System/admin/orders/index.php');
    }

    $stmt = db()->prepare('UPDATE orders SET collection_status = ? WHERE order_id = ?');
    $stmt->execute([$collectionStatus, $orderId]);
    flash('Collection status updated.');
    redirect('/Handout%20Payment%20System/admin/orders/index.php');
}

$where = [];
$params = [];
$selectedHandoutId = (int) ($_GET['handout_id'] ?? 0);
$where[] = 'o.payment_status = ?';
$params[] = 'paid';
if (!is_super_admin($admin)) {
    $where[] = 'aca.admin_id = ?';
    $params[] = (int) $admin['admin_id'];
}
if ($selectedHandoutId > 0) {
    $where[] = 'o.handout_id = ?';
    $params[] = $selectedHandoutId;
}
if (!empty($_GET['q'])) {
    $where[] = '(s.full_name LIKE ? OR s.index_number LIKE ? OR o.order_reference LIKE ? OR o.handout_title_snapshot LIKE ?)';
    $search = '%' . $_GET['q'] . '%';
    array_push($params, $search, $search, $search, $search);
}

$handoutSql = 'SELECT h.handout_id, h.title, h.course_code
    FROM handouts h';
$handoutParams = [];
if (!is_super_admin($admin)) {
    $handoutSql .= ' JOIN admin_course_assignments aca ON aca.course_id = h.course_id AND aca.admin_id = ?';
    $handoutParams[] = (int) $admin['admin_id'];
}
$handoutSql .= ' ORDER BY h.course_code, h.title';
$stmt = db()->prepare($handoutSql);
$stmt->execute($handoutParams);
$handouts = $stmt->fetchAll();

$paidSummarySql = 'SELECT o.handout_id, o.course_code_snapshot, o.handout_title_snapshot,
        COUNT(*) AS paid_count,
        COALESCE(SUM(o.price_snapshot), 0) AS paid_total
    FROM orders o
';
$paidSummaryParams = [];
if (!is_super_admin($admin)) {
    $paidSummarySql .= 'JOIN handouts h ON h.handout_id = o.handout_id
        JOIN admin_course_assignments aca ON aca.course_id = h.course_id AND aca.admin_id = ?';
    $paidSummaryParams[] = (int) $admin['admin_id'];
}
$paidSummarySql .= ' WHERE o.payment_status = "paid"
    GROUP BY o.handout_id, o.course_code_snapshot, o.handout_title_snapshot
    ORDER BY o.course_code_snapshot, o.handout_title_snapshot';
$stmt = db()->prepare($paidSummarySql);
$stmt->execute($paidSummaryParams);
$paidSummary = $stmt->fetchAll();

$sql = 'SELECT o.*, s.full_name, s.index_number, s.phone, s.email
    FROM orders o
    JOIN students s ON s.student_id = o.student_id';
if (!is_super_admin($admin)) {
    $sql .= ' JOIN handouts h ON h.handout_id = o.handout_id
        JOIN admin_course_assignments aca ON aca.course_id = h.course_id';
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY o.ordered_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

page_header('Orders');
?>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">Paid list</h1>
                        <p class="text-muted mb-0"><?= is_super_admin($admin) ? 'Only students who have paid appear here. Super admins can review orders, while course reps manage collection updates.' : 'Only students who have paid appear here. Incomplete payment details are still saved separately.' ?></p>
        </div>
        <a class="btn btn-outline-primary" href="/Handout%20Payment%20System/admin/dashboard.php">Dashboard</a>
    </div>
    <form class="bg-white border rounded-2 p-3 mb-4" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label" for="q">Search</label>
                <input class="form-control" id="q" name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="Student, index, reference or handout">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="handout_id">Handout</label>
                <select class="form-select" id="handout_id" name="handout_id">
                    <option value="">All handouts</option>
                    <?php foreach ($handouts as $handout): ?>
                        <option value="<?= (int) $handout['handout_id'] ?>" <?= $selectedHandoutId === (int) $handout['handout_id'] ? 'selected' : '' ?>>
                            <?= h($handout['course_code'] . ' - ' . $handout['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Filter</button>
            </div>
        </div>
    </form>
    <div class="bg-white border rounded-2 p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
            <div>
                <h2 class="h4 mb-1">Paid by handout</h2>
                <p class="text-muted mb-0">Use these totals to prepare copies for each course handout.</p>
            </div>
            <a class="btn btn-sm btn-outline-primary align-self-md-start" href="/Handout%20Payment%20System/admin/orders/index.php">Show all paid</a>
        </div>
        <div class="row g-3">
            <?php foreach ($paidSummary as $summary): ?>
                <div class="col-md-4">
                    <a class="card dashboard-card text-decoration-none h-100" href="/Handout%20Payment%20System/admin/orders/index.php?handout_id=<?= (int) $summary['handout_id'] ?>">
                        <div class="card-body">
                            <div class="text-muted small"><?= h($summary['course_code_snapshot']) ?></div>
                            <h3 class="h6 text-dark mb-3"><?= h($summary['handout_title_snapshot']) ?></h3>
                            <div class="d-flex justify-content-between align-items-end">
                                <div>
                                    <div class="h3 mb-0 text-primary"><?= (int) $summary['paid_count'] ?></div>
                                    <div class="text-muted small">students paid</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-dark"><?= money($summary['paid_total']) ?></div>
                                    <div class="text-muted small">received</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!$paidSummary): ?>
            <div class="alert alert-info mb-0">No paid orders have been recorded yet.</div>
        <?php endif; ?>
    </div>
    <div class="bg-white border rounded-2 p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Student</th>
                        <th>Contact</th>
                        <th>Handout</th>
                        <th>Amount</th>
                        <th>Collection</th>
                        <?php if (!is_super_admin($admin)): ?>
                            <th></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= h($order['order_reference']) ?></td>
                            <td><?= h($order['full_name']) ?><br><span class="text-muted small"><?= h($order['index_number']) ?></span></td>
                            <td><span class="small"><?= h($order['phone']) ?><br><?= h($order['email']) ?></span></td>
                            <td><?= h($order['course_code_snapshot']) ?><br><span class="text-muted small"><?= h($order['handout_title_snapshot']) ?></span></td>
                            <td><?= money($order['price_snapshot']) ?></td>
                            <td>
                                <?php if (is_super_admin($admin)): ?>
                                    <?= status_badge($order['collection_status']) ?>
                                <?php else: ?>
                                    <form method="post" class="d-flex gap-2">
                                        <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                                        <input type="hidden" name="action" value="update_collection">
                                        <select class="form-select form-select-sm" name="collection_status">
                                            <?php foreach (['not_ready', 'ready_for_collection', 'collected'] as $status): ?>
                                                <option value="<?= h($status) ?>" <?= $order['collection_status'] === $status ? 'selected' : '' ?>><?= h(str_replace('_', ' ', $status)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <?php if (!is_super_admin($admin)): ?>
                                <td class="text-end">
                                    <form method="post" onsubmit="return confirm('Delete this student from the paid list after giving the handout?');">
                                        <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger" name="action" value="delete_paid_order" type="submit">Delete</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (!$orders): ?>
            <div class="alert alert-info mb-0">No paid students match the current filters.</div>
        <?php endif; ?>
    </div>
</main>
<?php page_footer(); ?>
