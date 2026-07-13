<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/payment.php';

$connectionError = null;
try {
    $serverDsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
    $pdo = new PDO($serverDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $exception) {
    $connectionError = $exception->getMessage();
}

if ($connectionError !== null) {
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Setup Needs Database Access</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <main class="container py-5">
        <div class="bg-white border rounded-3 p-4 mx-auto" style="max-width: 760px;">
            <h1 class="h3">Database connection failed</h1>
            <p class="text-muted">Update the database settings in <code>config/database.php</code>, then reload this setup page.</p>
            <div class="alert alert-warning mb-0"><?= htmlspecialchars($connectionError, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </main>
    </body>
    </html>
    <?php
    exit;
}

$schema = file_get_contents(__DIR__ . '/database/schema.sql');
$pdo->exec($schema);
$db = db();

function column_exists(PDO $db, string $table, string $column): bool
{
    $stmt = $db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    $stmt->execute([$table, $column]);
    return (int) $stmt->fetchColumn() > 0;
}

$db->exec("ALTER TABLE orders MODIFY payment_status ENUM('not_paid', 'paid', 'pending_payment', 'cancelled', 'payment_failed') NOT NULL DEFAULT 'not_paid'");
$db->exec("UPDATE orders SET payment_status = 'not_paid' WHERE payment_status IN ('pending_payment', 'cancelled', 'payment_failed')");
$db->exec("ALTER TABLE orders MODIFY payment_status ENUM('not_paid', 'paid') NOT NULL DEFAULT 'not_paid'");
$db->exec("ALTER TABLE admins MODIFY role ENUM('super_admin', 'course_rep') NOT NULL DEFAULT 'course_rep'");
if (!column_exists($db, 'admins', 'department_id')) {
    $db->exec('ALTER TABLE admins ADD department_id INT NULL AFTER status');
}
if (!column_exists($db, 'admins', 'level_id')) {
    $db->exec('ALTER TABLE admins ADD level_id INT NULL AFTER department_id');
}
if (!column_exists($db, 'handouts', 'department_id')) {
    $db->exec('ALTER TABLE handouts ADD department_id INT NULL AFTER handout_id');
}
if (!column_exists($db, 'handouts', 'level_id')) {
    $db->exec('ALTER TABLE handouts ADD level_id INT NULL AFTER department_id');
}
if (!column_exists($db, 'handouts', 'course_id')) {
    $db->exec('ALTER TABLE handouts ADD course_id INT NULL AFTER level_id');
}

$stmt = $db->prepare('INSERT INTO departments (name, code)
    SELECT ?, ?
    WHERE NOT EXISTS (SELECT 1 FROM departments WHERE code = ?)');
$stmt->execute(['Computer Science', 'CS', 'CS']);

$levels = [
    ['Level 100', 100],
    ['Level 200', 200],
    ['Level 300', 300],
    ['Level 400', 400],
];
$stmt = $db->prepare('INSERT INTO academic_levels (name, sort_order)
    SELECT ?, ?
    WHERE NOT EXISTS (SELECT 1 FROM academic_levels WHERE name = ?)');
foreach ($levels as $level) {
    $stmt->execute([$level[0], $level[1], $level[0]]);
}

$departmentId = (int) $db->query("SELECT department_id FROM departments WHERE code = 'CS' LIMIT 1")->fetchColumn();
$levelId = (int) $db->query("SELECT level_id FROM academic_levels WHERE name = 'Level 200' LIMIT 1")->fetchColumn();

$handouts = [
    ['Database Systems', 'H001', 'Relational models, SQL design, normalization and transaction concepts.', 40.00],
    ['Computer Networking', 'H002', 'Network models, addressing, routing basics and practical configuration notes.', 50.00],
    ['Java Programming', 'H003', 'Core Java syntax, object-oriented programming and practice exercises.', 35.00],
];

$stmt = $db->prepare('INSERT INTO handouts (title, course_code, description, current_price, status, created_by)
    SELECT ?, ?, ?, ?, "available", 1
    WHERE NOT EXISTS (SELECT 1 FROM handouts WHERE course_code = ?)');
foreach ($handouts as $handout) {
    $stmt->execute([$handout[0], $handout[1], $handout[2], $handout[3], $handout[1]]);
}

$stmt = $db->prepare('INSERT INTO courses (department_id, level_id, course_code, title)
    SELECT ?, ?, ?, ?
    WHERE NOT EXISTS (
        SELECT 1 FROM courses WHERE department_id = ? AND level_id = ? AND course_code = ?
    )');
foreach ($handouts as $handout) {
    $stmt->execute([$departmentId, $levelId, $handout[1], $handout[0], $departmentId, $levelId, $handout[1]]);
}

$stmt = $db->prepare('UPDATE handouts h
    JOIN courses c ON c.course_code = h.course_code
    SET h.department_id = c.department_id, h.level_id = c.level_id, h.course_id = c.course_id
    WHERE h.department_id IS NULL AND h.level_id IS NULL AND h.course_id IS NULL');
$stmt->execute();

$superAdminHash = password_hash('change-me-super-admin', PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO admins (name, email, password_hash, role, status)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role = VALUES(role), status = VALUES(status), department_id = NULL, level_id = NULL');
$stmt->execute(['Super Admin', 'super.user@example.test', $superAdminHash, 'super_admin', 'active']);

$courseRepHash = password_hash('change-me-course-rep', PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO admins (name, email, password_hash, role, status, department_id, level_id)
    VALUES (?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE name = VALUES(name), password_hash = VALUES(password_hash), role = VALUES(role), status = VALUES(status), department_id = VALUES(department_id), level_id = VALUES(level_id)');
$stmt->execute(['Course Representative', 'course.rep@example.test', $courseRepHash, 'course_rep', 'active', $departmentId, $levelId]);

$courseRepId = (int) $db->query("SELECT admin_id FROM admins WHERE email = 'course.rep@example.test' LIMIT 1")->fetchColumn();
$stmt = $db->prepare('DELETE FROM admin_course_assignments WHERE admin_id = ?');
$stmt->execute([$courseRepId]);
$stmt = $db->prepare('INSERT INTO admin_course_assignments (admin_id, course_id)
    SELECT ?, course_id FROM courses WHERE department_id = ? AND level_id = ?');
$stmt->execute([$courseRepId, $departmentId, $levelId]);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup Complete</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5">
    <div class="setup-card bg-white border rounded-3 p-4 mx-auto" style="max-width: 720px;">
        <h1 class="h3">Setup complete</h1>
        <p class="text-muted">The database, campus foundation, sample handouts and local admin accounts are ready.</p>
        <div class="alert alert-info">
            <strong>Super admin:</strong> super.user@example.test / change-me-super-admin<br>
            <strong>Course rep:</strong> course.rep@example.test / change-me-course-rep
        </div>
        <a class="btn btn-primary" href="/Handout%20Payment%20System/">Open Website</a>
        <a class="btn btn-outline-primary" href="/Handout%20Payment%20System/admin/login.php">Admin Login</a>
    </div>
</main>
</body>
</html>
