<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/layout.php';
require_once __DIR__ . '/app/paystack.php';

$reference = trim($_GET['order'] ?? '');
$paystackReference = trim($_GET['reference'] ?? $_GET['trxref'] ?? '');
$stmt = db()->prepare('SELECT o.*, s.full_name, s.email, s.phone, p.reference AS payment_reference, p.status AS gateway_status, p.paid_at
    FROM orders o
    JOIN students s ON s.student_id = o.student_id
    JOIN payments p ON p.order_id = o.order_id
    WHERE o.order_reference = ?');
$stmt->execute([$reference]);
$order = $stmt->fetch();

if (!$order) {
    flash('Order not found.', 'warning');
    redirect('/Handout%20Payment%20System/receipt.php');
}

if ($paystackReference !== '' && $order['payment_status'] !== 'paid') {
    try {
        $response = paystack_verify_transaction($paystackReference);
        $data = $response['data'] ?? [];
        $verifiedAmount = (int) ($data['amount'] ?? 0);
        $verifiedStatus = $data['status'] ?? '';
        $expectedAmount = paystack_amount_subunit($order['price_snapshot']);
        $referenceMatches = hash_equals($order['payment_reference'], (string) ($data['reference'] ?? $paystackReference));

        if ($verifiedStatus === 'success' && $verifiedAmount === $expectedAmount && $referenceMatches) {
            $pdo = db();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('UPDATE payments SET status = "successful", paid_at = NOW() WHERE order_id = ?');
            $stmt->execute([$order['order_id']]);
            $stmt = $pdo->prepare('UPDATE orders SET payment_status = "paid" WHERE order_id = ?');
            $stmt->execute([$order['order_id']]);
            $pdo->commit();
            $order['payment_status'] = 'paid';
            $order['gateway_status'] = 'successful';
            $order['paid_at'] = date('Y-m-d H:i:s');
        } else {
            $stmt = db()->prepare('UPDATE payments SET status = ? WHERE order_id = ?');
            $stmt->execute([$verifiedStatus === 'reversed' ? 'reversed' : 'failed', $order['order_id']]);
            $order['gateway_status'] = $verifiedStatus === 'reversed' ? 'reversed' : 'failed';
        }
    } catch (Throwable $exception) {
        flash('Payment verification failed: ' . $exception->getMessage(), 'danger');
    }
}

page_header('Payment Result', 'receipt');
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="bg-white border rounded-2 p-4 receipt-box">
                <h1 class="h3"><?= $order['payment_status'] === 'paid' ? 'Payment confirmed' : 'Payment not completed' ?></h1>
                <p class="text-muted">Keep this reference for collection and follow-up.</p>
                <dl class="row">
                    <dt class="col-sm-4">Order reference</dt>
                    <dd class="col-sm-8"><?= h($order['order_reference']) ?></dd>
                    <dt class="col-sm-4">Payment reference</dt>
                    <dd class="col-sm-8"><?= h($order['payment_reference']) ?></dd>
                    <dt class="col-sm-4">Student</dt>
                    <dd class="col-sm-8"><?= h($order['full_name']) ?></dd>
                    <dt class="col-sm-4">Handout</dt>
                    <dd class="col-sm-8"><?= h($order['course_code_snapshot'] . ' - ' . $order['handout_title_snapshot']) ?></dd>
                    <dt class="col-sm-4">Amount</dt>
                    <dd class="col-sm-8"><?= money($order['price_snapshot']) ?></dd>
                    <dt class="col-sm-4">Payment status</dt>
                    <dd class="col-sm-8"><?= status_badge($order['payment_status']) ?></dd>
                    <dt class="col-sm-4">Collection status</dt>
                    <dd class="col-sm-8"><?= status_badge($order['collection_status']) ?></dd>
                </dl>
                <?php if ($order['payment_status'] === 'paid'): ?>
                    <div class="alert alert-success mb-0">Your order has been recorded. Bring your student ID and order reference when collecting the physical handout.</div>
                <?php else: ?>
                    <a class="btn btn-primary" href="/Handout%20Payment%20System/payment.php?order=<?= urlencode($order['order_reference']) ?>">Try payment again</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php page_footer(); ?>
