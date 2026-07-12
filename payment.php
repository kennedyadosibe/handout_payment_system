<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/layout.php';

$reference = trim($_GET['order'] ?? '');
$stmt = db()->prepare('SELECT o.*, s.full_name, s.email, p.reference AS payment_reference, p.status AS payment_status_detail
    FROM orders o
    JOIN students s ON s.student_id = o.student_id
    JOIN payments p ON p.order_id = o.order_id
    WHERE o.order_reference = ?');
$stmt->execute([$reference]);
$order = $stmt->fetch();

if (!$order) {
    flash('Order not found.', 'warning');
    redirect('/Handout%20Payment%20System/handouts.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'success';
    $pdo = db();
    if ($action === 'success') {
        $stmt = $pdo->prepare('UPDATE payments SET status = "successful", paid_at = NOW() WHERE order_id = ? AND amount = ?');
        $stmt->execute([$order['order_id'], $order['price_snapshot']]);
        $stmt = $pdo->prepare('UPDATE orders SET payment_status = "paid" WHERE order_id = ?');
        $stmt->execute([$order['order_id']]);
    } else {
        $stmt = $pdo->prepare('UPDATE payments SET status = "failed" WHERE order_id = ?');
        $stmt->execute([$order['order_id']]);
        $stmt = $pdo->prepare('UPDATE orders SET payment_status = "payment_failed" WHERE order_id = ?');
        $stmt->execute([$order['order_id']]);
    }
    redirect('/Handout%20Payment%20System/payment-result.php?order=' . urlencode($order['order_reference']));
}

page_header('Payment', 'handouts');
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="bg-white border rounded-2 p-4">
                <h1 class="h3">Test payment checkout</h1>
                <p class="text-muted">This prototype simulates a gateway response. A production version should initialize and verify payment with a real Ghana-supported provider.</p>
                <dl class="row">
                    <dt class="col-sm-4">Student</dt>
                    <dd class="col-sm-8"><?= h($order['full_name']) ?></dd>
                    <dt class="col-sm-4">Handout</dt>
                    <dd class="col-sm-8"><?= h($order['course_code_snapshot'] . ' - ' . $order['handout_title_snapshot']) ?></dd>
                    <dt class="col-sm-4">Amount</dt>
                    <dd class="col-sm-8 fw-bold"><?= money($order['price_snapshot']) ?></dd>
                    <dt class="col-sm-4">Payment reference</dt>
                    <dd class="col-sm-8"><?= h($order['payment_reference']) ?></dd>
                </dl>
                <form method="post" class="d-flex gap-2 flex-wrap">
                    <button class="btn btn-primary" name="action" value="success" type="submit">Simulate successful payment</button>
                    <button class="btn btn-outline-danger" name="action" value="fail" type="submit">Simulate failed payment</button>
                </form>
            </div>
        </div>
    </div>
</main>
<?php page_footer(); ?>
