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

function logout_admin(): void
{
    unset($_SESSION['admin_id']);
}
