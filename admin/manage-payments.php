<?php
include(__DIR__ . '/admin-auth.php');
ensureAdminSetup($conn);
requireAdmin();

if (isset($_GET['id'], $_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $allowed = ['pending', 'paid', 'failed'];
    if (in_array($status, $allowed, true)) {
        $stmt = $conn->prepare('UPDATE payments SET status = ? WHERE payment_id = ?');
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();

        $row = $conn->query('SELECT order_id FROM payments WHERE payment_id = ' . $id)->fetch_assoc();
        if ($row) {
            $orderId = (int)$row['order_id'];
            $upOrder = $conn->prepare('UPDATE orders SET payment_status = ? WHERE order_id = ?');
            $upOrder->bind_param('si', $status, $orderId);
            $upOrder->execute();
        }
    }
    header('Location: manage-payments.php');
    exit;
}

$payments = $conn->query('SELECT payment_id, order_id, user_id, method, amount, status, transaction_ref, created_at FROM payments ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Payments - Admin</title>
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
  <h1>Manage Payments</h1>
  <section class="admin-table-card">
    <div class="admin-table-wrap">
      <table class="admin-table">
        <tr><th>ID</th><th>Order</th><th>User</th><th>Method</th><th>Amount</th><th>Status</th><th>Ref</th><th>Date</th><th>Action</th></tr>
        <?php while ($p = $payments->fetch_assoc()): ?>
        <tr>
          <td><?= (int)$p['payment_id'] ?></td>
          <td>#<?= (int)$p['order_id'] ?></td>
          <td><?= (int)$p['user_id'] ?></td>
          <td><?= htmlspecialchars($p['method']) ?></td>
          <td>Rs. <?= number_format((float)$p['amount'], 2) ?></td>
          <td><?= htmlspecialchars($p['status']) ?></td>
          <td><?= htmlspecialchars($p['transaction_ref'] ?: '-') ?></td>
          <td><?= htmlspecialchars($p['created_at']) ?></td>
          <td>
            <a class="table-action" href="manage-payments.php?id=<?= (int)$p['payment_id'] ?>&status=paid">Mark Paid</a>
            <a class="table-action danger" href="manage-payments.php?id=<?= (int)$p['payment_id'] ?>&status=failed">Mark Failed</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </section>
</main>
</body>
</html>
