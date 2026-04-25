<?php
include(__DIR__ . '/oauth-helpers.php');
ensureAuthSchema($conn);

$oauth = include(__DIR__ . '/../config/oauth.php');
$demo = (bool)($oauth['demo_mode'] ?? true);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$name = trim($_POST['name'] ?? 'Google User');
		$email = trim($_POST['email'] ?? '');

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$error = 'Enter a valid email address.';
		} else {
				$user = loginOrCreateSocialUser($conn, $name, $email, 'google');
				if ($user) {
						completeLogin($user);
				}
				$error = 'Unable to login right now. Please try again.';
		}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Google Login - GreenCart</title>
	<link rel="stylesheet" href="../assets/css/style.css?v=20260425">
</head>
<body class="auth-body auth-screen">
	<div class="auth-container">
		<div class="auth-logo">🌿 GreenCart</div>
		<div class="auth-card">
			<h1 class="auth-title">Google Sign-In</h1>
			<p class="auth-subtitle">OAuth-ready flow with demo mode for development.</p>
			<?php if ($error): ?><div class="auth-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
			<?php if ($demo): ?>
			<form method="POST" class="auth-form">
				<div class="form-group">
					<label>Name</label>
					<input type="text" name="name" value="Google User" required>
				</div>
				<div class="form-group">
					<label>Google Email</label>
					<input type="email" name="email" placeholder="you@gmail.com" required>
				</div>
				<button type="submit" class="social-btn google-btn">Continue as Google User</button>
			</form>
			<p class="oauth-note">Set Google credentials in config/oauth.php and replace this with token verification for production.</p>
			<?php else: ?>
			<p class="oauth-note">Google OAuth is disabled. Please configure client_id and client_secret in config/oauth.php.</p>
			<?php endif; ?>
			<a href="login.php" class="auth-link-btn" style="margin-top:14px">Back to Login</a>
		</div>
	</div>
</body>
</html>
