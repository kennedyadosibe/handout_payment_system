<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/layout.php';

$handoutId = (int) ($_GET['handout_id'] ?? $_POST['handout_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM handouts WHERE handout_id = ? AND status = "available"');
$stmt->execute([$handoutId]);
$handout = $stmt->fetch();

if (!$handout) {
    flash('The selected handout is not available.', 'warning');
    redirect('/Handout%20Payment%20System/handouts.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $required = ['full_name', 'index_number', 'email', 'phone', 'programme', 'level'];
    foreach ($required as $field) {
        if (trim($_POST[$field] ?? '') === '') {
            flash('Please complete all required fields.', 'danger');
            redirect('/Handout%20Payment%20System/order.php?handout_id=' . $handoutId);
        }
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('INSERT INTO students (full_name, index_number, email, phone, programme, level, class_group)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), email = VALUES(email), phone = VALUES(phone),
            programme = VALUES(programme), level = VALUES(level), class_group = VALUES(class_group)');
        $stmt->execute([
            trim($_POST['full_name']),
            trim($_POST['index_number']),
            trim($_POST['email']),
            trim($_POST['phone']),
            trim($_POST['programme']),
            trim($_POST['level']),
            trim($_POST['class_group'] ?? ''),
        ]);

        $stmt = $pdo->prepare('SELECT student_id FROM students WHERE index_number = ?');
        $stmt->execute([trim($_POST['index_number'])]);
        $studentId = (int) $stmt->fetchColumn();

        $orderReference = reference_code();
        $stmt = $pdo->prepare('INSERT INTO orders
            (order_reference, student_id, handout_id, handout_title_snapshot, course_code_snapshot, price_snapshot)
            VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $orderReference,
            $studentId,
            $handout['handout_id'],
            $handout['title'],
            $handout['course_code'],
            $handout['current_price'],
        ]);
        $orderId = (int) $pdo->lastInsertId();

        $paymentReference = reference_code('PAY');
        $stmt = $pdo->prepare('INSERT INTO payments (order_id, reference, amount, provider, status) VALUES (?, ?, ?, ?, "initialized")');
        $stmt->execute([$orderId, $paymentReference, $handout['current_price'], PAYMENT_PROVIDER]);

        $pdo->commit();
        redirect('/Handout%20Payment%20System/payment.php?order=' . urlencode($orderReference));
    } catch (Throwable $exception) {
        $pdo->rollBack();
        flash('Order could not be created. Please try again.', 'danger');
        redirect('/Handout%20Payment%20System/order.php?handout_id=' . $handoutId);
    }
}

page_header('Order Handout', 'handouts');
?>
<main class="container py-5">
    <div class="row g-4">
        <div class="col-lg-5">
            <div class="bg-white p-4 rounded-2 border">
                <span class="badge text-bg-secondary"><?= h($handout['course_code']) ?></span>
                <h1 class="h3 mt-3"><?= h($handout['title']) ?></h1>
                <p class="text-muted"><?= h($handout['description']) ?></p>
                <div class="price-pill d-inline-block"><?= money($handout['current_price']) ?></div>
                <p class="small text-muted mt-3 mb-0">The price is loaded from the handout record and saved on your order as a price snapshot.</p>
            </div>
        </div>
        <div class="col-lg-7">
            <form method="post" class="bg-white border rounded-2 p-4">
                <input type="hidden" name="handout_id" value="<?= (int) $handout['handout_id'] ?>">
                <h2 class="h4 mb-3">Student details</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="full_name">Full name</label>
                        <input class="form-control" id="full_name" name="full_name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="index_number">Student ID / Index number</label>
                        <input class="form-control" id="index_number" name="index_number" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" type="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="phone">Phone</label>
                        <input class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="programme">Programme</label>
                        <input class="form-control" id="programme" name="programme" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="level">Level</label>
                        <input class="form-control" id="level" name="level" placeholder="300" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="class_group">Class</label>
                        <input class="form-control" id="class_group" name="class_group" placeholder="A">
                    </div>
                </div>
                <button class="btn btn-primary btn-lg w-100 mt-4" type="submit">Continue to payment</button>
            </form>
        </div>
    </div>
</main>
<?php page_footer(); ?>
