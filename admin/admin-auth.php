<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../config/db.php');

function ensureAdminSetup(mysqli $conn): void {
    $conn->query("CREATE TABLE IF NOT EXISTS admin_users (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(60) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        full_name VARCHAR(120) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS feedbacks (
        feedback_id INT AUTO_INCREMENT PRIMARY KEY,
        category VARCHAR(60) DEFAULT 'General',
        name VARCHAR(120) NOT NULL,
        email VARCHAR(120) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('new','reviewed','resolved') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->query("CREATE TABLE IF NOT EXISTS payments (
        payment_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        user_id INT NOT NULL,
        method ENUM('cash_on_delivery','card','bank_transfer') DEFAULT 'cash_on_delivery',
        amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending','paid','failed') DEFAULT 'pending',
        transaction_ref VARCHAR(120) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $orderColumns = [];
    $colRs = $conn->query("SHOW COLUMNS FROM orders");
    if ($colRs) {
        while ($col = $colRs->fetch_assoc()) {
            $orderColumns[$col['Field']] = true;
        }
    }

    if (!isset($orderColumns['customer_name'])) {
        $conn->query("ALTER TABLE orders ADD COLUMN customer_name VARCHAR(120) NULL");
    }
    if (!isset($orderColumns['phone'])) {
        $conn->query("ALTER TABLE orders ADD COLUMN phone VARCHAR(30) NULL");
    }
    if (!isset($orderColumns['address_line'])) {
        $conn->query("ALTER TABLE orders ADD COLUMN address_line VARCHAR(255) NULL");
    }
    if (!isset($orderColumns['city'])) {
        $conn->query("ALTER TABLE orders ADD COLUMN city VARCHAR(120) NULL");
    }
    if (!isset($orderColumns['payment_method'])) {
        $conn->query("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(40) NULL");
    }
    if (!isset($orderColumns['payment_status'])) {
        $conn->query("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) NULL");
    }

    $defaultUsername = 'admin';
    $defaultName = 'System Admin';
    $defaultHash = password_hash('admin123', PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT admin_id FROM admin_users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $defaultUsername);
    $stmt->execute();
    $existing = $stmt->get_result();

    if ($existing && $existing->num_rows > 0) {
        $update = $conn->prepare("UPDATE admin_users SET password_hash = ?, full_name = ? WHERE username = ?");
        $update->bind_param("sss", $defaultHash, $defaultName, $defaultUsername);
        $update->execute();
    } else {
        $insert = $conn->prepare("INSERT INTO admin_users (username, password_hash, full_name) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $defaultUsername, $defaultHash, $defaultName);
        $insert->execute();
    }
}

function requireAdmin(): void {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: dashboard.php');
        exit;
    }
}
