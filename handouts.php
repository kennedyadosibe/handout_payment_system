<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/layout.php';

$stmt = db()->query('SELECT * FROM handouts WHERE status = "available" ORDER BY course_code, title');
$handouts = $stmt->fetchAll();

page_header('Available Handouts', 'handouts');
?>
<main class="container py-5">
    <div class="mb-4">
        <h1 class="h2">Available handouts</h1>
        <p class="text-muted">Choose a handout to create an order. The amount is calculated by the system from the selected handout.</p>
    </div>
    <div class="row g-4">
        <?php foreach ($handouts as $handout): ?>
            <div class="col-md-6 col-xl-4">
                <div class="card handout-card border-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between gap-2">
                            <span class="badge text-bg-secondary"><?= h($handout['course_code']) ?></span>
                            <span class="price-pill"><?= money($handout['current_price']) ?></span>
                        </div>
                        <h2 class="h5 mt-3"><?= h($handout['title']) ?></h2>
                        <p class="text-muted flex-grow-1"><?= h($handout['description']) ?></p>
                        <a class="btn btn-primary" href="/Handout%20Payment%20System/order.php?handout_id=<?= (int) $handout['handout_id'] ?>">Order handout</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (!$handouts): ?>
        <div class="alert alert-info">No handouts are available right now.</div>
    <?php endif; ?>
</main>
<?php page_footer(); ?>
