<?php

require_once __DIR__ . '/../../app/bootstrap.php';
require_once __DIR__ . '/../../app/layout.php';

require_admin();
$handouts = db()->query('SELECT h.*, COUNT(o.order_id) AS order_count
    FROM handouts h
    LEFT JOIN orders o ON o.handout_id = h.handout_id
    GROUP BY h.handout_id
    ORDER BY h.created_at DESC')->fetchAll();

page_header('Manage Handouts');
?>
<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 mb-0">Manage handouts</h1>
        <a class="btn btn-primary" href="/Handout%20Payment%20System/admin/handouts/edit.php">Add handout</a>
    </div>
    <div class="bg-white border rounded-2 p-4">
        <div class="table-responsive">
            <table class="table align-middle">
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
                    <?php foreach ($handouts as $handout): ?>
                        <tr>
                            <td><?= h($handout['course_code']) ?></td>
                            <td><?= h($handout['title']) ?></td>
                            <td><?= money($handout['current_price']) ?></td>
                            <td><?= status_badge($handout['status']) ?></td>
                            <td><?= (int) $handout['order_count'] ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="/Handout%20Payment%20System/admin/handouts/edit.php?id=<?= (int) $handout['handout_id'] ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php page_footer(); ?>
