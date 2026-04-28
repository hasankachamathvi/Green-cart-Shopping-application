<?php
session_start();
$order_id = $_SESSION['order_success'] ?? null;
$payment_method = $_SESSION['payment_method'] ?? 'cash_on_delivery';
$payment_status = $_SESSION['payment_status'] ?? 'pending';
if (!$order_id) { header("Location: products.php"); exit; }
unset($_SESSION['order_success']);
unset($_SESSION['payment_method']);
unset($_SESSION['payment_status']);

function paymentLabel($method) {
  switch ($method) {
    case 'card':
      return 'Card';
    case 'bank_transfer':
      return 'Bank Transfer';
    default:
      return 'Cash on Delivery';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Order Placed – GreenCart</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=20260425">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
</head>
<body class="success-screen">
<nav class="nav">
  <div class="nav-logo"><span>🌿</span> GreenCart</div>
</nav>
<div class="success-page">
  <div class="success-card">
    <div class="success-icon">✅</div>
    <h1>Order Placed!</h1>
    <p>Your order <strong>#<?= $order_id ?></strong> has been placed successfully.</p>
    <p style="font-size:13px;color:var(--muted);margin-top:8px">Payment: <strong><?= paymentLabel($payment_method) ?></strong> | Status: <strong><?= ucfirst($payment_status) ?></strong></p>
    <p style="font-size:13px;color:var(--muted);margin-top:6px">We'll deliver your fresh groceries shortly!</p>
    <a href="products.php" class="hero-cta" style="display:inline-block;margin-top:24px;text-decoration:none">Continue Shopping →</a>
  </div>
</div>
</body>
</html>
