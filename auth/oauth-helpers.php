<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include(__DIR__ . '/../config/db.php');

function safeRedirectTarget(?string $target): string {
    $fallback = '../pages/products.php';
    $target = trim((string)$target);

    if ($target === '') {
        return $fallback;
    }

    if (preg_match('/^https?:\/\//i', $target)) {
        return $fallback;
    }

    if ($target[0] === '/') {
        return $fallback;
    }

    return $target;
}

function ensureAuthSchema(mysqli $conn): void {
    $cols = [];
    $rs = $conn->query("SHOW COLUMNS FROM users");
    if ($rs) {
        while ($c = $rs->fetch_assoc()) {
            $cols[$c['Field']] = true;
        }
    }

    if (!isset($cols['passkey_id'])) {
        $conn->query("ALTER TABLE users ADD COLUMN passkey_id VARCHAR(255) NULL");
    }
}

function loginOrCreateSocialUser(mysqli $conn, string $name, string $email, string $type): ?array {
    $stmt = $conn->prepare("SELECT user_id, name, email, login_type FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $up = $conn->prepare("UPDATE users SET login_type = ?, name = ? WHERE user_id = ?");
        $up->bind_param('ssi', $type, $name, $user['user_id']);
        $up->execute();
        $user['name'] = $name;
        $user['login_type'] = $type;
        return $user;
    }

    $ins = $conn->prepare("INSERT INTO users (name, email, password, login_type) VALUES (?, ?, NULL, ?)");
    $ins->bind_param('sss', $name, $email, $type);
    if (!$ins->execute()) {
        return null;
    }

    $uid = (int)$conn->insert_id;
    $conn->query("INSERT IGNORE INTO carts (user_id) VALUES ($uid)");

    return [
        'user_id' => $uid,
        'name' => $name,
        'email' => $email,
        'login_type' => $type,
    ];
}

function completeLogin(array $user, ?string $redirect = null): void {
    $_SESSION['user_id'] = (int)$user['user_id'];
    $_SESSION['user_name'] = $user['name'];
    $sessionRedirect = $_SESSION['redirect_url'] ?? null;
    if (isset($_SESSION['redirect_url'])) {
        unset($_SESSION['redirect_url']);
    }

    $target = safeRedirectTarget($redirect ?: $sessionRedirect);
    header('Location: ' . $target);
    exit;
}
