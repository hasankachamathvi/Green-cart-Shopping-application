<?php
include(__DIR__ . '/admin-auth.php');
ensureAdminSetup($conn);
requireAdmin();

if (isset($_GET['id'], $_GET['status'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['status'];
    $allowed = ['new', 'reviewed', 'resolved'];
    if (in_array($status, $allowed, true)) {
        $stmt = $conn->prepare('UPDATE feedbacks SET status = ? WHERE feedback_id = ?');
        $stmt->bind_param('si', $status, $id);
        $stmt->execute();
    }
    header('Location: manage-feedback.php');
    exit;
}

$feedbacks = $conn->query('SELECT feedback_id, category, name, email, message, status, created_at FROM feedbacks ORDER BY created_at DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Feedback - Admin</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=20260429">
</head>
<body class="admin-page">
<nav class="nav">
  <a class="nav-logo" href="dashboard.php"><span>🧩</span> Admin</a>
  <div class="nav-right">
    <a href="dashboard.php" class="back-btn">Dashboard</a>
    <a href="manage-orders.php" class="back-btn">Orders</a>
    <a href="manage-payments.php" class="back-btn">Payments</a>
  </div>
</nav>

<main class="admin-wrap">
  <h1>Manage Feedback</h1>
  <section class="admin-table-card">
    <div class="admin-table-wrap">
      <table class="admin-table">
        <tr><th>ID</th><th>Category</th><th>Name</th><th>Email</th><th>Message</th><th>Status</th><th>Date</th><th>Action</th></tr>
        <?php while ($f = $feedbacks->fetch_assoc()): ?>
        <tr>
          <td><?= (int)$f['feedback_id'] ?></td>
          <td><?= htmlspecialchars($f['category']) ?></td>
          <td><?= htmlspecialchars($f['name']) ?></td>
          <td><?= htmlspecialchars($f['email']) ?></td>
          <td><?= htmlspecialchars($f['message']) ?></td>
          <td><?= htmlspecialchars($f['status']) ?></td>
          <td><?= htmlspecialchars($f['created_at']) ?></td>
          <td>
            <a class="table-action" href="manage-feedback.php?id=<?= (int)$f['feedback_id'] ?>&status=reviewed">Review</a>
            <a class="table-action" href="manage-feedback.php?id=<?= (int)$f['feedback_id'] ?>&status=resolved">Resolve</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </section>
</main>
</body>
</html>
