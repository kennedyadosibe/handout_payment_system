<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

if (current_admin()) {
    redirect('/Handout%20Payment%20System/admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login_admin(trim($_POST['email'] ?? ''), $_POST['password'] ?? '')) {
        redirect('/Handout%20Payment%20System/admin/dashboard.php');
    }
    flash('Invalid login details.', 'danger');
    redirect('/Handout%20Payment%20System/admin/login.php');
}

page_header('Admin Login');
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <form method="post" class="bg-white border rounded-2 p-4">
                <h1 class="h3">Admin login</h1>
                <p class="text-muted">Default local account: course.rep@example.test / change-me-course-rep</p>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" type="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <input class="form-control" id="password" type="password" name="password" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Login</button>
            </form>
        </div>
    </div>
</main>
<?php page_footer(); ?>
