<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

$admin = require_admin();
$pdo = db();
$stats = [
    'total_handouts' => (int) $pdo->query('SELECT COUNT(*) FROM handouts')->fetchColumn(),
    'available_handouts' => (int) $pdo->query('SELECT COUNT(*) FROM handouts WHERE status = "available"')->fetchColumn(),
    'paid_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE payment_status = "paid"')->fetchColumn(),
    'recorded_unpaid' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE payment_status = "not_paid"')->fetchColumn(),
    'revenue' => (float) $pdo->query('SELECT COALESCE(SUM(price_snapshot), 0) FROM orders WHERE payment_status = "paid"')->fetchColumn(),
];
$recent = $pdo->query('SELECT o.*, s.full_name FROM orders o JOIN students s ON s.student_id = o.student_id WHERE o.payment_status = "paid" ORDER BY o.ordered_at DESC LIMIT 8')->fetchAll();

page_header('Admin Dashboard');
?>
<main class="container py-5">
    <div class="d-flex flex-column flex-md-row justify-content-between gap-3 align-items-md-center mb-4">
        <div>
            <h1 class="h2 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Welcome, <?= h($admin['name']) ?>.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="/Handout%20Payment%20System/admin/handouts/index.php">Manage handouts</a>
            <a class="btn btn-primary" href="/Handout%20Payment%20System/admin/orders/index.php">View orders</a>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <?php foreach ([
            'Total handouts' => $stats['total_handouts'],
            'Available' => $stats['available_handouts'],
            'Paid orders' => $stats['paid_orders'],
            'Saved incomplete details' => $stats['recorded_unpaid'],
            'Revenue' => money($stats['revenue']),
        ] as $label => $value): ?>
            <div class="col-md">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="text-muted small"><?= h($label) ?></div>
                        <div class="h3 mb-0"><?= h((string) $value) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="bg-white border rounded-2 p-4">
        <h2 class="h4">Recent paid orders</h2>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Student</th>
                        <th>Handout</th>
                        <th>Amount</th>
                        <th>Collection</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $order): ?>
                        <tr>
                            <td><?= h($order['order_reference']) ?></td>
                            <td><?= h($order['full_name']) ?></td>
                            <td><?= h($order['course_code_snapshot']) ?></td>
                            <td><?= money($order['price_snapshot']) ?></td>
                            <td><?= status_badge($order['collection_status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (!$recent): ?>
            <div class="alert alert-info mb-0">No paid orders have been recorded yet.</div>
        <?php endif; ?>
    </div>
</main>
<?php page_footer(); ?>
