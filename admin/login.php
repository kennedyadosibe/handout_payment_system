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
                <p class="text-muted">Super admin: super.user@example.test. Course rep: course.rep@example.test.</p>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input class="form-control" id="email" type="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group password-toggle-group">
                        <input class="form-control" id="password" type="password" name="password" autocomplete="current-password" required>
                        <button class="btn btn-outline-primary password-toggle" type="button" data-password-toggle aria-controls="password" aria-label="Show password">
                            <span aria-hidden="true" data-password-toggle-icon>Show</span>
                        </button>
                    </div>
                </div>
                <button class="btn btn-primary w-100" type="submit">Login</button>
            </form>
        </div>
    </div>
</main>
<script>
document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
    button.addEventListener('click', function () {
        var input = document.getElementById(button.getAttribute('aria-controls'));
        var icon = button.querySelector('[data-password-toggle-icon]');
        if (!input) {
            return;
        }

        var shouldShow = input.type === 'password';
        input.type = shouldShow ? 'text' : 'password';
        button.setAttribute('aria-label', shouldShow ? 'Hide password' : 'Show password');
        if (icon) {
            icon.textContent = shouldShow ? 'Hide' : 'Show';
        }
    });
});
</script>
<?php page_footer(); ?>
