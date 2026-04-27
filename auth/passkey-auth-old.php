<?php
session_start();
include(__DIR__ . '/../config/db.php');
include(__DIR__ . '/oauth-helpers.php');

$redirect = safeRedirectTarget($_POST['redirect'] ?? ($_GET['redirect'] ?? '../pages/products.php'));

// Handle demo login from account chooser
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'demo-login') {
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? 'Passkey User');
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $user = loginOrCreateSocialUser($conn, $name, $email, 'passkey');
        if ($user) {
            completeLogin($user, $redirect);
        }
    }
    header('Location: login.php?redirect=' . urlencode($redirect) . '&error=unable_to_login');
    exit;
}

// Redirect to account chooser
header('Location: passkey-chooser.php?redirect=' . urlencode($redirect));
exit;
?>
