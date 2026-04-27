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

function getGoogleRedirectUri(): string {
    $oauth = include(__DIR__ . '/../config/oauth.php');
    $configured = trim((string)($oauth['google']['redirect_uri'] ?? ''));

    if ($configured !== '') {
        return $configured;
    }

    return 'http://localhost/Shopping-cart-application/auth/google-callback.php';
}

function buildGoogleAuthUrl(string $clientId, string $redirectUri, string $state): string {
    $params = [
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'online',
        'prompt' => 'select_account',
        'include_granted_scopes' => 'true',
        'state' => $state,
    ];

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

function httpPostForm(string $url, array $fields): array {
    $body = http_build_query($fields, '', '&', PHP_QUERY_RFC3986);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'status' => 0, 'body' => null, 'error' => $error ?: 'Request failed'];
        }

        return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'body' => $response, 'error' => $status >= 200 && $status < 300 ? '' : 'Unexpected HTTP status'];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $body,
            'timeout' => 20,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    $status = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $status = (int)$m[1];
    }

    if ($response === false) {
        return ['ok' => false, 'status' => $status, 'body' => null, 'error' => 'Request failed'];
    }

    return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'body' => $response, 'error' => $status >= 200 && $status < 300 ? '' : 'Unexpected HTTP status'];
}

function httpGetJson(string $url, string $bearerToken = ''): array {
    $headers = $bearerToken !== '' ? ["Authorization: Bearer {$bearerToken}"] : [];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['ok' => false, 'status' => 0, 'body' => null, 'error' => $error ?: 'Request failed'];
        }

        return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'body' => json_decode($response, true), 'error' => $status >= 200 && $status < 300 ? '' : 'Unexpected HTTP status'];
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'timeout' => 20,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    $status = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $status = (int)$m[1];
    }

    if ($response === false) {
        return ['ok' => false, 'status' => $status, 'body' => null, 'error' => 'Request failed'];
    }

    return ['ok' => $status >= 200 && $status < 300, 'status' => $status, 'body' => json_decode($response, true), 'error' => $status >= 200 && $status < 300 ? '' : 'Unexpected HTTP status'];
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
