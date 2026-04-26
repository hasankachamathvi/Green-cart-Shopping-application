<?php
include(__DIR__ . '/admin-auth.php');
ensureAdminSetup($conn);
requireAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$name = trim($_POST['name'] ?? '');
		$description = trim($_POST['description'] ?? '');
		$price = (float)($_POST['price'] ?? 0);
		$image_url = trim($_POST['image_url'] ?? '');
		$category_id = (int)($_POST['category_id'] ?? 0);

		if ($name !== '' && $price > 0) {
				$stmt = $conn->prepare('INSERT INTO products (name, description, price, image_url, category_id) VALUES (?, ?, ?, ?, ?)');
				$stmt->bind_param('ssdsi', $name, $description, $price, $image_url, $category_id);
				$stmt->execute();
				$message = 'Product added successfully.';
		} else {
				$message = 'Name and valid price are required.';
		}
}

$categories = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_name ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Add Product - Admin</title>
	<link rel="stylesheet" href="../assets/css/style.css?v=20260430">
	<link rel="stylesheet" href="../assets/css/admin-sidebar.css?v=20260426">
</head>
<body class="admin-page">
<nav class="admin-sidebar">
	<a class="nav-logo" href="dashboard.php"><span>🧩</span> Admin</a>
	<div class="nav-right">
		<a href="dashboard.php" class="back-btn">Dashboard</a>
		<a href="manage-category.php" class="back-btn">Categories</a>
		<a href="manage-orders.php" class="back-btn">Orders</a>
		<a href="manage-payments.php" class="back-btn">Payments</a>
	</div>
</nav>

<main class="admin-form-wrap">
	<h1>Add Product</h1>
	<?php if ($message): ?><div class="contact-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

	<form method="POST" class="admin-form-card">
		<div class="form-group"><label>Name</label><input type="text" name="name" required></div>
		<div class="form-group"><label>Description</label><input type="text" name="description" required></div>
		<div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" required></div>
		<div class="form-group"><label>Image URL (or filename in assets/images)</label><input type="text" name="image_url"></div>
		<div class="form-group">
			<label>Category</label>
			<select name="category_id" class="checkout-select" required>
				<?php while ($cat = $categories->fetch_assoc()): ?>
					<option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
				<?php endwhile; ?>
			</select>
		</div>
		<button type="submit" class="checkout-btn">Save Product</button>
	</form>

	<div style="margin-top:18px">
		<a href="edit-product.php" class="hero-ghost">Manage Existing Products</a>
	</div>
</main>
</body>
</html>
