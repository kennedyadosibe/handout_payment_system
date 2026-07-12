<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

$admin = require_admin();
$pdo = db();

$dashboardBaseUrl = '/Handout%20Payment%20System/admin/dashboard.php';
$allowedReturnPanels = ['overview', 'revenue', 'paid-students', 'manage-handouts', 'edit-handout', 'view-orders'];
$returnPanel = $_POST['return_panel'] ?? 'overview';
if (!in_array($returnPanel, $allowedReturnPanels, true)) {
    $returnPanel = 'overview';
}
$returnUrl = $dashboardBaseUrl . '?panel=' . rawurlencode($returnPanel);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $handoutId = (int) ($_POST['handout_id'] ?? 0);

    if ($action === 'delete' || $action === 'archive') {
        $stmt = $pdo->prepare('SELECT h.*, COUNT(o.order_id) AS order_count
            FROM handouts h
            LEFT JOIN orders o ON o.handout_id = h.handout_id
            WHERE h.handout_id = ?
            GROUP BY h.handout_id');
        $stmt->execute([$handoutId]);
        $handout = $stmt->fetch();

        if (!$handout) {
            flash('Handout not found.', 'warning');
            redirect($returnUrl);
        }

        if ($action === 'delete') {
            if ((int) $handout['order_count'] > 0) {
                flash('This handout already has orders, so it was not deleted. Archive it to remove it from active class use while keeping records.', 'warning');
                redirect($returnUrl);
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('DELETE FROM handouts WHERE handout_id = ?');
                $stmt->execute([$handoutId]);

                $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
                $stmt->execute([$admin['admin_id'], 'delete handout from dashboard', 'handouts', (string) $handoutId]);

                $pdo->commit();
                flash('Handout deleted.');
            } catch (Throwable $exception) {
                $pdo->rollBack();
                flash('Handout could not be deleted. Please try again.', 'danger');
            }

            redirect($returnUrl);
        }

        if ($action === 'archive') {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('UPDATE handouts SET status = "archived" WHERE handout_id = ?');
                $stmt->execute([$handoutId]);

                $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
                $stmt->execute([$admin['admin_id'], 'archive handout from dashboard', 'handouts', (string) $handoutId]);

                $pdo->commit();
                flash('Handout archived. Existing order history is preserved.');
            } catch (Throwable $exception) {
                $pdo->rollBack();
                flash('Handout could not be archived. Please try again.', 'danger');
            }

            redirect($returnUrl);
        }
    }

    if ($action === 'save_handout') {
        $data = [
            trim($_POST['title'] ?? ''),
            trim($_POST['course_code'] ?? ''),
            trim($_POST['description'] ?? ''),
            (float) ($_POST['current_price'] ?? 0),
            $_POST['status'] ?? 'available',
        ];
        $saveUrl = $dashboardBaseUrl . '?panel=edit-handout';
        if ($handoutId > 0) {
            $saveUrl .= '&handout_id=' . $handoutId;
        }

        if ($data[0] === '' || $data[1] === '' || $data[2] === '' || $data[3] <= 0 || !in_array($data[4], ['available', 'unavailable', 'archived'], true)) {
            flash('Please complete all fields with a valid price.', 'danger');
            redirect($saveUrl);
        }

        if ($handoutId > 0) {
            $stmt = $pdo->prepare('UPDATE handouts SET title = ?, course_code = ?, description = ?, current_price = ?, status = ? WHERE handout_id = ?');
            $stmt->execute([...$data, $handoutId]);
            flash('Handout updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO handouts (title, course_code, description, current_price, status, created_by) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([...$data, $admin['admin_id']]);
            flash('Handout added.');
        }

        redirect($dashboardBaseUrl . '?panel=manage-handouts');
    }

    if ($action === 'delete_paid_order') {
        $stmt = $pdo->prepare('SELECT o.*, s.full_name
            FROM orders o
            JOIN students s ON s.student_id = o.student_id
            WHERE o.order_id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order || $order['payment_status'] !== 'paid') {
            flash('Paid student record not found.', 'warning');
            redirect($returnUrl);
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE FROM payments WHERE order_id = ?');
            $stmt->execute([$orderId]);

            $stmt = $pdo->prepare('DELETE FROM orders WHERE order_id = ?');
            $stmt->execute([$orderId]);

            $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$admin['admin_id'], 'delete paid student from dashboard handout group', 'orders', $order['order_reference']]);

            $pdo->commit();
            flash($order['full_name'] . ' has been deleted from the handout paid list.');
        } catch (Throwable $exception) {
            $pdo->rollBack();
            flash('Student could not be deleted from the handout paid list. Please try again.', 'danger');
        }

        redirect($returnUrl);
    }

    if ($action === 'update_collection') {
        $collectionStatus = $_POST['collection_status'] ?? '';
        if (!in_array($collectionStatus, ['not_ready', 'ready_for_collection', 'collected'], true)) {
            flash('Collection status is invalid.', 'warning');
            redirect($returnUrl);
        }

        $stmt = $pdo->prepare('UPDATE orders SET collection_status = ? WHERE order_id = ? AND payment_status = "paid"');
        $stmt->execute([$collectionStatus, $orderId]);
        flash('Collection status updated.');
        redirect($returnUrl);
    }

    if ($action === 'mark_collected') {
        $stmt = $pdo->prepare('SELECT o.*, s.full_name
            FROM orders o
            JOIN students s ON s.student_id = o.student_id
            WHERE o.order_id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order || $order['payment_status'] !== 'paid') {
            flash('Paid student record not found.', 'warning');
            redirect($returnUrl);
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE orders SET collection_status = "collected" WHERE order_id = ?');
            $stmt->execute([$orderId]);

            $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$admin['admin_id'], 'mark handout as given from dashboard', 'orders', $order['order_reference']]);

            $pdo->commit();
            flash($order['full_name'] . ' has been marked as given.');
        } catch (Throwable $exception) {
            $pdo->rollBack();
            flash('Student could not be marked as given. Please try again.', 'danger');
        }

        redirect($returnUrl);
    }
}

