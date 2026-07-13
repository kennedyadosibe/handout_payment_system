<?php

require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/layout.php';

$admin = require_admin();
$pdo = db();

$dashboardBaseUrl = '/Handout%20Payment%20System/admin/dashboard.php';
$allowedReturnPanels = ['overview', 'revenue', 'paid-students', 'incomplete-details', 'campus-setup', 'manage-handouts', 'edit-handout', 'view-orders'];
$returnPanel = $_POST['return_panel'] ?? 'overview';
if (!in_array($returnPanel, $allowedReturnPanels, true)) {
    $returnPanel = 'overview';
}
$returnUrl = $dashboardBaseUrl . '?panel=' . rawurlencode($returnPanel);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $orderId = (int) ($_POST['order_id'] ?? 0);
    $handoutId = (int) ($_POST['handout_id'] ?? 0);

    if ($action === 'save_department') {
        if (!is_super_admin($admin)) {
            flash('Super admin access is required.', 'warning');
            redirect($dashboardBaseUrl . '?panel=overview');
        }

        $departmentName = trim($_POST['department_name'] ?? '');
        $departmentCode = strtoupper(trim($_POST['department_code'] ?? ''));

        if ($departmentName === '' || $departmentCode === '') {
            flash('Department name and code are required.', 'danger');
            redirect($dashboardBaseUrl . '?panel=campus-setup');
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO departments (name, code) VALUES (?, ?)');
            $stmt->execute([$departmentName, $departmentCode]);
            flash('Department added.');
        } catch (Throwable $exception) {
            flash('Department could not be added. Check that the code is unique.', 'danger');
        }

        redirect($dashboardBaseUrl . '?panel=campus-setup');
    }

    if ($action === 'save_level') {
        if (!is_super_admin($admin)) {
            flash('Super admin access is required.', 'warning');
            redirect($dashboardBaseUrl . '?panel=overview');
        }

        $levelName = trim($_POST['level_name'] ?? '');
        $sortOrder = (int) ($_POST['sort_order'] ?? 0);

        if ($levelName === '') {
            flash('Level name is required.', 'danger');
            redirect($dashboardBaseUrl . '?panel=campus-setup');
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO academic_levels (name, sort_order) VALUES (?, ?)');
            $stmt->execute([$levelName, $sortOrder]);
            flash('Level added.');
        } catch (Throwable $exception) {
            flash('Level could not be added. Check that the name is unique.', 'danger');
        }

        redirect($dashboardBaseUrl . '?panel=campus-setup');
    }

    if ($action === 'save_course') {
        if (!is_super_admin($admin)) {
            flash('Super admin access is required.', 'warning');
            redirect($dashboardBaseUrl . '?panel=overview');
        }

        $departmentId = (int) ($_POST['department_id'] ?? 0);
        $levelId = (int) ($_POST['level_id'] ?? 0);
        $courseCode = strtoupper(trim($_POST['course_code'] ?? ''));
        $courseTitle = trim($_POST['course_title'] ?? '');

        if ($departmentId <= 0 || $levelId <= 0 || $courseCode === '' || $courseTitle === '') {
            flash('Department, level, course code, and course title are required.', 'danger');
            redirect($dashboardBaseUrl . '?panel=campus-setup');
        }

        try {
            $stmt = $pdo->prepare('INSERT INTO courses (department_id, level_id, course_code, title) VALUES (?, ?, ?, ?)');
            $stmt->execute([$departmentId, $levelId, $courseCode, $courseTitle]);
            flash('Course added.');
        } catch (Throwable $exception) {
            flash('Course could not be added. Check that it is not already created for that department and level.', 'danger');
        }

        redirect($dashboardBaseUrl . '?panel=campus-setup');
    }

    if ($action === 'save_course_rep') {
        if (!is_super_admin($admin)) {
            flash('Super admin access is required.', 'warning');
            redirect($dashboardBaseUrl . '?panel=overview');
        }

        $repId = (int) ($_POST['rep_id'] ?? 0);
        $repName = trim($_POST['rep_name'] ?? '');
        $repEmail = strtolower(trim($_POST['rep_email'] ?? ''));
        $repPassword = $_POST['rep_password'] ?? '';
        $repStatus = $_POST['rep_status'] ?? 'active';
        $departmentId = (int) ($_POST['rep_department_id'] ?? 0);
        $levelId = (int) ($_POST['rep_level_id'] ?? 0);
        $courseIds = array_values(array_unique(array_map('intval', $_POST['course_ids'] ?? [])));
        $courseIds = array_filter($courseIds, fn (int $courseId): bool => $courseId > 0);

        if ($repName === '' || !filter_var($repEmail, FILTER_VALIDATE_EMAIL) || ($repId <= 0 && $repPassword === '') || $departmentId <= 0 || $levelId <= 0 || !$courseIds || !in_array($repStatus, ['active', 'inactive'], true)) {
            flash('Rep name, valid email, department, level, status, and at least one course are required. New reps also need a password.', 'danger');
            redirect($dashboardBaseUrl . '?panel=campus-setup');
        }

        if ($repId > 0) {
            $stmt = $pdo->prepare('SELECT admin_id FROM admins WHERE admin_id = ? AND role = "course_rep"');
            $stmt->execute([$repId]);
            if (!$stmt->fetchColumn()) {
                flash('Course representative account not found.', 'warning');
                redirect($dashboardBaseUrl . '?panel=campus-setup');
            }
        }

        $placeholders = implode(',', array_fill(0, count($courseIds), '?'));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM courses WHERE department_id = ? AND level_id = ? AND course_id IN ($placeholders)");
        $stmt->execute([$departmentId, $levelId, ...$courseIds]);
        if ((int) $stmt->fetchColumn() !== count($courseIds)) {
            flash('Selected courses must belong to the chosen department and level.', 'danger');
            redirect($dashboardBaseUrl . '?panel=campus-setup');
        }

        $pdo->beginTransaction();
        try {
            if ($repId > 0) {
                if ($repPassword !== '') {
                    $passwordHash = password_hash($repPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('UPDATE admins SET name = ?, email = ?, password_hash = ?, status = ?, department_id = ?, level_id = ? WHERE admin_id = ? AND role = "course_rep"');
                    $stmt->execute([$repName, $repEmail, $passwordHash, $repStatus, $departmentId, $levelId, $repId]);
                } else {
                    $stmt = $pdo->prepare('UPDATE admins SET name = ?, email = ?, status = ?, department_id = ?, level_id = ? WHERE admin_id = ? AND role = "course_rep"');
                    $stmt->execute([$repName, $repEmail, $repStatus, $departmentId, $levelId, $repId]);
                }

                $stmt = $pdo->prepare('DELETE FROM admin_course_assignments WHERE admin_id = ?');
                $stmt->execute([$repId]);
            } else {
                $passwordHash = password_hash($repPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO admins (name, email, password_hash, role, status, department_id, level_id) VALUES (?, ?, ?, "course_rep", ?, ?, ?)');
                $stmt->execute([$repName, $repEmail, $passwordHash, $repStatus, $departmentId, $levelId]);
                $repId = (int) $pdo->lastInsertId();
            }

            $stmt = $pdo->prepare('INSERT INTO admin_course_assignments (admin_id, course_id) VALUES (?, ?)');
            foreach ($courseIds as $courseId) {
                $stmt->execute([$repId, $courseId]);
            }

            $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$admin['admin_id'], $repId > 0 && (int) ($_POST['rep_id'] ?? 0) > 0 ? 'update course representative account' : 'create course representative account', 'admins', (string) $repId]);

            $pdo->commit();
            flash($repId > 0 && (int) ($_POST['rep_id'] ?? 0) > 0 ? 'Course representative account updated.' : 'Course representative account created and assigned.');
        } catch (Throwable $exception) {
            $pdo->rollBack();
            flash('Course representative could not be saved. Check that the email is not already used.', 'danger');
        }

        redirect($dashboardBaseUrl . '?panel=campus-setup');
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare('SELECT h.*, COUNT(o.order_id) AS order_count
            FROM handouts h
            LEFT JOIN orders o ON o.handout_id = h.handout_id
            WHERE h.handout_id = ?
            GROUP BY h.handout_id');
        $stmt->execute([$handoutId]);
        $handout = $stmt->fetch();

        if (!$handout) {
            flash('Handout not found.', 'warning');
            redirect($returnUrl);
        }
        if (is_super_admin($admin)) {
            flash('Course representatives manage handout availability and prices.', 'warning');
            redirect($returnUrl);
        }
        if (!is_super_admin($admin) && !manageable_course_for_admin($admin, (int) $handout['course_id'])) {
            flash('You can only manage handouts for your assigned courses.', 'warning');
            redirect($returnUrl);
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE p FROM payments p
                JOIN orders o ON o.order_id = p.order_id
                WHERE o.handout_id = ?');
            $stmt->execute([$handoutId]);

            $stmt = $pdo->prepare('DELETE FROM orders WHERE handout_id = ?');
            $stmt->execute([$handoutId]);

            $stmt = $pdo->prepare('DELETE FROM handouts WHERE handout_id = ?');
            $stmt->execute([$handoutId]);

            $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$admin['admin_id'], 'permanently delete handout and related records from dashboard', 'handouts', (string) $handoutId]);

            $pdo->commit();
            flash($handout['course_code'] . ' handout and its related records were deleted completely.');
        } catch (Throwable $exception) {
            $pdo->rollBack();
            flash('Handout could not be deleted completely. Please try again.', 'danger');
        }

        redirect($returnUrl);
    }

    if ($action === 'save_handout') {
        if (is_super_admin($admin)) {
            flash('Course representatives are responsible for adding handouts and setting prices.', 'warning');
            redirect($dashboardBaseUrl . '?panel=campus-setup');
        }

        $title = trim($_POST['title'] ?? '');
        $courseId = (int) ($_POST['course_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $currentPrice = (float) ($_POST['current_price'] ?? 0);
        $status = $_POST['status'] ?? 'available';
        $saveUrl = $dashboardBaseUrl . '?panel=edit-handout';
        if ($handoutId > 0) {
            $saveUrl .= '&handout_id=' . $handoutId;
        }

        if ($title === '' || $description === '' || $currentPrice <= 0 || !in_array($status, ['available', 'unavailable', 'archived'], true)) {
            flash('Please complete all fields with a valid price.', 'danger');
            redirect($saveUrl);
        }

        $course = manageable_course_for_admin($admin, $courseId);
        if (!$course) {
            flash('Select a valid course you are allowed to manage.', 'danger');
            redirect($saveUrl);
        }

        if ($handoutId > 0) {
            $stmt = $pdo->prepare('SELECT * FROM handouts WHERE handout_id = ?');
            $stmt->execute([$handoutId]);
            $existingHandout = $stmt->fetch();
            if (!$existingHandout || !manageable_course_for_admin($admin, (int) $existingHandout['course_id'])) {
                flash('You can only update handouts for your assigned courses.', 'warning');
                redirect($dashboardBaseUrl . '?panel=manage-handouts');
            }

            $stmt = $pdo->prepare('UPDATE handouts
                SET department_id = ?, level_id = ?, course_id = ?, title = ?, course_code = ?, description = ?, current_price = ?, status = ?
                WHERE handout_id = ?');
            $stmt->execute([
                (int) $course['department_id'],
                (int) $course['level_id'],
                (int) $course['course_id'],
                $title,
                $course['course_code'],
                $description,
                $currentPrice,
                $status,
                $handoutId,
            ]);
            flash('Handout updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO handouts
                (department_id, level_id, course_id, title, course_code, description, current_price, status, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                (int) $course['department_id'],
                (int) $course['level_id'],
                (int) $course['course_id'],
                $title,
                $course['course_code'],
                $description,
                $currentPrice,
                $status,
                $admin['admin_id'],
            ]);
            flash('Handout added.');
        }

        redirect($dashboardBaseUrl . '?panel=manage-handouts');
    }

    if ($action === 'delete_paid_order') {
        $stmt = $pdo->prepare('SELECT o.*, s.full_name, h.course_id
            FROM orders o
            JOIN students s ON s.student_id = o.student_id
            JOIN handouts h ON h.handout_id = o.handout_id
            WHERE o.order_id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order || $order['payment_status'] !== 'paid') {
            flash('Paid student record not found.', 'warning');
            redirect($returnUrl);
        }
        if (!is_super_admin($admin) && !manageable_course_for_admin($admin, (int) $order['course_id'])) {
            flash('You can only update orders for your assigned courses.', 'warning');
            redirect($returnUrl);
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE FROM payments WHERE order_id = ?');
            $stmt->execute([$orderId]);

            $stmt = $pdo->prepare('DELETE FROM orders WHERE order_id = ?');
            $stmt->execute([$orderId]);

            $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$admin['admin_id'], 'delete paid student from dashboard handout group', 'orders', $order['order_reference']]);

            $pdo->commit();
            flash($order['full_name'] . ' has been deleted from the handout paid list.');
        } catch (Throwable $exception) {
            $pdo->rollBack();
            flash('Student could not be deleted from the handout paid list. Please try again.', 'danger');
        }

        redirect($returnUrl);
    }

    if ($action === 'update_collection') {
        $collectionStatus = $_POST['collection_status'] ?? '';
        if (!in_array($collectionStatus, ['not_ready', 'ready_for_collection', 'collected'], true)) {
            flash('Collection status is invalid.', 'warning');
            redirect($returnUrl);
        }

        $stmt = $pdo->prepare('SELECT h.course_id
            FROM orders o
            JOIN handouts h ON h.handout_id = o.handout_id
            WHERE o.order_id = ? AND o.payment_status = "paid"');
        $stmt->execute([$orderId]);
        $orderCourseId = (int) $stmt->fetchColumn();
        if ($orderCourseId <= 0 || (!is_super_admin($admin) && !manageable_course_for_admin($admin, $orderCourseId))) {
            flash('You can only update orders for your assigned courses.', 'warning');
            redirect($returnUrl);
        }

        $stmt = $pdo->prepare('UPDATE orders SET collection_status = ? WHERE order_id = ? AND payment_status = "paid"');
        $stmt->execute([$collectionStatus, $orderId]);
        flash('Collection status updated.');
        redirect($returnUrl);
    }

    if ($action === 'mark_collected') {
        $stmt = $pdo->prepare('SELECT o.*, s.full_name, h.course_id
            FROM orders o
            JOIN students s ON s.student_id = o.student_id
            JOIN handouts h ON h.handout_id = o.handout_id
            WHERE o.order_id = ?');
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();

        if (!$order || $order['payment_status'] !== 'paid') {
            flash('Paid student record not found.', 'warning');
            redirect($returnUrl);
        }
        if (!is_super_admin($admin) && !manageable_course_for_admin($admin, (int) $order['course_id'])) {
            flash('You can only update orders for your assigned courses.', 'warning');
            redirect($returnUrl);
        }

        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('UPDATE orders SET collection_status = "collected" WHERE order_id = ?');
            $stmt->execute([$orderId]);

            $stmt = $pdo->prepare('INSERT INTO audit_logs (admin_id, action, entity, entity_id) VALUES (?, ?, ?, ?)');
            $stmt->execute([$admin['admin_id'], 'mark handout as given from dashboard', 'orders', $order['order_reference']]);

            $pdo->commit();
            flash($order['full_name'] . ' has been marked as given.');
        } catch (Throwable $exception) {
            $pdo->rollBack();
            flash('Student could not be marked as given. Please try again.', 'danger');
        }

        redirect($returnUrl);
    }
}

$manageableCourses = manageable_courses_for_admin($admin);
$stats = [];
if (is_super_admin($admin)) {
    $stats = [
        'total_handouts' => (int) $pdo->query('SELECT COUNT(*) FROM handouts')->fetchColumn(),
        'available_handouts' => (int) $pdo->query('SELECT COUNT(*) FROM handouts WHERE status = "available"')->fetchColumn(),
        'paid_orders' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE payment_status = "paid"')->fetchColumn(),
        'recorded_unpaid' => (int) $pdo->query('SELECT COUNT(*) FROM orders WHERE payment_status = "not_paid"')->fetchColumn(),
    ];
} else {
    $stmt = $pdo->prepare('SELECT
            COUNT(*) AS total_handouts,
            SUM(h.status = "available") AS available_handouts
        FROM handouts h
        JOIN admin_course_assignments aca ON aca.course_id = h.course_id
        WHERE aca.admin_id = ?');
    $stmt->execute([(int) $admin['admin_id']]);
    $handoutStats = $stmt->fetch() ?: ['total_handouts' => 0, 'available_handouts' => 0];

    $stmt = $pdo->prepare('SELECT
            SUM(o.payment_status = "paid") AS paid_orders,
            SUM(o.payment_status = "not_paid") AS recorded_unpaid
        FROM orders o
        JOIN handouts h ON h.handout_id = o.handout_id
        JOIN admin_course_assignments aca ON aca.course_id = h.course_id
        WHERE aca.admin_id = ?');
    $stmt->execute([(int) $admin['admin_id']]);
    $orderStats = $stmt->fetch() ?: ['paid_orders' => 0, 'recorded_unpaid' => 0];

    $stats = [
        'total_handouts' => (int) ($handoutStats['total_handouts'] ?? 0),
        'available_handouts' => (int) ($handoutStats['available_handouts'] ?? 0),
        'paid_orders' => (int) ($orderStats['paid_orders'] ?? 0),
        'recorded_unpaid' => (int) ($orderStats['recorded_unpaid'] ?? 0),
    ];
}
$editHandoutId = (int) ($_GET['handout_id'] ?? 0);
$editHandout = null;
if ($editHandoutId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM handouts WHERE handout_id = ?');
    $stmt->execute([$editHandoutId]);
    $editHandout = $stmt->fetch();
    if (!$editHandout) {
        flash('Handout not found.', 'warning');
        redirect($dashboardBaseUrl . '?panel=manage-handouts');
    }
    if (!is_super_admin($admin) && !manageable_course_for_admin($admin, (int) $editHandout['course_id'])) {
        flash('You can only edit handouts for your assigned courses.', 'warning');
        redirect($dashboardBaseUrl . '?panel=manage-handouts');
    }
}
$studentSearch = trim($_GET['student_name'] ?? '');
$revenueSql = 'SELECT o.handout_id, o.course_code_snapshot, o.handout_title_snapshot,
        COUNT(*) AS paid_count,
        COALESCE(SUM(o.price_snapshot), 0) AS total_revenue
    FROM orders o';
$revenueParams = [];
if (!is_super_admin($admin)) {
    $revenueSql .= ' JOIN handouts h ON h.handout_id = o.handout_id
        JOIN admin_course_assignments aca ON aca.course_id = h.course_id AND aca.admin_id = ?';
    $revenueParams[] = (int) $admin['admin_id'];
}
$revenueSql .= ' WHERE o.payment_status = "paid"
    GROUP BY o.handout_id, o.course_code_snapshot, o.handout_title_snapshot
    ORDER BY o.course_code_snapshot, o.handout_title_snapshot';
$stmt = $pdo->prepare($revenueSql);
$stmt->execute($revenueParams);
$revenueByHandout = $stmt->fetchAll();
$dashboardHandoutSql = 'SELECT h.*, c.title AS campus_course_title, d.code AS department_code, l.name AS level_name, COUNT(o.order_id) AS order_count
    FROM handouts h
    LEFT JOIN courses c ON c.course_id = h.course_id
    LEFT JOIN departments d ON d.department_id = h.department_id
    LEFT JOIN academic_levels l ON l.level_id = h.level_id
    LEFT JOIN orders o ON o.handout_id = h.handout_id';
$dashboardHandoutParams = [];
if (!is_super_admin($admin)) {
    $dashboardHandoutSql .= ' JOIN admin_course_assignments aca ON aca.course_id = h.course_id AND aca.admin_id = ?';
    $dashboardHandoutParams[] = (int) $admin['admin_id'];
}
$dashboardHandoutSql .= ' GROUP BY h.handout_id ORDER BY h.created_at DESC';
$stmt = $pdo->prepare($dashboardHandoutSql);
$stmt->execute($dashboardHandoutParams);
$dashboardHandouts = $stmt->fetchAll();
$dashboardOrdersSql = 'SELECT o.*, s.full_name, s.index_number, s.phone, s.email
    FROM orders o
    JOIN students s ON s.student_id = o.student_id
';
$dashboardOrderParams = [];
if (!is_super_admin($admin)) {
    $dashboardOrdersSql .= 'JOIN handouts h ON h.handout_id = o.handout_id
        JOIN admin_course_assignments aca ON aca.course_id = h.course_id AND aca.admin_id = ?';
    $dashboardOrderParams[] = (int) $admin['admin_id'];
}
$dashboardOrdersSql .= ' WHERE o.payment_status = "paid" ORDER BY o.ordered_at DESC';
$stmt = $pdo->prepare($dashboardOrdersSql);
$stmt->execute($dashboardOrderParams);
$dashboardOrders = $stmt->fetchAll();

$incompleteOrdersSql = 'SELECT o.*, s.full_name, s.index_number, s.phone, s.email
    FROM orders o
    JOIN students s ON s.student_id = o.student_id
';
$incompleteOrderParams = [];
if (!is_super_admin($admin)) {
    $incompleteOrdersSql .= 'JOIN handouts h ON h.handout_id = o.handout_id
        JOIN admin_course_assignments aca ON aca.course_id = h.course_id AND aca.admin_id = ?';
    $incompleteOrderParams[] = (int) $admin['admin_id'];
}
$incompleteOrdersSql .= ' WHERE o.payment_status = "not_paid" ORDER BY o.ordered_at DESC';
$stmt = $pdo->prepare($incompleteOrdersSql);
$stmt->execute($incompleteOrderParams);
$incompleteOrders = $stmt->fetchAll();
$departments = $pdo->query('SELECT * FROM departments ORDER BY name')->fetchAll();
$levels = $pdo->query('SELECT * FROM academic_levels ORDER BY sort_order, name')->fetchAll();
$courses = $pdo->query('SELECT c.*, d.name AS department_name, d.code AS department_code, l.name AS level_name
    FROM courses c
    JOIN departments d ON d.department_id = c.department_id
    JOIN academic_levels l ON l.level_id = c.level_id
    ORDER BY d.name, l.sort_order, c.course_code')->fetchAll();
$editRepId = is_super_admin($admin) ? (int) ($_GET['rep_id'] ?? 0) : 0;
$editRep = null;
$editRepCourseIds = [];
if ($editRepId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM admins WHERE admin_id = ? AND role = "course_rep"');
    $stmt->execute([$editRepId]);
    $editRep = $stmt->fetch();
    if (!$editRep) {
        flash('Course representative account not found.', 'warning');
        redirect($dashboardBaseUrl . '?panel=campus-setup');
    }

    $stmt = $pdo->prepare('SELECT course_id FROM admin_course_assignments WHERE admin_id = ?');
    $stmt->execute([$editRepId]);
    $editRepCourseIds = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}
$courseReps = $pdo->query('SELECT a.admin_id, a.name, a.email, a.status, a.department_id, a.level_id, d.name AS department_name, l.name AS level_name,
        COUNT(aca.course_id) AS course_count,
        GROUP_CONCAT(CONCAT(c.course_code, " - ", c.title) ORDER BY c.course_code SEPARATOR "||") AS assigned_courses
    FROM admins a
    LEFT JOIN departments d ON d.department_id = a.department_id
    LEFT JOIN academic_levels l ON l.level_id = a.level_id
    LEFT JOIN admin_course_assignments aca ON aca.admin_id = a.admin_id
    LEFT JOIN courses c ON c.course_id = aca.course_id
    WHERE a.role = "course_rep"
    GROUP BY a.admin_id, a.name, a.email, a.status, a.department_id, a.level_id, d.name, l.name
    ORDER BY a.name')->fetchAll();
$paidSql = 'SELECT o.*, s.full_name, s.index_number, s.phone
    FROM orders o
    JOIN students s ON s.student_id = o.student_id';
$paidParams = [];
if (!is_super_admin($admin)) {
    $paidSql .= ' JOIN handouts h ON h.handout_id = o.handout_id
        JOIN admin_course_assignments aca ON aca.course_id = h.course_id AND aca.admin_id = ?';
    $paidParams[] = (int) $admin['admin_id'];
}
$paidSql .= ' WHERE o.payment_status = "paid"';
if ($studentSearch !== '') {
    $paidSql .= ' AND s.full_name LIKE ?';
    $paidParams[] = '%' . $studentSearch . '%';
}
$paidSql .= ' ORDER BY o.course_code_snapshot, o.handout_title_snapshot, s.full_name';
$stmt = $pdo->prepare($paidSql);
$stmt->execute($paidParams);
$paidRows = $stmt->fetchAll();
$paidByHandout = [];
foreach ($paidRows as $row) {
    $key = $row['course_code_snapshot'] . '|' . $row['handout_title_snapshot'];
    if (!isset($paidByHandout[$key])) {
        $paidByHandout[$key] = [
            'course_code' => $row['course_code_snapshot'],
            'title' => $row['handout_title_snapshot'],
            'total' => 0.0,
            'students' => [],
        ];
    }
    $paidByHandout[$key]['total'] += (float) $row['price_snapshot'];
    $paidByHandout[$key]['students'][] = $row;
}

page_header('Admin Dashboard');
?>
<main class="container-fluid px-3 px-lg-4 py-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center mb-4">
        <div>
            <h1 class="h2 mb-1">Dashboard</h1>
            <p class="text-muted mb-0">Welcome, <?= h($admin['name']) ?>.</p>
        </div>
    </div>

    <div class="dashboard-shell">
        <aside class="dashboard-sidebar" aria-label="Dashboard sections">
            <div class="sidebar-label">Sections</div>
            <button class="dashboard-nav-item active" type="button" data-dashboard-target="overview">
                <span>Overview</span>
                <strong><?= h((string) $stats['paid_orders']) ?></strong>
            </button>
            <button class="dashboard-nav-item" type="button" data-dashboard-target="revenue">
                <span>Revenue</span>
                <strong><?= count($revenueByHandout) ?></strong>
            </button>
            <button class="dashboard-nav-item" type="button" data-dashboard-target="paid-students">
                <span>Paid students</span>
                <strong><?= h((string) $stats['paid_orders']) ?></strong>
            </button>
            <button class="dashboard-nav-item" type="button" data-dashboard-target="incomplete-details">
                <span>Incomplete details</span>
                <strong><?= count($incompleteOrders) ?></strong>
            </button>
            <?php if ($paidByHandout): ?>
                <div class="dashboard-subnav" aria-label="Paid student handouts">
                    <button class="dashboard-subnav-item active" type="button" data-course-target="all">
                        <span>All paid lists</span>
                    </button>
                    <?php foreach ($paidByHandout as $group): ?>
                        <?php $courseKey = md5($group['course_code'] . '|' . $group['title']); ?>
                        <button class="dashboard-subnav-item" type="button" data-course-target="<?= h($courseKey) ?>">
                            <span><?= h($group['course_code']) ?></span>
                            <small><?= count($group['students']) ?></small>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (is_super_admin($admin)): ?>
                <div class="sidebar-label mt-4">Super admin</div>
                <button class="dashboard-nav-item" type="button" data-dashboard-target="campus-setup">
                    <span>Campus setup</span>
                    <strong><?= count($departments) + count($levels) + count($courses) + count($courseReps) ?></strong>
                </button>
            <?php endif; ?>

            <div class="sidebar-label mt-4"><?= is_super_admin($admin) ? 'Oversight' : 'Course rep tools' ?></div>
            <?php if (!is_super_admin($admin)): ?>
                <button class="dashboard-nav-item" type="button" data-dashboard-target="manage-handouts">
                    <span>Manage handouts</span>
                    <strong><?= count($dashboardHandouts) ?></strong>
                </button>
                <button class="dashboard-nav-item" type="button" data-dashboard-target="edit-handout">
                    <span><?= $editHandoutId ? 'Edit handout' : 'Add handout' ?></span>
                </button>
            <?php endif; ?>
            <button class="dashboard-nav-item" type="button" data-dashboard-target="view-orders">
                <span>View orders</span>
                <strong><?= count($dashboardOrders) ?></strong>
            </button>
        </aside>

        <div class="dashboard-content">
            <section class="dashboard-panel" id="dashboard-overview" data-dashboard-panel="overview">
                <div class="row g-3">
                    <?php foreach ([
                        'Total handouts' => $stats['total_handouts'],
                        'Available' => $stats['available_handouts'],
                        'Paid orders' => $stats['paid_orders'],
                        'Saved incomplete details' => $stats['recorded_unpaid'],
                    ] as $label => $value): ?>
                        <div class="col-md-6 col-xl-3">
                            <div class="card dashboard-card h-100">
                                <div class="card-body">
                                    <div class="text-muted small"><?= h($label) ?></div>
                                    <div class="h3 mb-0"><?= h((string) $value) ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="dashboard-panel" id="dashboard-revenue" data-dashboard-panel="revenue" hidden>
                <div class="bg-white border rounded-2 p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Revenue by handout</h2>
                            <p class="text-muted mb-0">Each handout keeps its own revenue total.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary align-self-md-start" type="button" data-dashboard-target="view-orders">Open paid list</button>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($revenueByHandout as $revenue): ?>
                            <div class="col-md-6 col-xl-4">
                                <div class="card dashboard-card revenue-card h-100">
                                    <div class="card-body">
                                        <div class="text-muted small"><?= h($revenue['course_code_snapshot']) ?></div>
                                        <h3 class="h6 mb-3"><?= h($revenue['handout_title_snapshot']) ?></h3>
                                        <div class="h3 mb-1"><?= money($revenue['total_revenue']) ?></div>
                                        <div class="text-muted small"><?= (int) $revenue['paid_count'] ?> paid student<?= (int) $revenue['paid_count'] === 1 ? '' : 's' ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if (!$revenueByHandout): ?>
                        <div class="alert alert-info mb-0">No paid revenue has been recorded yet.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="dashboard-panel" id="dashboard-paid-students" data-dashboard-panel="paid-students" hidden>
                <div class="bg-white border rounded-2 p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Paid students by handout</h2>
                            <p class="text-muted mb-0">Students are grouped under the exact handout they paid for.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary align-self-md-start" type="button" data-dashboard-target="view-orders">Open paid list</button>
                    </div>
                    <form class="row g-3 align-items-end mb-4" method="get">
                        <input type="hidden" name="panel" value="paid-students">
                        <div class="col-md-8">
                            <label class="form-label" for="student_name">Search student name</label>
                            <input class="form-control" id="student_name" name="student_name" value="<?= h($studentSearch) ?>" placeholder="Enter student name">
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button class="btn btn-primary flex-fill" type="submit">Search</button>
                            <?php if ($studentSearch !== ''): ?>
                                <a class="btn btn-outline-secondary" href="/Handout%20Payment%20System/admin/dashboard.php?panel=paid-students">Clear</a>
                            <?php endif; ?>
                        </div>
                    </form>
                    <?php if ($studentSearch !== ''): ?>
                        <div class="alert alert-info">Showing paid students matching "<?= h($studentSearch) ?>".</div>
                    <?php endif; ?>
                    <?php foreach ($paidByHandout as $group): ?>
                        <?php $courseKey = md5($group['course_code'] . '|' . $group['title']); ?>
                        <section class="paid-group border rounded-2 p-3 mb-3" data-course-group="<?= h($courseKey) ?>">
                            <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                                <div>
                                    <span class="badge text-bg-secondary"><?= h($group['course_code']) ?></span>
                                    <h3 class="h5 mt-2 mb-0"><?= h($group['title']) ?></h3>
                                </div>
                                <div class="text-md-end">
                                    <div class="fw-bold"><?= count($group['students']) ?> student<?= count($group['students']) === 1 ? '' : 's' ?></div>
                                    <div class="text-muted small"><?= money($group['total']) ?> received</div>
                                </div>
                            </div>
                            <div class="paid-group-search mb-3">
                                <label class="form-label" for="search-<?= h(md5($group['course_code'] . $group['title'])) ?>">Search this handout list</label>
                                <input class="form-control form-control-sm" id="search-<?= h(md5($group['course_code'] . $group['title'])) ?>" data-handout-search type="search" placeholder="Student name in <?= h($group['course_code']) ?>">
                                <div class="small text-muted mt-1" data-handout-search-count></div>
                            </div>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Index</th>
                                            <th>Phone</th>
                                            <th>Amount</th>
                                            <th>Collection</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($group['students'] as $student): ?>
                                            <tr class="<?= $student['collection_status'] === 'collected' ? 'student-collected' : '' ?>">
                                                <td><?= h($student['full_name']) ?></td>
                                                <td><?= h($student['index_number']) ?></td>
                                                <td><?= h($student['phone']) ?></td>
                                                <td><?= money($student['price_snapshot']) ?></td>
                                                <td><?= status_badge($student['collection_status']) ?></td>
                                                <td class="text-end">
                                                    <div class="d-inline-flex gap-2">
                                                        <?php if ($student['collection_status'] !== 'collected'): ?>
                                                            <form method="post">
                                                                <input type="hidden" name="order_id" value="<?= (int) $student['order_id'] ?>">
                                                                <input type="hidden" name="return_panel" value="paid-students">
                                                                <button class="btn btn-sm btn-outline-primary" name="action" value="mark_collected" type="submit">Given</button>
                                                            </form>
                                                        <?php endif; ?>
                                                        <form method="post" onsubmit="return confirm('Delete this student from this handout paid list? This removes the order and reduces revenue.');">
                                                            <input type="hidden" name="order_id" value="<?= (int) $student['order_id'] ?>">
                                                            <input type="hidden" name="return_panel" value="paid-students">
                                                            <button class="btn btn-sm btn-outline-danger" name="action" value="delete_paid_order" type="submit">Delete</button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    <?php endforeach; ?>
                    <?php if (!$paidByHandout): ?>
                        <div class="alert alert-info mb-0"><?= $studentSearch !== '' ? 'No paid students match that name.' : 'No paid orders have been recorded yet.' ?></div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="dashboard-panel" id="dashboard-incomplete-details" data-dashboard-panel="incomplete-details" hidden>
                <div class="bg-white border rounded-2 p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Incomplete details</h2>
                            <p class="text-muted mb-0">Students listed here entered their details, but Paystack has not confirmed payment.</p>
                        </div>
                        <span class="badge text-bg-warning align-self-md-start"><?= count($incompleteOrders) ?> not paid</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Contact</th>
                                    <th>Handout</th>
                                    <th>Amount</th>
                                    <th>Reference</th>
                                    <th>Saved</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($incompleteOrders as $order): ?>
                                    <tr>
                                        <td><?= h($order['full_name']) ?><br><span class="text-muted small"><?= h($order['index_number']) ?></span></td>
                                        <td><span class="small"><?= h($order['phone']) ?><br><?= h($order['email']) ?></span></td>
                                        <td><?= h($order['course_code_snapshot']) ?><br><span class="text-muted small"><?= h($order['handout_title_snapshot']) ?></span></td>
                                        <td><?= money($order['price_snapshot']) ?></td>
                                        <td><span class="small"><?= h($order['order_reference']) ?></span></td>
                                        <td><span class="small"><?= h($order['ordered_at']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!$incompleteOrders): ?>
                        <div class="alert alert-info mb-0">No incomplete payment details are currently saved.</div>
                    <?php endif; ?>
                </div>
            </section>

            <?php if (is_super_admin($admin)): ?>
                <section class="dashboard-panel" id="dashboard-campus-setup" data-dashboard-panel="campus-setup" hidden>
                    <div class="bg-white border rounded-2 p-4">
                        <div class="mb-4">
                            <h2 class="h4 mb-1">Campus setup</h2>
                            <p class="text-muted mb-0">Create the campus structure and course rep accounts. Course reps publish available handouts and set prices.</p>
                        </div>

                        <div class="row g-4">
                            <div class="col-xl-5">
                                <form method="post" class="campus-form border rounded-2 p-3 mb-4">
                                    <input type="hidden" name="action" value="save_department">
                                    <h3 class="h5 mb-3">Add department</h3>
                                    <div class="mb-3">
                                        <label class="form-label" for="department_name">Department name</label>
                                        <input class="form-control" id="department_name" name="department_name" placeholder="Computer Science" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="department_code">Code</label>
                                        <input class="form-control" id="department_code" name="department_code" placeholder="CS" required>
                                    </div>
                                    <button class="btn btn-primary w-100" type="submit">Save department</button>
                                </form>

                                <form method="post" class="campus-form border rounded-2 p-3 mb-4">
                                    <input type="hidden" name="action" value="save_level">
                                    <h3 class="h5 mb-3">Add level</h3>
                                    <div class="mb-3">
                                        <label class="form-label" for="level_name">Level name</label>
                                        <input class="form-control" id="level_name" name="level_name" placeholder="Level 200" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="sort_order">Sort order</label>
                                        <input class="form-control" id="sort_order" name="sort_order" type="number" value="0">
                                    </div>
                                    <button class="btn btn-primary w-100" type="submit">Save level</button>
                                </form>

                                <form method="post" class="campus-form border rounded-2 p-3">
                                    <input type="hidden" name="action" value="save_course">
                                    <h3 class="h5 mb-3">Add official course</h3>
                                    <div class="mb-3">
                                        <label class="form-label" for="department_id">Department</label>
                                        <select class="form-select" id="department_id" name="department_id" required>
                                            <option value="">Select department</option>
                                            <?php foreach ($departments as $department): ?>
                                                <option value="<?= (int) $department['department_id'] ?>"><?= h($department['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="level_id">Level</label>
                                        <select class="form-select" id="level_id" name="level_id" required>
                                            <option value="">Select level</option>
                                            <?php foreach ($levels as $level): ?>
                                                <option value="<?= (int) $level['level_id'] ?>"><?= h($level['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label" for="campus_course_code">Course code</label>
                                            <input class="form-control" id="campus_course_code" name="course_code" placeholder="CS201" required>
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label" for="course_title">Course title</label>
                                            <input class="form-control" id="course_title" name="course_title" placeholder="Data Structures" required>
                                        </div>
                                    </div>
                                    <button class="btn btn-primary w-100 mt-3" type="submit">Save course</button>
                                </form>

                                <form method="post" class="campus-form border rounded-2 p-3 mt-4" id="course-rep-form">
                                    <input type="hidden" name="action" value="save_course_rep">
                                    <input type="hidden" name="rep_id" value="<?= (int) ($editRep['admin_id'] ?? 0) ?>">
                                    <div class="d-flex justify-content-between gap-2 align-items-center mb-3">
                                        <h3 class="h5 mb-0"><?= $editRep ? 'Edit course rep' : 'Add course rep' ?></h3>
                                        <?php if ($editRep): ?>
                                            <a class="btn btn-sm btn-outline-secondary" href="/Handout%20Payment%20System/admin/dashboard.php?panel=campus-setup">New rep</a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="rep_name">Rep name</label>
                                        <input class="form-control" id="rep_name" name="rep_name" value="<?= h($editRep['name'] ?? '') ?>" placeholder="Course Rep Name" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="rep_email">Email</label>
                                        <input class="form-control" id="rep_email" name="rep_email" type="email" value="<?= h($editRep['email'] ?? '') ?>" placeholder="rep@example.com" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="rep_password"><?= $editRep ? 'Reset password' : 'Temporary password' ?></label>
                                        <input class="form-control" id="rep_password" name="rep_password" type="password" <?= $editRep ? '' : 'required' ?>>
                                        <?php if ($editRep): ?>
                                            <div class="form-text">Leave blank to keep the current password.</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="rep_status">Status</label>
                                        <select class="form-select" id="rep_status" name="rep_status" required>
                                            <?php foreach (['active', 'inactive'] as $status): ?>
                                                <option value="<?= h($status) ?>" <?= ($editRep['status'] ?? 'active') === $status ? 'selected' : '' ?>><?= h(ucfirst($status)) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label" for="rep_department_id">Department</label>
                                            <select class="form-select" id="rep_department_id" name="rep_department_id" required>
                                                <option value="">Select department</option>
                                                <?php foreach ($departments as $department): ?>
                                                    <option value="<?= (int) $department['department_id'] ?>" <?= (int) ($editRep['department_id'] ?? 0) === (int) $department['department_id'] ? 'selected' : '' ?>><?= h($department['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="rep_level_id">Level</label>
                                            <select class="form-select" id="rep_level_id" name="rep_level_id" required>
                                                <option value="">Select level</option>
                                                <?php foreach ($levels as $level): ?>
                                                    <option value="<?= (int) $level['level_id'] ?>" <?= (int) ($editRep['level_id'] ?? 0) === (int) $level['level_id'] ? 'selected' : '' ?>><?= h($level['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <div class="form-label">Courses managed</div>
                                        <div class="course-assignment-list">
                                            <?php foreach ($courses as $course): ?>
                                                <label class="course-assignment-option" data-rep-course-option data-department-id="<?= (int) $course['department_id'] ?>" data-level-id="<?= (int) $course['level_id'] ?>">
                                                    <input class="form-check-input" type="checkbox" name="course_ids[]" value="<?= (int) $course['course_id'] ?>" <?= in_array((int) $course['course_id'], $editRepCourseIds, true) ? 'checked' : '' ?>>
                                                    <span>
                                                        <strong><?= h($course['course_code']) ?></strong>
                                                        <?= h($course['title']) ?><br>
                                                        <small><?= h($course['department_code']) ?>, <?= h($course['level_name']) ?></small>
                                                    </span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="form-text" data-rep-course-message></div>
                                        <?php if (!$courses): ?>
                                            <div class="alert alert-info mt-2 mb-0">Create at least one course before adding a course rep.</div>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-primary w-100 mt-3" type="submit"><?= $editRep ? 'Update course rep' : 'Save course rep' ?></button>
                                </form>
                            </div>

                            <div class="col-xl-7">
                                <div class="table-responsive mb-4">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Department</th>
                                                <th>Code</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($departments as $department): ?>
                                                <tr>
                                                    <td><?= h($department['name']) ?></td>
                                                    <td><?= h($department['code']) ?></td>
                                                    <td><?= status_badge($department['status']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (!$departments): ?>
                                    <div class="alert alert-info">No departments have been created yet.</div>
                                <?php endif; ?>

                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Level</th>
                                                <th>Sort</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($levels as $level): ?>
                                                <tr>
                                                    <td><?= h($level['name']) ?></td>
                                                    <td><?= (int) $level['sort_order'] ?></td>
                                                    <td><?= status_badge($level['status']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (!$levels): ?>
                                    <div class="alert alert-info mt-3">No levels have been created yet.</div>
                                <?php endif; ?>

                                <div class="table-responsive mt-4">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Department</th>
                                                <th>Level</th>
                                                <th>Code</th>
                                                <th>Course</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($courses as $course): ?>
                                                <tr>
                                                    <td><?= h($course['department_name']) ?><br><span class="text-muted small"><?= h($course['department_code']) ?></span></td>
                                                    <td><?= h($course['level_name']) ?></td>
                                                    <td><?= h($course['course_code']) ?></td>
                                                    <td><?= h($course['title']) ?></td>
                                                    <td><?= status_badge($course['status']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (!$courses): ?>
                                    <div class="alert alert-info mt-3">No courses have been created yet.</div>
                                <?php endif; ?>

                                <div class="table-responsive mt-4">
                                    <table class="table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Course rep</th>
                                                <th>Scope</th>
                                                <th>Courses</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($courseReps as $rep): ?>
                                                <?php $assignedCourses = $rep['assigned_courses'] ? explode('||', $rep['assigned_courses']) : []; ?>
                                                <tr>
                                                    <td><?= h($rep['name']) ?><br><span class="text-muted small"><?= h($rep['email']) ?></span></td>
                                                    <td><?= h($rep['department_name'] ?? 'Not set') ?><br><span class="text-muted small"><?= h($rep['level_name'] ?? 'Not set') ?></span></td>
                                                    <td>
                                                        <?php if ($assignedCourses): ?>
                                                            <span class="small"><?= h(implode(', ', $assignedCourses)) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted small">No courses assigned</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?= status_badge($rep['status']) ?></td>
                                                    <td class="text-end">
                                                        <a class="btn btn-sm btn-outline-primary" href="/Handout%20Payment%20System/admin/dashboard.php?panel=campus-setup&rep_id=<?= (int) $rep['admin_id'] ?>#course-rep-form">Edit</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (!$courseReps): ?>
                                    <div class="alert alert-info mt-3">No course representatives have been created yet.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (!is_super_admin($admin)): ?>
            <section class="dashboard-panel" id="dashboard-manage-handouts" data-dashboard-panel="manage-handouts" hidden>
                <div class="bg-white border rounded-2 p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Manage handouts</h2>
                            <p class="text-muted mb-0">Edit handouts or delete a handout completely with its related order records.</p>
                        </div>
                        <a class="btn btn-sm btn-primary align-self-md-start" href="/Handout%20Payment%20System/admin/dashboard.php?panel=edit-handout">Add handout</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Course</th>
                                    <th>Title</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Orders</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dashboardHandouts as $handout): ?>
                                    <tr>
                                        <td>
                                            <?= h($handout['course_code']) ?><br>
                                            <span class="text-muted small">
                                                <?= h($handout['campus_course_title'] ?? 'No campus course') ?>
                                                <?php if (!empty($handout['department_code']) || !empty($handout['level_name'])): ?>
                                                    · <?= h(trim(($handout['department_code'] ?? '') . ' ' . ($handout['level_name'] ?? ''))) ?>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td><?= h($handout['title']) ?></td>
                                        <td><?= money($handout['current_price']) ?></td>
                                        <td><?= status_badge($handout['status']) ?></td>
                                        <td><?= (int) $handout['order_count'] ?></td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-2">
                                                <a class="btn btn-sm btn-outline-primary" href="/Handout%20Payment%20System/admin/dashboard.php?panel=edit-handout&handout_id=<?= (int) $handout['handout_id'] ?>">Edit</a>
                                                <form method="post" onsubmit="return confirm('Delete this handout completely? This will remove its orders and payment records too.');">
                                                    <input type="hidden" name="handout_id" value="<?= (int) $handout['handout_id'] ?>">
                                                    <input type="hidden" name="return_panel" value="manage-handouts">
                                                    <button class="btn btn-sm btn-outline-danger" name="action" value="delete" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!$dashboardHandouts): ?>
                        <div class="alert alert-info mb-0">No handouts have been created yet.</div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="dashboard-panel" id="dashboard-edit-handout" data-dashboard-panel="edit-handout" hidden>
                <form method="post" class="bg-white border rounded-2 p-4">
                    <input type="hidden" name="action" value="save_handout">
                    <input type="hidden" name="handout_id" value="<?= (int) $editHandoutId ?>">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1"><?= $editHandoutId ? 'Edit handout' : 'Add handout' ?></h2>
                            <p class="text-muted mb-0">Update the course handout details without leaving the dashboard.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary align-self-md-start" type="button" data-dashboard-target="manage-handouts">Back to handouts</button>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="title">Title</label>
                            <input class="form-control" id="title" name="title" value="<?= h($editHandout['title'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="course_id">Campus course</label>
                            <select class="form-select" id="course_id" name="course_id" required>
                                <option value="">Select campus course</option>
                                <?php foreach ($manageableCourses as $course): ?>
                                    <option value="<?= (int) $course['course_id'] ?>" <?= (int) ($editHandout['course_id'] ?? 0) === (int) $course['course_id'] ? 'selected' : '' ?>>
                                        <?= h($course['course_code'] . ' - ' . $course['title'] . ' (' . $course['department_code'] . ', ' . $course['level_name'] . ')') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!$manageableCourses): ?>
                                <div class="form-text text-danger">No campus courses are available for this admin yet.</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="current_price">Current price</label>
                            <input class="form-control" id="current_price" name="current_price" type="number" step="0.01" min="0" value="<?= h($editHandout['current_price'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="status">Status</label>
                            <select class="form-select" id="status" name="status">
                                <?php foreach (['available', 'unavailable', 'archived'] as $status): ?>
                                    <option value="<?= h($status) ?>" <?= ($editHandout['status'] ?? 'available') === $status ? 'selected' : '' ?>><?= h(str_replace('_', ' ', $status)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?= h($editHandout['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-4">
                        <button class="btn btn-primary" type="submit">Save handout</button>
                        <button class="btn btn-outline-secondary" type="button" data-dashboard-target="manage-handouts">Cancel</button>
                    </div>
                </form>
            </section>
            <?php endif; ?>

            <section class="dashboard-panel" id="dashboard-view-orders" data-dashboard-panel="view-orders" hidden>
                <div class="bg-white border rounded-2 p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">View orders</h2>
                            <p class="text-muted mb-0">Only paid students appear here. Use this list to update collection status or remove a paid record.</p>
                        </div>
                        <button class="btn btn-sm btn-outline-primary align-self-md-start" type="button" data-dashboard-target="paid-students">Grouped paid lists</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Reference</th>
                                    <th>Student</th>
                                    <th>Contact</th>
                                    <th>Handout</th>
                                    <th>Amount</th>
                                    <th>Collection</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($dashboardOrders as $order): ?>
                                    <tr class="<?= $order['collection_status'] === 'collected' ? 'student-collected' : '' ?>">
                                        <td><?= h($order['order_reference']) ?></td>
                                        <td><?= h($order['full_name']) ?><br><span class="text-muted small"><?= h($order['index_number']) ?></span></td>
                                        <td><span class="small"><?= h($order['phone']) ?><br><?= h($order['email']) ?></span></td>
                                        <td><?= h($order['course_code_snapshot']) ?><br><span class="text-muted small"><?= h($order['handout_title_snapshot']) ?></span></td>
                                        <td><?= money($order['price_snapshot']) ?></td>
                                        <td>
                                            <form method="post" class="d-flex gap-2">
                                                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                                                <input type="hidden" name="action" value="update_collection">
                                                <input type="hidden" name="return_panel" value="view-orders">
                                                <select class="form-select form-select-sm" name="collection_status">
                                                    <?php foreach (['not_ready', 'ready_for_collection', 'collected'] as $status): ?>
                                                        <option value="<?= h($status) ?>" <?= $order['collection_status'] === $status ? 'selected' : '' ?>><?= h(str_replace('_', ' ', $status)) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button class="btn btn-sm btn-outline-primary" type="submit">Save</button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <form method="post" onsubmit="return confirm('Delete this student from the paid list after giving the handout?');">
                                                <input type="hidden" name="order_id" value="<?= (int) $order['order_id'] ?>">
                                                <input type="hidden" name="return_panel" value="view-orders">
                                                <button class="btn btn-sm btn-outline-danger" name="action" value="delete_paid_order" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if (!$dashboardOrders): ?>
                        <div class="alert alert-info mb-0">No paid orders have been recorded yet.</div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</main>
<script src="/Handout%20Payment%20System/assets/js/dashboard.js?v=20260713"></script>
<?php page_footer(); ?>
