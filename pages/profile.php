<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
	header('Location: ../auth/login.php?redirect=../pages/profile.php');
	exit;
}

$user_id = (int)$_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'User';

$stmt = $conn->prepare('SELECT user_id, name, email, login_type, created_at FROM users WHERE user_id = ? LIMIT 1');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$orders = $conn->query('SELECT order_id, total_amount, status, payment_status, payment_method, order_date FROM orders WHERE user_id = ' . $user_id . ' ORDER BY order_date DESC LIMIT 10');
$order_count_row = $conn->query('SELECT COUNT(*) AS total_orders, COALESCE(SUM(total_amount),0) AS total_spent FROM orders WHERE user_id = ' . $user_id)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile - GreenCart</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=20260425">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
</head>
<body class="profile-screen">
<nav class="nav">
  <a class="nav-logo" href="index.php"><span>🌿</span> GreenCart</a>
  <div class="nav-right">
    <a href="products.php" class="back-btn">Products</a>
    <a href="cart.php" class="back-btn">Cart</a>
    <a href="contact.php" class="back-btn">Contact Us</a>
    <a href="../auth/logout.php" class="logout-btn">Log Out</a>
  </div>
</nav>

<main class="checkout-page" style="grid-template-columns: 1fr; max-width: 1100px;">
  <section class="checkout-form-card">
    <h1>My Profile</h1>
    <p>View your account details and order history.</p>

    <div class="checkout-items" style="margin-top:18px">
      <div class="checkout-item-row"><span>Name</span><span><?= htmlspecialchars($user['name'] ?? $user_name) ?></span></div>
      <div class="checkout-item-row"><span>Email</span><span><?= htmlspecialchars($user['email'] ?? '-') ?></span></div>
      <div class="checkout-item-row"><span>Login Type</span><span><?= htmlspecialchars(ucfirst($user['login_type'] ?? 'manual')) ?></span></div>
      <div class="checkout-item-row"><span>Member Since</span><span><?= htmlspecialchars($user['created_at'] ?? '-') ?></span></div>
      <div class="checkout-item-row"><span>Total Orders</span><span><?= (int)($order_count_row['total_orders'] ?? 0) ?></span></div>
      <div class="checkout-item-row"><span>Total Spent</span><span>Rs. <?= number_format((float)($order_count_row['total_spent'] ?? 0), 2) ?></span></div>
    </div>
  </section>

  <aside class="checkout-summary-card">
    <h2>Recent Orders</h2>
    <div class="checkout-items">
      <?php if ($orders && $orders->num_rows > 0): ?>
        <?php while ($order = $orders->fetch_assoc()): ?>
          <div class="checkout-item-row">
            <span>#<?= (int)$order['order_id'] ?> - <?= htmlspecialchars($order['status']) ?></span>
            <span>Rs. <?= number_format((float)$order['total_amount'], 2) ?></span>
          </div>
          <div class="checkout-item-row" style="font-size:12px;color:var(--muted)">
            <span><?= htmlspecialchars($order['payment_method'] ?: '-') ?> / <?= htmlspecialchars($order['payment_status'] ?: '-') ?></span>
            <span><?= htmlspecialchars($order['order_date']) ?></span>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <p style="color:var(--muted)">No orders yet.</p>
      <?php endif; ?>
    </div>
    <a href="order-history.php" class="checkout-btn" style="display: block; text-align: center; margin-top: 15px; text-decoration: none;">View All Orders →</a>
  </aside>
</main>
</body>
</html>
