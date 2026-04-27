<?php
include(__DIR__ . '/oauth-helpers.php');
ensureAuthSchema($conn);

$redirect = safeRedirectTarget($_GET['redirect'] ?? '../pages/products.php');

// Redirect to account chooser
header('Location: account-chooser.php?provider=google&redirect=' . urlencode($redirect));
exit;
?>