$stats = [
    'total_handouts' => (int) $pdo->query('SELECT COUNT(*) FROM handouts')->fetchColumn(),
    'available_handouts' => (int) $pdo->query('SELECT COUNT(*) FROM handouts WHERE status = "available"')->fetchColumn(),
    'paid_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE payment_status = "paid"')->fetchColumn(),
    'recorded_unpaid' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE payment_status = "not_paid"')->fetchColumn(),
];
$editHandoutId = (int) ($_GET['handout_id'] ?? 0);
$editHandout = null;
if ($editHandoutId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM handouts WHERE handout_id = ?');
    $stmt->execute([$editHandoutId]);
    $editHandout = $stmt->fetch();
    if (!$editHandout) {
        flash('Handout not found.', 'warning');
        redirect($dashboardBaseUrl . '?panel=manage-handouts');
    }
}
$studentSearch = trim($_GET['student_name'] ?? '');
$revenueByHandout = $pdo->query('SELECT handout_id, course_code_snapshot, handout_title_snapshot,
        COUNT(*) AS paid_count,
        COALESCE(SUM(price_snapshot), 0) AS total_revenue
    FROM orders
    WHERE payment_status = "paid"
    GROUP BY handout_id, course_code_snapshot, handout_title_snapshot
    ORDER BY course_code_snapshot, handout_title_snapshot')->fetchAll();
$dashboardHandouts = $pdo->query('SELECT h.*, COUNT(o.order_id) AS order_count
    FROM handouts h
    LEFT JOIN orders o ON o.handout_id = h.handout_id
    GROUP BY h.handout_id
    ORDER BY h.created_at DESC')->fetchAll();
$dashboardOrders = $pdo->query('SELECT o.*, s.full_name, s.index_number, s.phone, s.email
    FROM orders o
    JOIN students s ON s.student_id = o.student_id
    WHERE o.payment_status = "paid"
    ORDER BY o.ordered_at DESC')->fetchAll();
$paidSql = 'SELECT o.*, s.full_name, s.index_number, s.phone
    FROM orders o
    JOIN students s ON s.student_id = o.student_id
    WHERE o.payment_status = "paid"';
$paidParams = [];
if ($studentSearch !== '') {
    $paidSql .= ' AND s.full_name LIKE ?';
    $paidParams[] = '%' . $studentSearch . '%';
}
$paidSql .= ' ORDER BY o.course_code_snapshot, o.handout_title_snapshot, s.full_name';
$stmt = $pdo->prepare($paidSql);
$stmt->execute($paidParams);
$paidRows = $stmt->fetchAll();
$paidByHandout = [];
foreach ($paidRows as $row) {
    $key = $row['course_code_snapshot'] . '|' . $row['handout_title_snapshot'];
    if (!isset($paidByHandout[$key])) {
        $paidByHandout[$key] = [
            'course_code' => $row['course_code_snapshot'],
            'title' => $row['handout_title_snapshot'],
            'total' => 0.0,
            'students' => [],
        ];
    }
    $paidByHandout[$key]['total'] += (float) $row['price_snapshot'];
    $paidByHandout[$key]['students'][] = $row;
}

page_header('Admin Dashboard');
?>
<main class="container-fluid px-3 px-lg-4 py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center mb-4">
        <div>
            <h1 class="h2 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Welcome, <?= h($admin['name']) ?>.</p>
        </div>
    </div>

    <div class="dashboard-shell">
        <aside class="dashboard-sidebar" aria-label="Dashboard sections">
            <div class="sidebar-label">Sections</div>
            <button class="dashboard-nav-item active" type="button" data-dashboard-target="overview">
                <span>Overview</span>
                <strong><?= h((string) $stats['paid_orders']) ?></strong>
            </button>
            <button class="dashboard-nav-item" type="button" data-dashboard-target="revenue">
                <span>Revenue</span>
                <strong><?= count($revenueByHandout) ?></strong>
            </button>
            <button class="dashboard-nav-item" type="button" data-dashboard-target="paid-students">
                <span>Paid students</span>
                <strong><?= h((string) $stats['paid_orders']) ?></strong>
            </button>
            <?php if ($paidByHandout): ?>
                <div class="dashboard-subnav" aria-label="Paid student handouts">
                    <button class="dashboard-subnav-item active" type="button" data-course-target="all">
                        <span>All paid lists</span>
                    </button>
                    <?php foreach ($paidByHandout as $group): ?>
                        <?php $courseKey = md5($group['course_code'] . '|' . $group['title']); ?>
                        <button class="dashboard-subnav-item" type="button" data-course-target="<?= h($courseKey) ?>">
                            <span><?= h($group['course_code']) ?></span>
                            <small><?= count($group['students']) ?></small>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="sidebar-label mt-4">Admin links</div>
            <button class="dashboard-nav-item" type="button" data-dashboard-target="manage-handouts">
                <span>Manage handouts</span>
                <strong><?= count($dashboardHandouts) ?></strong>
            </button>
            <button class="dashboard-nav-item" type="button" data-dashboard-target="edit-handout">
                <span><?= $editHandoutId ? 'Edit handout' : 'Add handout' ?></span>
            </button>
            <button class="dashboard-nav-item" type="button" data-dashboard-target="view-orders">
                <span>View orders</span>
                <strong><?= count($dashboardOrders) ?></strong>
            </button>
        </aside>

        <div class="dashboard-content">
            <section class="dashboard-panel" id="dashboard-overview" data-dashboard-panel="overview">
                <div class="row g-3">
                    <?php foreach ([
                        'Total handouts' => $stats['total_handouts'],
                        'Available' => $stats['available_handouts'],
                        'Paid orders' => $stats['paid_orders'],
                        'Saved incomplete details' => $stats['recorded_unpaid'],
                    ] as $label => $value): ?>
                        <div class="col-md-6 col-xl-3">
                            <div class="card dashboard-card h-100">
                                <div class="card-body">
                                    <div class="text-muted small"><?= h($label) ?></div>
                                    <div class="h3 mb-0"><?= h((string) $value) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="dashboard-panel" id="dashboard-revenue" data-dashboard-panel="revenue" hidden>
                <div class="bg-white border rounded-2 p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Revenue by handout</h2>
                            <p class="text-muted mb-0">Each handout keeps its own revenue total.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary align-self-md-start" type="button" data-dashboard-target="view-orders">Open paid list</button>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($revenueByHandout as $revenue): ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="card dashboard-card revenue-card h-100">
                                    <div class="card-body">
                                        <div class="text-muted small"><?= h($revenue['course_code_snapshot']) ?></div>
                                        <h3 class="h6 mb-3"><?= h($revenue['handout_title_snapshot']) ?></h3>
                                        <div class="h3 mb-1"><?= money($revenue['total_revenue']) ?></div>
                                        <div class="text-muted small"><?= (int) $revenue['paid_count'] ?> paid student<?= (int) $revenue['paid_count'] === 1 ? '' : 's' ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!$revenueByHandout): ?>
                        <div class="alert alert-info mb-0">No paid revenue has been recorded yet.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="dashboard-panel" id="dashboard-paid-students" data-dashboard-panel="paid-students" hidden>
                <div class="bg-white border rounded-2 p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Paid students by handout</h2>
                            <p class="text-muted mb-0">Students are grouped under the exact handout they paid for.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary align-self-md-start" type="button" data-dashboard-target="view-orders">Open paid list</button>
                    </div>
                    <form class="row g-3 align-items-end mb-4" method="get">
                        <input type="hidden" name="panel" value="paid-students">
                        <div class="col-md-8">
                            <label class="form-label" for="student_name">Search student name</label>
                            <input class="form-control" id="student_name" name="student_name" value="<?= h($studentSearch) ?>" placeholder="Enter student name">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button class="btn btn-primary flex-fill" type="submit">Search</button>
                            <?php if ($studentSearch !== ''): ?>
                                <a class="btn btn-outline-secondary" href="/Handout%20Payment%20System/admin/dashboard.php?panel=paid-students">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                    <?php if ($studentSearch !== ''): ?>
                        <div class="alert alert-info">Showing paid students matching "<?= h($studentSearch) ?>".</div>
                    <?php endif; ?>
                    <?php foreach ($paidByHandout as $group): ?>
                        <?php $courseKey = md5($group['course_code'] . '|' . $group['title']); ?>
                        <section class="paid-group border rounded-2 p-3 mb-3" data-course-group="<?= h($courseKey) ?>">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                                <div>
                                    <span class="badge text-bg-secondary"><?= h($group['course_code']) ?></span>
                                    <h3 class="h5 mt-2 mb-0"><?= h($group['title']) ?></h3>
                                </div>
                                <div class="text-md-end">
                                    <div class="fw-bold"><?= count($group['students']) ?> student<?= count($group['students']) === 1 ? '' : 's' ?></div>
                                    <div class="text-muted small"><?= money($group['total']) ?> received</div>
                                </div>
                            </div>
                            <div class="paid-group-search mb-3">
                                <label class="form-label" for="search-<?= h(md5($group['course_code'] . $group['title'])) ?>">Search this handout list</label>
                                <input class="form-control form-control-sm" id="search-<?= h(md5($group['course_code'] . $group['title'])) ?>" data-handout-search type="search" placeholder="Student name in <?= h($group['course_code']) ?>">
                                <div class="small text-muted mt-1" data-handout-search-count></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Index</th>
                                            <th>Phone</th>
                                            <th>Amount</th>
                                            <th>Collection</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($group['students'] as $student): ?>
                                            <tr class="<?= $student['collection_status'] === 'collected' ? 'student-collected' : '' ?>">
                                                <td><?= h($student['full_name']) ?></td>
                                                <td><?= h($student['index_number']) ?></td>
                                                <td><?= h($student['phone']) ?></td>
                                                <td><?= money($student['price_snapshot']) ?></td>
                                                <td><?= status_badge($student['collection_status']) ?></td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-2">
                                                        <?php if ($student['collection_status'] !== 'collected'): ?>
                                                            <form method="post">
                                                                <input type="hidden" name="order_id" value="<?= (int) $student['order_id'] ?>">
                                                                <input type="hidden" name="return_panel" value="paid-students">
                                                                <button class="btn btn-sm btn-outline-primary" name="action" value="mark_collected" type="submit">Given</button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <form method="post" onsubmit="return confirm('Delete this student from this handout paid list? This removes the order and reduces revenue.');">
                                                            <input type="hidden" name="order_id" value="<?= (int) $student['order_id'] ?>">
                                                            <input type="hidden" name="return_panel" value="paid-students">
                                                            <button class="btn btn-sm btn-outline-danger" name="action" value="delete_paid_order" type="submit">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    <?php endforeach; ?>
                    <?php if (!$paidByHandout): ?>
                        <div class="alert alert-info mb-0"><?= $studentSearch !== '' ? 'No paid students match that name.' : 'No paid orders have been recorded yet.' ?></div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="dashboard-panel" id="dashboard-manage-handouts" data-dashboard-panel="manage-handouts" hidden>
                <div class="bg-white border rounded-2 p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Manage handouts</h2>
                            <p class="text-muted mb-0">Edit, delete unused handouts, or archive handouts that already have order history.</p>
                        </div>
                        <a class="btn btn-sm btn-primary align-self-md-start" href="/Handout%20Payment%20System/admin/dashboard.php?panel=edit-handout">Add handout</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
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
                                <?php foreach ($dashboardHandouts as $handout): ?>
                                    <tr>
                                        <td><?= h($handout['course_code']) ?></td>
                                        <td><?= h($handout['title']) ?></td>
                                        <td><?= money($handout['current_price']) ?></td>
                                        <td><?= status_badge($handout['status']) ?></td>
                                        <td><?= (int) $handout['order_count'] ?></td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <a class="btn btn-sm btn-outline-primary" href="/Handout%20Payment%20System/admin/dashboard.php?panel=edit-handout&handout_id=<?= (int) $handout['handout_id'] ?>">Edit</a>
                                                <?php if ((int) $handout['order_count'] === 0): ?>
                                                    <form method="post" onsubmit="return confirm('Delete this handout permanently?');">
                                                        <input type="hidden" name="handout_id" value="<?= (int) $handout['handout_id'] ?>">
                                                        <input type="hidden" name="return_panel" value="manage-handouts">
                                                        <button class="btn btn-sm btn-outline-danger" name="action" value="delete" type="submit">Delete</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="post" onsubmit="return confirm('Archive this handout and preserve its order history?');">
                                                        <input type="hidden" name="handout_id" value="<?= (int) $handout['handout_id'] ?>">
                                                        <input type="hidden" name="return_panel" value="manage-handouts">
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
                    <?php if (!$dashboardHandouts): ?>
                        <div class="alert alert-info mb-0">No handouts have been created yet.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="dashboard-panel" id="dashboard-edit-handout" data-dashboard-panel="edit-handout" hidden>
                <form method="post" class="bg-white border rounded-2 p-4">
                    <input type="hidden" name="action" value="save_handout">
                    <input type="hidden" name="handout_id" value="<?= (int) $editHandoutId ?>">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1"><?= $editHandoutId ? 'Edit handout' : 'Add handout' ?></h2>
                            <p class="text-muted mb-0">Update the course handout details without leaving the dashboard.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary align-self-md-start" type="button" data-dashboard-target="manage-handouts">Back to handouts</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label" for="title">Title</label>
                            <input class="form-control" id="title" name="title" value="<?= h($editHandout['title'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="course_code">Course code</label>
                            <input class="form-control" id="course_code" name="course_code" value="<?= h($editHandout['course_code'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="current_price">Current price</label>
                            <input class="form-control" id="current_price" name="current_price" type="number" step="0.01" min="0" value="<?= h($editHandout['current_price'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php foreach (['available', 'unavailable', 'archived'] as $status): ?>
                                    <option value="<?= h($status) ?>" <?= ($editHandout['status'] ?? 'available') === $status ? 'selected' : '' ?>><?= h(str_replace('_', ' ', $status)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?= h($editHandout['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">Save handout</button>
                        <button class="btn btn-outline-secondary" type="button" data-dashboard-target="manage-handouts">Cancel</button>
                    </div>
                </form>
            </section>

            <section class="dashboard-panel" id="dashboard-view-orders" data-dashboard-panel="view-orders" hidden>
                <div class="bg-white border rounded-2 p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">View orders</h2>
                            <p class="text-muted mb-0">Only paid students appear here. Use this list to update collection status or remove a paid record.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary align-self-md-start" type="button" data-dashboard-target="paid-students">Grouped paid lists</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Student</th>
                                    <th>Contact</th>
                                    <th>Handout</th>
                                    <th>Amount</th>
                                    <th>Collection</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dashboardOrders as $order): ?>
                                    <tr class="<?= $order['collection_status'] === 'collected' ? 'student-collected' : '' ?>">
                                        <td><?= h($order['order_reference']) ?></td>
                                        <td><?= h($order['full_name']) ?><br><span class="text-muted small"><?= h($order['index_number']) ?></span></td>
                                        <td><span class="small"><?= h($order['phone']) ?><br><?= h($order['email']) ?></span></td>
                                        <td><?= h($order['course_code_snapshot']) ?><br><span class="text-muted small"><?= h($order['handout_title_snapshot']) ?></span></td>
                                        <td><?= money($order['price_snapshot']) ?></td>
                                        <td>
                                            <form method="post" class="d-flex gap-2">
                                                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                                                <input type="hidden" name="action" value="update_collection">
                                                <input type="hidden" name="return_panel" value="view-orders">
                                                <select class="form-select form-select-sm" name="collection_status">
                                                    <?php foreach (['not_ready', 'ready_for_collection', 'collected'] as $status): ?>
                                                        <option value="<?= h($status) ?>" <?= $order['collection_status'] === $status ? 'selected' : '' ?>><?= h(str_replace('_', ' ', $status)) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <form method="post" onsubmit="return confirm('Delete this student from the paid list after giving the handout?');">
                                                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                                                <input type="hidden" name="return_panel" value="view-orders">
                                                <button class="btn btn-sm btn-outline-danger" name="action" value="delete_paid_order" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!$dashboardOrders): ?>
                        <div class="alert alert-info mb-0">No paid orders have been recorded yet.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</main>
<script src="/Handout%20Payment%20System/assets/js/dashboard.js"></script>
<?php page_footer(); ?>
