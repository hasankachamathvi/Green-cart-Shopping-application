<?php
session_start();
include("../config/db.php");

$error = '';
$redirect = $_POST['redirect'] ?? ($_GET['redirect'] ?? '../pages/products.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // For manual login: check password (if set)
        if ($user['login_type'] === 'manual' && $user['password'] && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['name'];
          header("Location: " . $redirect);
            exit;
        } elseif ($user['login_type'] !== 'manual') {
            $error = 'This account uses ' . ucfirst($user['login_type']) . ' login.';
        } else {
            $error = 'Invalid email or password.';
        }
    } else {
        $error = 'No account found with that email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login – GreenCart</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=20260425">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
</head>
<body class="auth-body auth-screen">

<div class="auth-container">
  <div class="auth-logo">🌿 GreenCart</div>
  <div class="auth-card">
    <h1 class="auth-title">Welcome back</h1>
    <p class="auth-subtitle">Sign in to your account</p>

    <?php if ($error): ?>
      <div class="auth-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
      <div class="form-group">
        <label>Email address</label>
        <input type="email" name="email" required placeholder="you@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required placeholder="Your password">
      </div>
      <button type="submit" class="auth-btn">Sign In →</button>
    </form>

    <div class="auth-divider"><span>or</span></div>

    <div class="social-login-stack">
      <a href="google-login.php" class="social-btn google-btn">Continue with Google</a>
      <a href="facebook-login.php" class="social-btn facebook-btn">Continue with Facebook</a>
      <a href="passkey-auth.php" class="social-btn passkey-btn">Continue with Passkey</a>
    </div>

    <div class="auth-divider"><span>new here?</span></div>

    <a href="register.php" class="auth-link-btn">Create new account</a>
  </div>
</div>

</body>
</html>
