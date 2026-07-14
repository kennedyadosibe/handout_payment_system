<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

if (current_admin()) {
    redirect('/Handout%20Payment%20System/admin/dashboard.php');
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = preg_replace('/\D+/', '', $_POST['code'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($password !== $confirmPassword) {
        flash('Passwords do not match.', 'danger');
        redirect('/Handout%20Payment%20System/admin/reset-password.php?token=' . urlencode($token));
    }

    if (strlen($password) < 8) {
        flash('Use at least 8 characters for the new password.', 'danger');
        redirect('/Handout%20Payment%20System/admin/reset-password.php?token=' . urlencode($token));
    }

    if (reset_course_rep_password($token, $code, $password)) {
        flash('Password reset successful. You can now log in.', 'success');
        redirect('/Handout%20Payment%20System/admin/login.php');
    }

    flash('The reset link or verification code is invalid, expired, or locked after too many attempts.', 'danger');
    redirect('/Handout%20Payment%20System/admin/reset-password.php?token=' . urlencode($token));
}

page_header('Reset Password');
?>
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <form method="post" class="bg-white border rounded-2 p-4">
                <h1 class="h3">Reset password</h1>
                <p class="text-muted">Use the link from your email and enter the separate verification code before choosing a new password.</p>
                <input type="hidden" name="token" value="<?= h($token) ?>">
                <div class="mb-3">
                    <label class="form-label" for="code">Verification code</label>
                    <input class="form-control" id="code" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">New password</label>
                    <input class="form-control" id="password" type="password" name="password" autocomplete="new-password" minlength="8" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="confirm_password">Confirm new password</label>
                    <input class="form-control" id="confirm_password" type="password" name="confirm_password" autocomplete="new-password" minlength="8" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Reset password</button>
                <a class="btn btn-link w-100 mt-2" href="/Handout%20Payment%20System/admin/forgot-password.php">Request a new link</a>
            </form>
        </div>
    </div>
</main>
<?php page_footer(); ?>
