<?php
include(__DIR__ . '/oauth-helpers.php');
ensureAuthSchema($conn);

$oauth = include(__DIR__ . '/../config/oauth.php');
$google = $oauth['google'] ?? [];
$clientId = trim((string)($google['client_id'] ?? ''));
$clientSecret = trim((string)($google['client_secret'] ?? ''));
$redirectUri = getGoogleRedirectUri();
$redirect = safeRedirectTarget($_SESSION['redirect_url'] ?? '../pages/products.php');

if (isset($_SESSION['redirect_url'])) {
	unset($_SESSION['redirect_url']);
}

if ($clientId === '' || $clientSecret === '') {
	die('Google OAuth is not configured. Set client_id and client_secret in config/oauth.php.');
}

if (!empty($_GET['error'])) {
	die('Google sign-in failed: ' . htmlspecialchars((string)$_GET['error']));
}

if (empty($_GET['code'])) {
	die('Missing Google authorization code.');
}

if (empty($_GET['state']) || empty($_SESSION['google_oauth_state']) || !hash_equals((string)$_SESSION['google_oauth_state'], (string)$_GET['state'])) {
	unset($_SESSION['google_oauth_state']);
	die('Invalid Google sign-in state. Please try again.');
}

unset($_SESSION['google_oauth_state']);

$tokenResponse = httpPostForm('https://oauth2.googleapis.com/token', [
	'code' => $_GET['code'],
	'client_id' => $clientId,
	'client_secret' => $clientSecret,
	'redirect_uri' => $redirectUri,
	'grant_type' => 'authorization_code',
]);

if (!$tokenResponse['ok']) {
	die('Unable to exchange Google code for access token.');
}

$tokenData = json_decode((string)$tokenResponse['body'], true);
$accessToken = $tokenData['access_token'] ?? '';
if ($accessToken === '') {
	die('Google access token not returned.');
}

$userResponse = httpGetJson('https://www.googleapis.com/oauth2/v2/userinfo', $accessToken);
if (!$userResponse['ok']) {
	die('Unable to fetch Google profile.');
}

$userData = $userResponse['body'] ?? [];
$email = trim((string)($userData['email'] ?? ''));
$name = trim((string)($userData['name'] ?? 'Google User'));

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
	die('Google account did not return a valid email address.');
}

$user = loginOrCreateSocialUser($conn, $name, $email, 'google');
if (!$user) {
	die('Unable to create or update your account.');
}

completeLogin($user, $redirect);
