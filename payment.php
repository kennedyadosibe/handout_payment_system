<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/layout.php';
require_once __DIR__ . '/app/paystack.php';

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
    try {
        $response = paystack_initialize_transaction($order);
        $authorizationUrl = $response['data']['authorization_url'] ?? '';

        if ($authorizationUrl === '') {
            throw new RuntimeException('Paystack did not return a checkout URL.');
        }

        redirect($authorizationUrl);
    } catch (Throwable $exception) {
        flash('Payment could not start: ' . $exception->getMessage(), 'danger');
        redirect('/Handout%20Payment%20System/payment.php?order=' . urlencode($order['order_reference']));
    }
}

page_header('Payment', 'handouts');
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="bg-white border rounded-2 p-4">
                <h1 class="h3">Pay with Paystack</h1>
                <p class="text-muted">Confirm your order details, then continue to Paystack to complete payment securely.</p>
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
                <?php if (paystack_configured()): ?>
                    <form method="post">
                        <button class="btn btn-primary btn-lg w-100" type="submit">Continue to Paystack</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">Paystack is not configured. Add your secret key to <code>config/payment.local.php</code>.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<?php page_footer(); ?>
