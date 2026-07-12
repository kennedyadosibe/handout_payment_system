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
