<?php

declare(strict_types=1);

function current_admin(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM admins WHERE admin_id = ? AND status = "active"');
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: null;
}

function require_admin(): array
{
    $admin = current_admin();
    if (!$admin) {
        redirect('/Handout%20Payment%20System/admin/login.php');
    }

    return $admin;
}

function is_super_admin(?array $admin = null): bool
{
    $admin = $admin ?? current_admin();
    return ($admin['role'] ?? '') === 'super_admin';
}

function manageable_courses_for_admin(array $admin): array
{
    if (is_super_admin($admin)) {
        return db()->query('SELECT c.*, d.name AS department_name, d.code AS department_code, l.name AS level_name
            FROM courses c
            JOIN departments d ON d.department_id = c.department_id
            JOIN academic_levels l ON l.level_id = c.level_id
            WHERE c.status = "active"
            ORDER BY d.name, l.sort_order, c.course_code')->fetchAll();
    }

    $stmt = db()->prepare('SELECT c.*, d.name AS department_name, d.code AS department_code, l.name AS level_name
        FROM admin_course_assignments aca
        JOIN courses c ON c.course_id = aca.course_id
        JOIN departments d ON d.department_id = c.department_id
        JOIN academic_levels l ON l.level_id = c.level_id
        WHERE aca.admin_id = ? AND c.status = "active"
        ORDER BY d.name, l.sort_order, c.course_code');
    $stmt->execute([(int) $admin['admin_id']]);
    return $stmt->fetchAll();
}

function manageable_course_for_admin(array $admin, int $courseId): ?array
{
    if ($courseId <= 0) {
        return null;
    }

    if (is_super_admin($admin)) {
        $stmt = db()->prepare('SELECT c.*, d.name AS department_name, d.code AS department_code, l.name AS level_name
            FROM courses c
            JOIN departments d ON d.department_id = c.department_id
            JOIN academic_levels l ON l.level_id = c.level_id
            WHERE c.course_id = ? AND c.status = "active"');
        $stmt->execute([$courseId]);
        return $stmt->fetch() ?: null;
    }

    $stmt = db()->prepare('SELECT c.*, d.name AS department_name, d.code AS department_code, l.name AS level_name
        FROM admin_course_assignments aca
        JOIN courses c ON c.course_id = aca.course_id
        JOIN departments d ON d.department_id = c.department_id
        JOIN academic_levels l ON l.level_id = c.level_id
        WHERE aca.admin_id = ? AND c.course_id = ? AND c.status = "active"');
    $stmt->execute([(int) $admin['admin_id'], $courseId]);
    return $stmt->fetch() ?: null;
}

function require_super_admin(): array
{
    $admin = require_admin();
    if (!is_super_admin($admin)) {
        flash('Super admin access is required.', 'warning');
        redirect('/Handout%20Payment%20System/admin/dashboard.php');
    }

    return $admin;
}

function login_admin(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM admins WHERE email = ? AND status = "active" LIMIT 1');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        return false;
    }

    $_SESSION['admin_id'] = (int) $admin['admin_id'];
    return true;
}

function ensure_password_reset_table(): void
{
    db()->exec('CREATE TABLE IF NOT EXISTS admin_password_resets (
        reset_id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        token_hash CHAR(64) NOT NULL UNIQUE,
        code_hash VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        used_at DATETIME NULL,
        attempts INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_password_resets_admin_runtime FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE
    )');
}

function request_course_rep_password_reset(string $email): void
{
    ensure_password_reset_table();

    $stmt = db()->prepare('SELECT * FROM admins WHERE email = ? AND role = "course_rep" AND status = "active" LIMIT 1');
    $stmt->execute([strtolower(trim($email))]);
    $admin = $stmt->fetch();

    if (!$admin) {
        return;
    }

    $token = bin2hex(random_bytes(32));
    $code = (string) random_int(100000, 999999);
    $tokenHash = hash('sha256', $token);
    $codeHash = password_hash($code, PASSWORD_DEFAULT);
    $expiresAt = date('Y-m-d H:i:s', time() + 30 * 60);

    $pdo = db();
    $stmt = $pdo->prepare('UPDATE admin_password_resets SET used_at = NOW() WHERE admin_id = ? AND used_at IS NULL');
    $stmt->execute([(int) $admin['admin_id']]);

    $stmt = $pdo->prepare('INSERT INTO admin_password_resets (admin_id, token_hash, code_hash, expires_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([(int) $admin['admin_id'], $tokenHash, $codeHash, $expiresAt]);

    $resetLink = app_base_url() . '/admin/reset-password.php?token=' . urlencode($token);
    $body = 'Hello ' . $admin['name'] . ',' . PHP_EOL . PHP_EOL
        . 'A password reset was requested for your HandoutPay course representative account.' . PHP_EOL
        . 'Open this link: ' . $resetLink . PHP_EOL
        . 'Enter this verification code: ' . $code . PHP_EOL . PHP_EOL
        . 'The link and code expire in 30 minutes. If you did not request this, ignore this email and contact the super admin.';

    send_app_email($admin['email'], 'HandoutPay password reset', $body);
}

function reset_course_rep_password(string $token, string $code, string $password): bool
{
    ensure_password_reset_table();

    if ($token === '' || $code === '' || strlen($password) < 8) {
        return false;
    }

    $tokenHash = hash('sha256', $token);
    $stmt = db()->prepare('SELECT r.*, a.status, a.role
        FROM admin_password_resets r
        JOIN admins a ON a.admin_id = r.admin_id
        WHERE r.token_hash = ?
            AND r.used_at IS NULL
            AND r.expires_at > NOW()
            AND r.attempts < 5
            AND a.status = "active"
            AND a.role = "course_rep"
        LIMIT 1');
    $stmt->execute([$tokenHash]);
    $reset = $stmt->fetch();

    if (!$reset) {
        return false;
    }

    if (!password_verify($code, $reset['code_hash'])) {
        $stmt = db()->prepare('UPDATE admin_password_resets SET attempts = attempts + 1 WHERE reset_id = ?');
        $stmt->execute([(int) $reset['reset_id']]);
        return false;
    }

    $pdo = db();
    $pdo->beginTransaction();
    try {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE admin_id = ? AND role = "course_rep"');
        $stmt->execute([$passwordHash, (int) $reset['admin_id']]);

        $stmt = $pdo->prepare('UPDATE admin_password_resets SET used_at = NOW() WHERE admin_id = ? AND used_at IS NULL');
        $stmt->execute([(int) $reset['admin_id']]);

        $pdo->commit();
        return true;
    } catch (Throwable $exception) {
        $pdo->rollBack();
        return false;
    }
}

function logout_admin(): void
{
    unset($_SESSION['admin_id']);
}
