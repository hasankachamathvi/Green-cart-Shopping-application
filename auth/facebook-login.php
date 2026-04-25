<?php
include(__DIR__ . '/oauth-helpers.php');
ensureAuthSchema($conn);

$oauth = include(__DIR__ . '/../config/oauth.php');
$demo = (bool)($oauth['demo_mode'] ?? true);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$name = trim($_POST['name'] ?? 'Facebook User');
		$email = trim($_POST['email'] ?? '');

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$error = 'Enter a valid email address.';
		} else {
				$user = loginOrCreateSocialUser($conn, $name, $email, 'facebook');
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
	<title>Facebook Login - GreenCart</title>
	<link rel="stylesheet" href="../assets/css/style.css?v=20260425">
</head>
<body class="auth-body auth-screen">
	<div class="auth-container">
		<div class="auth-logo">🌿 GreenCart</div>
		<div class="auth-card">
			<h1 class="auth-title">Facebook Sign-In</h1>
			<p class="auth-subtitle">OAuth-ready flow with demo mode for development.</p>
			<?php if ($error): ?><div class="auth-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
			<?php if ($demo): ?>
			<form method="POST" class="auth-form">
				<div class="form-group">
					<label>Name</label>
					<input type="text" name="name" value="Facebook User" required>
				</div>
				<div class="form-group">
					<label>Facebook Email</label>
					<input type="email" name="email" placeholder="you@example.com" required>
				</div>
				<button type="submit" class="social-btn facebook-btn">Continue as Facebook User</button>
			</form>
			<p class="oauth-note">Set Facebook app credentials in config/oauth.php for production OAuth.</p>
			<?php else: ?>
			<p class="oauth-note">Facebook OAuth is disabled. Please configure app_id and app_secret in config/oauth.php.</p>
			<?php endif; ?>
			<a href="login.php" class="auth-link-btn" style="margin-top:14px">Back to Login</a>
		</div>
	</div>
</body>
</html>
