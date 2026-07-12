<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/layout.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare('UPDATE orders SET collection_status = ? WHERE order_id = ?');
    $stmt->execute([$_POST['collection_status'], (int) $_POST['order_id']]);
    flash('Collection status updated.');
    redirect('/Handout%20Payment%20System/admin/orders/index.php');
}

$where = [];
$params = [];
if (!empty($_GET['payment_status'])) {
    $where[] = 'o.payment_status = ?';
    $params[] = $_GET['payment_status'];
}
if (!empty($_GET['q'])) {
    $where[] = '(s.full_name LIKE ? OR s.index_number LIKE ? OR o.order_reference LIKE ? OR o.handout_title_snapshot LIKE ?)';
    $search = '%' . $_GET['q'] . '%';
    array_push($params, $search, $search, $search, $search);
}

$sql = 'SELECT o.*, s.full_name, s.index_number, s.phone, s.email
    FROM orders o
    JOIN students s ON s.student_id = o.student_id';
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
        <h1 class="h2 mb-0">Orders</h1>
        <a class="btn btn-outline-primary" href="/Handout%20Payment%20System/admin/dashboard.php">Dashboard</a>
    </div>
    <form class="bg-white border rounded-2 p-3 mb-4" method="get">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label" for="q">Search</label>
                <input class="form-control" id="q" name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="Student, index, reference or handout">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="payment_status">Payment status</label>
                <select class="form-select" id="payment_status" name="payment_status">
                    <option value="">All statuses</option>
                    <?php foreach (['pending_payment', 'paid', 'payment_failed', 'cancelled'] as $status): ?>
                        <option value="<?= h($status) ?>" <?= ($_GET['payment_status'] ?? '') === $status ? 'selected' : '' ?>><?= h(str_replace('_', ' ', $status)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Filter</button>
            </div>
        </div>
    </form>
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
                        <th>Payment</th>
                        <th>Collection</th>
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
                            <td><?= status_badge($order['payment_status']) ?></td>
                            <td>
                                <form method="post" class="d-flex gap-2">
                                    <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                                    <select class="form-select form-select-sm" name="collection_status" <?= $order['payment_status'] !== 'paid' ? 'disabled' : '' ?>>
                                        <?php foreach (['not_ready', 'ready_for_collection', 'collected'] as $status): ?>
                                            <option value="<?= h($status) ?>" <?= $order['collection_status'] === $status ? 'selected' : '' ?>><?= h(str_replace('_', ' ', $status)) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-outline-primary" type="submit" <?= $order['payment_status'] !== 'paid' ? 'disabled' : '' ?>>Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (!$orders): ?>
            <div class="alert alert-info mb-0">No orders match the current filters.</div>
        <?php endif; ?>
    </div>
</main>
<?php page_footer(); ?>
