<?php
include(__DIR__ . '/oauth-helpers.php');
ensureAuthSchema($conn);

$error = '';
$mode = $_POST['mode'] ?? 'login';
$redirect = safeRedirectTarget($_POST['redirect'] ?? ($_GET['redirect'] ?? '../pages/products.php'));

if ($redirect !== '../pages/products.php') {
		$_SESSION['redirect_url'] = $redirect;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$name = trim($_POST['name'] ?? 'Passkey User');
		$email = trim($_POST['email'] ?? '');
		$passkey_id = trim($_POST['passkey_id'] ?? 'browser-passkey');

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$error = 'Enter a valid email.';
		} else {
				if ($mode === 'register') {
						$existsStmt = $conn->prepare("SELECT user_id, name, email FROM users WHERE email = ? LIMIT 1");
						$existsStmt->bind_param('s', $email);
						$existsStmt->execute();
						$existing = $existsStmt->get_result()->fetch_assoc();

						if ($existing) {
								$uid = (int)$existing['user_id'];
								$up = $conn->prepare("UPDATE users SET login_type = 'passkey', passkey_id = ?, name = ? WHERE user_id = ?");
								$up->bind_param('ssi', $passkey_id, $name, $uid);
								if ($up->execute()) {
										$conn->query("INSERT IGNORE INTO carts (user_id) VALUES ($uid)");
										completeLogin(['user_id' => $uid, 'name' => ($name ?: $existing['name'])], $redirect);
								} else {
										$error = 'Unable to update existing user for passkey login.';
								}
						} else {
								$stmt = $conn->prepare("INSERT INTO users (name, email, password, login_type, passkey_id) VALUES (?, ?, NULL, 'passkey', ?)");
								$stmt->bind_param('sss', $name, $email, $passkey_id);
								if ($stmt->execute()) {
										$uid = (int)$conn->insert_id;
										$conn->query("INSERT IGNORE INTO carts (user_id) VALUES ($uid)");
										completeLogin(['user_id' => $uid, 'name' => $name], $redirect);
								} else {
										$error = 'Unable to register passkey user.';
								}
						}
				} else {
						$stmt = $conn->prepare("SELECT user_id, name, passkey_id FROM users WHERE email = ? AND login_type = 'passkey' LIMIT 1");
						$stmt->bind_param('s', $email);
						$stmt->execute();
						$user = $stmt->get_result()->fetch_assoc();

						if ($user) {
								if (!empty($user['passkey_id']) && $passkey_id !== '' && $user['passkey_id'] !== $passkey_id) {
										$error = 'Passkey identifier does not match this account.';
								} else {
										completeLogin($user, $redirect);
								}
						} else {
								$error = 'No passkey user found. Register first.';
						}
				}
		}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Passkey Login - GreenCart</title>
	<link rel="stylesheet" href="../assets/css/style.css?v=20260425">
</head>
<body class="auth-body auth-screen">
	<div class="auth-container">
		<div class="auth-logo">🌿 GreenCart</div>
		<div class="auth-card">
			<h1 class="auth-title">Passkey Authentication</h1>
			<p class="auth-subtitle">Register or sign in with a passkey identity.</p>
			<?php if ($error): ?><div class="auth-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

			<form method="POST" class="auth-form">
				<input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
				<div class="form-group">
					<label>Email</label>
					<input type="email" name="email" required>
				</div>
				<div class="form-group">
					<label>Name (for registration)</label>
					<input type="text" name="name" placeholder="Passkey User">
				</div>
				<div class="form-group">
					<label>Passkey Identifier</label>
					<input type="text" name="passkey_id" value="browser-passkey">
				</div>
				<div class="passkey-actions">
					<button type="submit" name="mode" value="login" class="social-btn passkey-btn">Login with Passkey</button>
					<button type="submit" name="mode" value="register" class="social-btn google-btn">Register Passkey</button>
				</div>
			</form>

			<p class="oauth-note">For production, replace with full WebAuthn challenge/verification.</p>
			<a href="login.php?redirect=<?= urlencode($redirect) ?>" class="auth-link-btn" style="margin-top:14px">Back to Login</a>
		</div>
	</div>
</body>
</html>
