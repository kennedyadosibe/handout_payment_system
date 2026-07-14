<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

if (current_admin()) {
    redirect('/Handout%20Payment%20System/admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        request_course_rep_password_reset($email);
    }

    flash('If that course representative email exists, a reset link and code have been sent.', 'info');
    redirect('/Handout%20Payment%20System/admin/forgot-password.php');
}

page_header('Forgot Password');
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <form method="post" class="bg-white border rounded-2 p-4">
                <h1 class="h3">Forgot password</h1>
                <p class="text-muted">Enter the email on your course representative account. The reset requires both the emailed link and verification code.</p>
                <div class="mb-3">
                    <label class="form-label" for="email">Course rep email</label>
                    <input class="form-control" id="email" type="email" name="email" autocomplete="email" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Send reset link</button>
                <a class="btn btn-link w-100 mt-2" href="/Handout%20Payment%20System/admin/login.php">Back to login</a>
            </form>
        </div>
    </div>
</main>
<?php page_footer(); ?>
