<?php

require_once __DIR__ . '/app/bootstrap.php';
require_once __DIR__ . '/app/layout.php';

$pdo = db();
$departments = $pdo->query('SELECT * FROM departments WHERE status = "active" ORDER BY name')->fetchAll();
$levels = $pdo->query('SELECT * FROM academic_levels WHERE status = "active" ORDER BY sort_order, name')->fetchAll();
$activeDepartments = count($departments);
$activeCourses = (int) $pdo->query('SELECT COUNT(*) FROM courses WHERE status = "active"')->fetchColumn();
$availableHandouts = (int) $pdo->query('SELECT COUNT(*) FROM handouts WHERE status = "available"')->fetchColumn();

page_header('Home', 'home');
?>
<section class="hero">
    <div class="container py-5">
        <span class="badge text-bg-light mb-3">Physical handout ordering</span>
        <h1 class="display-5">Find handouts for your class, pay the correct amount, and collect with proof.</h1>
        <p class="lead mt-3">Start by selecting your department and level. The system only shows handouts for that class, then calculates the price from the official handout record.</p>
        <div class="d-flex gap-2 flex-wrap mt-4">
            <a class="btn btn-light btn-lg" href="#class-finder">Find Class Handouts</a>
            <a class="btn btn-outline-light btn-lg" href="/Handout%20Payment%20System/receipt.php">Find Receipt</a>
        </div>
    </div>
</section>

<main class="container py-5">
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-2 stat">
                <div class="text-muted small">Departments</div>
                <div class="h2 mb-0"><?= $activeDepartments ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-2 stat">
                <div class="text-muted small">Active courses</div>
                <div class="h2 mb-0"><?= $activeCourses ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white p-4 rounded-2 stat">
                <div class="text-muted small">Available handouts</div>
                <div class="h2 mb-0"><?= $availableHandouts ?></div>
            </div>
        </div>
    </div>

    <section id="class-finder" class="bg-white border rounded-2 p-4 mb-5">
        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <h2 class="h4">Find your class handouts</h2>
                <p class="text-muted mb-0">Choose your department and level first. If your course rep has published handouts for that class, they will appear on the next page.</p>
            </div>
            <div class="col-lg-7">
                <form class="row g-3 align-items-end" action="/Handout%20Payment%20System/handouts.php" method="get">
                    <div class="col-md-5">
                        <label class="form-label" for="home_department_id">Department</label>
                        <select class="form-select" id="home_department_id" name="department_id" required>
                            <option value="">Select department</option>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?= (int) $department['department_id'] ?>"><?= h($department['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="home_level_id">Level</label>
                        <select class="form-select" id="home_level_id" name="level_id" required>
                            <option value="">Select level</option>
                            <?php foreach ($levels as $level): ?>
                                <option value="<?= (int) $level['level_id'] ?>"><?= h($level['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-grid">
                        <button class="btn btn-primary" type="submit">Find handouts</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-3">
        <div>
            <span class="text-primary fw-semibold small">Student flow</span>
            <h2 class="h4 mb-0">How collection works</h2>
        </div>
        <a class="btn btn-outline-primary btn-sm" href="/Handout%20Payment%20System/receipt.php">Check receipt</a>
    </div>

    <div class="row g-4 home-steps">
        <div class="col-md-4">
            <div class="home-step-card h-100">
                <div class="home-step-number">1</div>
                <div>
                    <span class="home-step-label">Step 1</span>
                    <h3 class="h5">Choose your class</h3>
                    <p class="text-muted mb-0">Select your department and level so you only see handouts meant for your class.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="home-step-card h-100">
                <div class="home-step-number">2</div>
                <div>
                    <span class="home-step-label">Step 2</span>
                    <h3 class="h5">Order the handout</h3>
                    <p class="text-muted mb-0">Pick the handout you need and enter your details. The price comes from the course rep's record.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="home-step-card h-100">
                <div class="home-step-number">3</div>
                <div>
                    <span class="home-step-label">Step 3</span>
                    <h3 class="h5">Pay and collect</h3>
                    <p class="text-muted mb-0">Pay through the system, keep your receipt, and show proof when collecting your copy.</p>
                </div>
            </div>
        </div>
    </div>
</main>
<?php page_footer(); ?>
