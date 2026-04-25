<?php
include(__DIR__ . '/admin-auth.php');
ensureAdminSetup($conn);
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'])) {
		$category_name = trim($_POST['category_name'] ?? '');
		if ($category_name !== '') {
				$stmt = $conn->prepare('INSERT INTO categories (category_name) VALUES (?)');
				$stmt->bind_param('s', $category_name);
				$stmt->execute();
		}
		header('Location: manage-category.php');
		exit;
}

if (isset($_GET['delete'])) {
		$id = (int)$_GET['delete'];
		$stmt = $conn->prepare('DELETE FROM categories WHERE category_id = ?');
		$stmt->bind_param('i', $id);
		$stmt->execute();
		header('Location: manage-category.php');
		exit;
}

$categories = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_name ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Manage Categories - Admin</title>
	<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-page">
<nav class="nav">
	<a class="nav-logo" href="dashboard.php"><span>🧩</span> Admin</a>
	<div class="nav-right">
		<a href="dashboard.php" class="back-btn">Dashboard</a>
		<a href="add-product.php" class="back-btn">Add Product</a>
		<a href="manage-orders.php" class="back-btn">Orders</a>
		<a href="manage-feedback.php" class="back-btn">Feedback</a>
	</div>
</nav>

<main class="admin-form-wrap">
	<h1>Manage Categories</h1>
	<form method="POST" class="admin-inline-form">
		<input type="hidden" name="new_category" value="1">
		<input type="text" name="category_name" placeholder="New category name" required>
		<button type="submit" class="checkout-btn" style="width:auto;padding:10px 16px">Add Category</button>
	</form>

	<div class="admin-table-wrap" style="margin-top:18px">
		<table class="admin-table">
			<tr><th>ID</th><th>Name</th><th>Action</th></tr>
			<?php while ($cat = $categories->fetch_assoc()): ?>
			<tr>
				<td><?= (int)$cat['category_id'] ?></td>
				<td><?= htmlspecialchars($cat['category_name']) ?></td>
				<td><a class="table-action danger" href="manage-category.php?delete=<?= (int)$cat['category_id'] ?>" onclick="return confirm('Delete this category?')">Delete</a></td>
			</tr>
			<?php endwhile; ?>
		</table>
	</div>
</main>
</body>
</html>
