CREATE DATABASE IF NOT EXISTS handout_payment_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE handout_payment_system;

CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'course_rep') NOT NULL DEFAULT 'course_rep',
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    department_id INT NULL,
    level_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS departments (
    department_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    code VARCHAR(40) NOT NULL UNIQUE,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS academic_levels (
    level_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    sort_order INT NOT NULL DEFAULT 0,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    level_id INT NOT NULL,
    course_code VARCHAR(40) NOT NULL,
    title VARCHAR(180) NOT NULL,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_course_scope (department_id, level_id, course_code),
    CONSTRAINT fk_courses_department FOREIGN KEY (department_id) REFERENCES departments(department_id),
    CONSTRAINT fk_courses_level FOREIGN KEY (level_id) REFERENCES academic_levels(level_id)
);

CREATE TABLE IF NOT EXISTS admin_course_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    course_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_admin_course (admin_id, course_id),
    CONSTRAINT fk_assignments_admin FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE CASCADE,
    CONSTRAINT fk_assignments_course FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS handouts (
    handout_id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NULL,
    level_id INT NULL,
    course_id INT NULL,
    title VARCHAR(180) NOT NULL,
    course_code VARCHAR(40) NOT NULL,
    description TEXT NOT NULL,
    current_price DECIMAL(10,2) NOT NULL,
    status ENUM('available', 'unavailable', 'archived') NOT NULL DEFAULT 'available',
    created_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_handouts_department FOREIGN KEY (department_id) REFERENCES departments(department_id) ON DELETE SET NULL,
    CONSTRAINT fk_handouts_level FOREIGN KEY (level_id) REFERENCES academic_levels(level_id) ON DELETE SET NULL,
    CONSTRAINT fk_handouts_course FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE SET NULL,
    CONSTRAINT fk_handouts_admin FOREIGN KEY (created_by) REFERENCES admins(admin_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(160) NOT NULL,
    index_number VARCHAR(80) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(60) NOT NULL,
    programme VARCHAR(160) NOT NULL,
    level VARCHAR(40) NOT NULL,
    class_group VARCHAR(80) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_student_index (index_number)
);

CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_reference VARCHAR(80) NOT NULL UNIQUE,
    student_id INT NOT NULL,
    handout_id INT NOT NULL,
    handout_title_snapshot VARCHAR(180) NOT NULL,
    course_code_snapshot VARCHAR(40) NOT NULL,
    price_snapshot DECIMAL(10,2) NOT NULL,
    payment_status ENUM('not_paid', 'paid') NOT NULL DEFAULT 'not_paid',
    collection_status ENUM('not_ready', 'ready_for_collection', 'collected') NOT NULL DEFAULT 'not_ready',
    ordered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_student FOREIGN KEY (student_id) REFERENCES students(student_id),
    CONSTRAINT fk_orders_handout FOREIGN KEY (handout_id) REFERENCES handouts(handout_id)
);

CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL UNIQUE,
    reference VARCHAR(100) NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    provider VARCHAR(80) NOT NULL,
    status ENUM('initialized', 'successful', 'failed', 'reversed') NOT NULL DEFAULT 'initialized',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(order_id)
);

CREATE TABLE IF NOT EXISTS audit_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NULL,
    action VARCHAR(120) NOT NULL,
    entity VARCHAR(80) NOT NULL,
    entity_id VARCHAR(80) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_logs_admin FOREIGN KEY (admin_id) REFERENCES admins(admin_id) ON DELETE SET NULL
);
