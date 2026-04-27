<?php
session_start();
include(__DIR__ . '/../config/db.php');
include(__DIR__ . '/oauth-helpers.php');

\ = safeRedirectTarget(\['redirect'] ?? (\['redirect'] ?? '../pages/products.php'));

// Handle demo login from account chooser
if (\['REQUEST_METHOD'] === 'POST' && isset(\['action']) && \['action'] === 'demo-login') {
    \ = trim(\['email'] ?? '');
    \ = trim(\['name'] ?? 'Passkey User');
    
    if (filter_var(\, FILTER_VALIDATE_EMAIL)) {
        \ = loginOrCreateSocialUser(\, \, \, 'passkey');
        if (\) {
            completeLogin(\, \);
        }
    }
    header('Location: login.php?redirect=' . urlencode(\) . '&error=unable_to_login');
    exit;
}

// Redirect to account chooser
header('Location: passkey-chooser.php?redirect=' . urlencode(\));
exit;
?>
