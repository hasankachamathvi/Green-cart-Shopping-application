<?php
include(__DIR__ . '/admin-auth.php');
ensureAdminSetup($conn);
requireAdmin();

if (isset($_GET['id'], $_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $allowed = ['pending', 'completed', 'cancelled'];
    if (in_array($status, $allowed, true)) {
        $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE order_id = ?');
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
    }
    header('Location: manage-orders.php');
    exit;
}

$orders = $conn->query('SELECT order_id, user_id, customer_name, phone, city, total_amount, payment_method, payment_status, status, order_date FROM orders ORDER BY order_date DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Orders - Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=20260430">
  <link rel="stylesheet" href="../assets/css/admin-sidebar.css?v=20260426">
</head>
<body class="admin-page">
<nav class="admin-sidebar">
  <a class="nav-logo" href="../pages/index.php"><span>🌿</span> GreenCart Admin</a>
  <div class="nav-right">
    <a href="dashboard.php" class="back-btn">Dashboard</a>
    <a href="manage-orders.php" class="back-btn">Orders</a>
    <a href="manage-payments.php" class="back-btn">Payments</a>
    <a href="manage-feedback.php" class="back-btn">Feedback</a>
    <a href="add-product.php" class="back-btn">Manage Product</a>
    <a href="manage-category.php" class="back-btn">Categories</a>
    <a href="dashboard.php?logout=1" class="logout-btn">Log Out</a>
  </div>
</nav>

<main class="admin-wrap">
  <h1>Manage Orders</h1>
  <section class="admin-table-card">
    <div class="admin-table-wrap">
      <table class="admin-table">
        <tr><th>ID</th><th>Customer</th><th>Phone</th><th>City</th><th>Total</th><th>Payment</th><th>Status</th><th>Date</th><th>Action</th></tr>
        <?php while ($o = $orders->fetch_assoc()): ?>
        <tr>
          <td>#<?= (int)$o['order_id'] ?></td>
          <td><?= htmlspecialchars($o['customer_name'] ?: ('User ' . (int)$o['user_id'])) ?></td>
          <td><?= htmlspecialchars($o['phone'] ?: '-') ?></td>
          <td><?= htmlspecialchars($o['city'] ?: '-') ?></td>
          <td>Rs. <?= number_format((float)$o['total_amount'], 2) ?></td>
          <td><?= htmlspecialchars(($o['payment_method'] ?: '-') . ' / ' . ($o['payment_status'] ?: '-')) ?></td>
          <td><?= htmlspecialchars($o['status']) ?></td>
          <td><?= htmlspecialchars($o['order_date']) ?></td>
          <td>
            <a class="table-action" href="manage-orders.php?id=<?= (int)$o['order_id'] ?>&status=completed">Complete</a>
            <a class="table-action danger" href="manage-orders.php?id=<?= (int)$o['order_id'] ?>&status=cancelled">Cancel</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </section>
</main>
</body>
</html>
