<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

$admin = require_admin();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderId = (int) ($_POST['order_id'] ?? 0);

    if ($action === 'delete_paid_order') {
        $stmt = $pdo->prepare('SELECT o.*, s.full_name
            FROM orders o
            JOIN students s ON s.student_id = o.student_id
            WHERE o.order_id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order || $order['payment_status'] !== 'paid') {
            flash('Paid student record not found.', 'warning');
            redirect('/Handout%20Payment%20System/admin/dashboard.php');
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

        redirect('/Handout%20Payment%20System/admin/dashboard.php');
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
            redirect('/Handout%20Payment%20System/admin/dashboard.php');
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

        redirect('/Handout%20Payment%20System/admin/dashboard.php');
    }
}

$stats = [
    'total_handouts' => (int) $pdo->query('SELECT COUNT(*) FROM handouts')->fetchColumn(),
    'available_handouts' => (int) $pdo->query('SELECT COUNT(*) FROM handouts WHERE status = "available"')->fetchColumn(),
    'paid_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE payment_status = "paid"')->fetchColumn(),
    'recorded_unpaid' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE payment_status = "not_paid"')->fetchColumn(),
];
$studentSearch = trim($_GET['student_name'] ?? '');
$revenueByHandout = $pdo->query('SELECT handout_id, course_code_snapshot, handout_title_snapshot,
        COUNT(*) AS paid_count,
        COALESCE(SUM(price_snapshot), 0) AS total_revenue
    FROM orders
    WHERE payment_status = "paid"
    GROUP BY handout_id, course_code_snapshot, handout_title_snapshot
    ORDER BY course_code_snapshot, handout_title_snapshot')->fetchAll();
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
            <a class="dashboard-nav-link" href="/Handout%20Payment%20System/admin/handouts/index.php">Manage handouts</a>
            <a class="dashboard-nav-link" href="/Handout%20Payment%20System/admin/orders/index.php">View orders</a>
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
                        <a class="btn btn-sm btn-outline-primary align-self-md-start" href="/Handout%20Payment%20System/admin/orders/index.php">Open paid list</a>
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
                        <a class="btn btn-sm btn-outline-primary align-self-md-start" href="/Handout%20Payment%20System/admin/orders/index.php">Open paid list</a>
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
                                                                <button class="btn btn-sm btn-outline-primary" name="action" value="mark_collected" type="submit">Given</button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <form method="post" onsubmit="return confirm('Delete this student from this handout paid list? This removes the order and reduces revenue.');">
                                                            <input type="hidden" name="order_id" value="<?= (int) $student['order_id'] ?>">
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
        </div>
    </div>
</main>
<script src="/Handout%20Payment%20System/assets/js/dashboard.js"></script>
<?php page_footer(); ?>
