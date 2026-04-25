<?php
include(__DIR__ . '/admin-auth.php');
ensureAdminSetup($conn);

$login_error = '';

if (isset($_GET['logout'])) {
		unset($_SESSION['admin_id'], $_SESSION['admin_name']);
		header('Location: dashboard.php');
		exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_login'])) {
		$username = trim($_POST['username'] ?? '');
		$password = $_POST['password'] ?? '';

		$stmt = $conn->prepare('SELECT admin_id, full_name, password_hash FROM admin_users WHERE username = ? LIMIT 1');
		$stmt->bind_param('s', $username);
		$stmt->execute();
		$admin = $stmt->get_result()->fetch_assoc();

		if ($admin && password_verify($password, $admin['password_hash'])) {
				$_SESSION['admin_id'] = (int)$admin['admin_id'];
				$_SESSION['admin_name'] = $admin['full_name'];
				header('Location: dashboard.php');
				exit;
		}

		$login_error = 'Invalid admin username or password.';
}

if (!isset($_SESSION['admin_id'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Login - GreenTrack</title>
	<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body admin-auth-screen">
	<div class="auth-container">
		<div class="auth-logo">🧩 GreenTrack Admin</div>
		<div class="auth-card">
			<h1 class="auth-title">Admin Login</h1>
			<p class="auth-subtitle">Use default credentials: admin / admin123</p>
			<?php if ($login_error): ?><div class="auth-error"><?= htmlspecialchars($login_error) ?></div><?php endif; ?>
			<form method="POST" class="auth-form">
				<input type="hidden" name="admin_login" value="1">
				<div class="form-group">
					<label>Username</label>
					<input type="text" name="username" required>
				</div>
				<div class="form-group">
					<label>Password</label>
					<input type="password" name="password" required>
				</div>
				<button class="auth-btn" type="submit">Sign In</button>
			</form>
		</div>
	</div>
</body>
</html>
<?php
exit;
endif;

if (isset($_GET['feedback_id'], $_GET['set_status'])) {
		$feedback_id = (int)$_GET['feedback_id'];
		$set_status = $_GET['set_status'];
		$allowed = ['new', 'reviewed', 'resolved'];
		if (in_array($set_status, $allowed, true)) {
				$fstmt = $conn->prepare('UPDATE feedbacks SET status = ? WHERE feedback_id = ?');
				$fstmt->bind_param('si', $set_status, $feedback_id);
				$fstmt->execute();
		}
		header('Location: dashboard.php');
		exit;
}

$cards = [
		'products' => (int)($conn->query('SELECT COUNT(*) c FROM products')->fetch_assoc()['c'] ?? 0),
		'users' => (int)($conn->query('SELECT COUNT(*) c FROM users')->fetch_assoc()['c'] ?? 0),
		'orders' => (int)($conn->query('SELECT COUNT(*) c FROM orders')->fetch_assoc()['c'] ?? 0),
		'feedbacks' => (int)($conn->query('SELECT COUNT(*) c FROM feedbacks')->fetch_assoc()['c'] ?? 0),
];
$revenue_row = $conn->query("SELECT COALESCE(SUM(total_amount),0) AS total FROM orders WHERE status IN ('pending','completed')")->fetch_assoc();
$cards['revenue'] = (float)($revenue_row['total'] ?? 0);

$orders = $conn->query('SELECT order_id, customer_name, total_amount, payment_method, payment_status, status, order_date FROM orders ORDER BY order_date DESC LIMIT 8');
$feedbacks = $conn->query('SELECT feedback_id, category, name, email, message, status, created_at FROM feedbacks ORDER BY created_at DESC LIMIT 8');
$payments = $conn->query('SELECT payment_id, order_id, method, amount, status, transaction_ref, created_at FROM payments ORDER BY created_at DESC LIMIT 8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Admin Dashboard - GreenTrack</title>
	<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-page">
<nav class="nav">
	<a class="nav-logo" href="../pages/index.php"><span>🌿</span> GreenTrack Admin</a>
	<div class="nav-right">
		<a href="manage-orders.php" class="back-btn">Orders</a>
		<a href="manage-payments.php" class="back-btn">Payments</a>
		<a href="manage-feedback.php" class="back-btn">Feedback</a>
		<a href="add-product.php" class="back-btn">Add Product</a>
		<a href="manage-category.php" class="back-btn">Categories</a>
		<a href="dashboard.php?logout=1" class="logout-btn">Log Out</a>
	</div>
</nav>

<main class="admin-wrap">
	<h1>Dashboard</h1>
	<p class="admin-subtitle">Welcome, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>.</p>

	<section class="admin-cards">
		<article class="admin-card"><h3>Total Products</h3><p><?= $cards['products'] ?></p></article>
		<article class="admin-card"><h3>Total Users</h3><p><?= $cards['users'] ?></p></article>
		<article class="admin-card"><h3>Total Orders</h3><p><?= $cards['orders'] ?></p></article>
		<article class="admin-card"><h3>Feedbacks</h3><p><?= $cards['feedbacks'] ?></p></article>
		<article class="admin-card"><h3>Revenue</h3><p>Rs. <?= number_format($cards['revenue'], 2) ?></p></article>
	</section>

	<section class="admin-table-card">
		<h2>Recent Orders</h2>
		<div class="admin-table-wrap">
			<table class="admin-table">
				<tr><th>Order</th><th>Customer</th><th>Amount</th><th>Payment</th><th>Status</th><th>Date</th></tr>
				<?php while ($o = $orders->fetch_assoc()): ?>
					<tr>
						<td>#<?= (int)$o['order_id'] ?></td>
						<td><?= htmlspecialchars($o['customer_name'] ?: '-') ?></td>
						<td>Rs. <?= number_format($o['total_amount'], 2) ?></td>
						<td><?= htmlspecialchars($o['payment_method'] ?: '-') ?> (<?= htmlspecialchars($o['payment_status'] ?: '-') ?>)</td>
						<td><?= htmlspecialchars($o['status']) ?></td>
						<td><?= htmlspecialchars($o['order_date']) ?></td>
					</tr>
				<?php endwhile; ?>
			</table>
		</div>
	</section>

	<section class="admin-table-card">
		<h2>Recent Payments</h2>
		<div class="admin-table-wrap">
			<table class="admin-table">
				<tr><th>ID</th><th>Order</th><th>Method</th><th>Amount</th><th>Status</th><th>Ref</th><th>Date</th></tr>
				<?php while ($p = $payments->fetch_assoc()): ?>
					<tr>
						<td><?= (int)$p['payment_id'] ?></td>
						<td>#<?= (int)$p['order_id'] ?></td>
						<td><?= htmlspecialchars($p['method']) ?></td>
						<td>Rs. <?= number_format($p['amount'], 2) ?></td>
						<td><?= htmlspecialchars($p['status']) ?></td>
						<td><?= htmlspecialchars($p['transaction_ref'] ?: '-') ?></td>
						<td><?= htmlspecialchars($p['created_at']) ?></td>
					</tr>
				<?php endwhile; ?>
			</table>
		</div>
	</section>

	<section class="admin-table-card">
		<h2>Customer Feedback</h2>
		<div class="admin-table-wrap">
			<table class="admin-table">
				<tr><th>Category</th><th>Name</th><th>Email</th><th>Message</th><th>Status</th><th>Action</th></tr>
				<?php while ($f = $feedbacks->fetch_assoc()): ?>
					<tr>
						<td><?= htmlspecialchars($f['category']) ?></td>
						<td><?= htmlspecialchars($f['name']) ?></td>
						<td><?= htmlspecialchars($f['email']) ?></td>
						<td><?= htmlspecialchars($f['message']) ?></td>
						<td><?= htmlspecialchars($f['status']) ?></td>
						<td>
							<a class="table-action" href="dashboard.php?feedback_id=<?= (int)$f['feedback_id'] ?>&set_status=reviewed">Review</a>
							<a class="table-action" href="dashboard.php?feedback_id=<?= (int)$f['feedback_id'] ?>&set_status=resolved">Resolve</a>
						</td>
					</tr>
				<?php endwhile; ?>
			</table>
		</div>
	</section>
</main>
</body>
</html>
