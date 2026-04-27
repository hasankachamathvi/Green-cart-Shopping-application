<?php
include(__DIR__ . '/oauth-helpers.php');
ensureAuthSchema($conn);

$oauth = include(__DIR__ . '/../config/oauth.php');
$facebook = $oauth['facebook'] ?? [];
$appId = trim((string)($facebook['app_id'] ?? ''));
$appSecret = trim((string)($facebook['app_secret'] ?? ''));
$redirectUri = getFacebookRedirectUri();
$redirect = safeRedirectTarget($_SESSION['redirect_url'] ?? '../pages/products.php');

if (isset($_SESSION['redirect_url'])) {
    unset($_SESSION['redirect_url']);
}

if ($appId === '' || $appSecret === '') {
    die('Facebook OAuth is not configured. Set app_id and app_secret in config/oauth.php.');
}

if (!empty($_GET['error'])) {
    $msg = (string)($_GET['error_description'] ?? $_GET['error']);
    die('Facebook sign-in failed: ' . htmlspecialchars($msg));
}

if (empty($_GET['code'])) {
    die('Missing Facebook authorization code.');
}

if (empty($_GET['state']) || empty($_SESSION['facebook_oauth_state']) || !hash_equals((string)$_SESSION['facebook_oauth_state'], (string)$_GET['state'])) {
    unset($_SESSION['facebook_oauth_state']);
    die('Invalid Facebook sign-in state. Please try again.');
}

unset($_SESSION['facebook_oauth_state']);

$tokenUrl = 'https://graph.facebook.com/v20.0/oauth/access_token?' . http_build_query([
    'client_id' => $appId,
    'client_secret' => $appSecret,
    'redirect_uri' => $redirectUri,
    'code' => (string)$_GET['code'],
], '', '&', PHP_QUERY_RFC3986);

$tokenResponse = httpGetJson($tokenUrl);
if (!$tokenResponse['ok']) {
    die('Unable to exchange Facebook code for access token.');
}

$tokenData = $tokenResponse['body'] ?? [];
$accessToken = (string)($tokenData['access_token'] ?? '');
if ($accessToken === '') {
    die('Facebook access token not returned.');
}

$userUrl = 'https://graph.facebook.com/me?' . http_build_query([
    'fields' => 'id,name,email',
    'access_token' => $accessToken,
], '', '&', PHP_QUERY_RFC3986);

$userResponse = httpGetJson($userUrl);
if (!$userResponse['ok']) {
    die('Unable to fetch Facebook profile.');
}

$userData = $userResponse['body'] ?? [];
$email = trim((string)($userData['email'] ?? ''));
$name = trim((string)($userData['name'] ?? 'Facebook User'));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Facebook account did not return a valid email. Ensure your app has email permission.');
}

$user = loginOrCreateSocialUser($conn, $name, $email, 'facebook');
if (!$user) {
    die('Unable to create or update your account.');
}

completeLogin($user, $redirect);
