<?php

declare(strict_types=1);

function page_header(string $title, string $active = ''): void
{
    $admin = current_admin();
    $flash = flash();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= h($title) ?> | Student Handout Payment System</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="/Handout%20Payment%20System/assets/css/styles.css" rel="stylesheet">
    </head>
    <body>
    <nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand brand-logo" href="/Handout%20Payment%20System/" aria-label="HandoutPay home">
                <span class="brand-mark" aria-hidden="true">
                    <span class="brand-book"></span>
                    <span class="brand-spark"></span>
                </span>
                <span class="brand-word">HandoutPay</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link <?= $active === 'home' ? 'active' : '' ?>" href="/Handout%20Payment%20System/">Home</a></li>
                    <li class="nav-item"><a class="nav-link <?= $active === 'handouts' ? 'active' : '' ?>" href="/Handout%20Payment%20System/handouts.php">Handouts</a></li>
                    <li class="nav-item"><a class="nav-link <?= $active === 'receipt' ? 'active' : '' ?>" href="/Handout%20Payment%20System/receipt.php">Receipt Lookup</a></li>
                </ul>
                <div class="d-flex gap-2">
                    <?php if ($admin): ?>
                        <a class="btn btn-outline-primary btn-sm" href="/Handout%20Payment%20System/admin/dashboard.php">Dashboard</a>
                        <a class="btn btn-primary btn-sm" href="/Handout%20Payment%20System/admin/logout.php">Logout</a>
                    <?php else: ?>
                        <a class="btn btn-primary btn-sm" href="/Handout%20Payment%20System/admin/login.php">Admin Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <?php if ($flash): ?>
        <div class="container mt-3">
            <div class="alert alert-<?= h($flash['type']) ?> mb-0"><?= h($flash['message']) ?></div>
        </div>
    <?php endif; ?>
    <?php
}

function page_footer(): void
{
    ?>
    <footer class="border-top mt-5 py-4 bg-white">
        <div class="container d-flex flex-column flex-md-row justify-content-between gap-2 small text-muted">
            <span>Student Handout Payment and Ordering System</span>
            <span>Physical copy ordering, payment records and collection tracking.</span>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}
