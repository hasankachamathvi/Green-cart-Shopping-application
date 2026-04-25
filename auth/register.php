<?php
session_start();
include("../config/db.php");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $error = 'An account with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, login_type) VALUES (?, ?, ?, 'manual')");
            $stmt->bind_param("sss", $name, $email, $hashed);
            if ($stmt->execute()) {
                $user_id = $conn->insert_id;
                // Create cart for new user
                $conn->query("INSERT INTO carts (user_id) VALUES ($user_id)");
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                header("Location: ../pages/products.php");
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
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
  <title>Register – FreshMart</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
</head>
<body class="auth-body auth-screen">

<div class="auth-container">
  <div class="auth-logo">🌿 FreshMart</div>
  <div class="auth-card">
    <h1 class="auth-title">Create account</h1>
    <p class="auth-subtitle">Join FreshMart today</p>

    <?php if ($error): ?>
      <div class="auth-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <div class="form-group">
        <label>Full name</label>
        <input type="text" name="name" required placeholder="Your name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Email address</label>
        <input type="email" name="email" required placeholder="you@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required placeholder="Min. 6 characters">
      </div>
      <div class="form-group">
        <label>Confirm password</label>
        <input type="password" name="confirm_password" required placeholder="Repeat password">
      </div>
      <button type="submit" class="auth-btn">Create Account →</button>
    </form>

    <div class="auth-divider"><span>or</span></div>

    <a href="login.php" class="auth-link-btn">Sign in to existing account</a>
  </div>
</div>

</body>
</html>
