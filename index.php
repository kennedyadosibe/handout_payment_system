<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/layout.php';

$pdo = db();
$totalHandouts = (int) $pdo->query('SELECT COUNT(*) FROM handouts')->fetchColumn();
$availableHandouts = (int) $pdo->query('SELECT COUNT(*) FROM handouts WHERE status = "available"')->fetchColumn();
$paidOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE payment_status = "paid"')->fetchColumn();
$stmt = $pdo->query('SELECT h.*, c.title AS campus_course_title, d.name AS department_name, l.name AS level_name
    FROM handouts h
    LEFT JOIN courses c ON c.course_id = h.course_id
    LEFT JOIN departments d ON d.department_id = h.department_id
    LEFT JOIN academic_levels l ON l.level_id = h.level_id
    WHERE h.status = "available"
    ORDER BY h.created_at DESC
    LIMIT 3');
$featured = $stmt->fetchAll();

page_header('Home', 'home');
?>
<section class="hero">
    <div class="container py-5">
        <span class="badge text-bg-light mb-3">Physical handout ordering</span>
        <h1 class="display-5">Order course handouts, pay the correct amount, and collect your copy with proof.</h1>
        <p class="lead mt-3">Students choose an available handout and the system calculates the price from the handout record. Course representatives manage payments, copies and collection from one dashboard.</p>
        <div class="d-flex gap-2 flex-wrap mt-4">
            <a class="btn btn-light btn-lg" href="/Handout%20Payment%20System/handouts.php">Browse Handouts</a>
            <a class="btn btn-outline-light btn-lg" href="/Handout%20Payment%20System/receipt.php">Find Receipt</a>
        </div>
    </div>
</section>

<main class="container py-5">
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-2 stat">
                <div class="text-muted small">Total handouts</div>
                <div class="h2 mb-0"><?= $totalHandouts ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-2 stat">
                <div class="text-muted small">Available now</div>
                <div class="h2 mb-0"><?= $availableHandouts ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-2 stat">
                <div class="text-muted small">Paid orders recorded</div>
                <div class="h2 mb-0"><?= $paidOrders ?></div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h4 mb-0">Available handouts</h2>
        <a href="/Handout%20Payment%20System/handouts.php" class="btn btn-outline-primary btn-sm">View all</a>
    </div>
    <div class="row g-4">
        <?php foreach ($featured as $handout): ?>
            <div class="col-md-4">
                <div class="card handout-card border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="badge text-bg-secondary"><?= h($handout['course_code']) ?></span>
                            <span class="price-pill"><?= money($handout['current_price']) ?></span>
                        </div>
                        <h3 class="h5 mt-3"><?= h($handout['title']) ?></h3>
                        <div class="text-muted small mb-2">
                            <?= h($handout['department_name'] ?? 'Department not set') ?> · <?= h($handout['level_name'] ?? 'Level not set') ?>
                            <?php if (!empty($handout['campus_course_title'])): ?>
                                <br><?= h($handout['campus_course_title']) ?>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted"><?= h($handout['description']) ?></p>
                        <a class="btn btn-primary w-100" href="/Handout%20Payment%20System/order.php?handout_id=<?= (int) $handout['handout_id'] ?>">Order</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>
<?php page_footer(); ?>
