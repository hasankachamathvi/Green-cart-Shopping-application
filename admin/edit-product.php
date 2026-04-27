<?php
include(__DIR__ . '/admin-auth.php');
ensureAdminSetup($conn);
requireAdmin();

$edit_id = (int)($_GET['id'] ?? 0);

function handleProductImageUpload(array $file): array {
		if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
				return [true, ''];
		}

		if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
				return [false, 'Image upload failed. Please try again.'];
		}

		$allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
		$originalName = $file['name'] ?? '';
		$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
		if (!in_array($ext, $allowedExt, true)) {
				return [false, 'Only JPG, PNG, WEBP, or GIF files are allowed.'];
		}

		$tmpPath = $file['tmp_name'] ?? '';
		$info = @getimagesize($tmpPath);
		if ($info === false) {
				return [false, 'Uploaded file is not a valid image.'];
		}

		$imagesDir = __DIR__ . '/../assets/images/';
		if (!is_dir($imagesDir) && !mkdir($imagesDir, 0755, true)) {
				return [false, 'Unable to create images directory.'];
		}

		$filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
		$targetPath = $imagesDir . $filename;

		if (!move_uploaded_file($tmpPath, $targetPath)) {
				return [false, 'Failed to save uploaded image.'];
		}

		return [true, $filename];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
		$id = (int)($_POST['product_id'] ?? 0);
		$name = trim($_POST['name'] ?? '');
		$description = trim($_POST['description'] ?? '');
		$price = (float)($_POST['price'] ?? 0);
		$image_url = trim($_POST['image_url'] ?? '');
		$category_id = (int)($_POST['category_id'] ?? 0);
		[$uploadOk, $uploadedFilename] = handleProductImageUpload($_FILES['image_file'] ?? []);

		if (!$uploadOk) {
				header('Location: edit-product.php?id=' . $id . '&error=' . urlencode($uploadedFilename));
				exit;
		}

		if ($uploadedFilename !== '') {
				$image_url = $uploadedFilename;
		}

		$stmt = $conn->prepare('UPDATE products SET name = ?, description = ?, price = ?, image_url = ?, category_id = ? WHERE product_id = ?');
		$stmt->bind_param('ssdsii', $name, $description, $price, $image_url, $category_id, $id);
		$stmt->execute();
		header('Location: edit-product.php?id=' . $id);
		exit;
}

$categories = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_name ASC');
$products = $conn->query('SELECT product_id, name, price FROM products ORDER BY product_id DESC');

$selected = null;
if ($edit_id > 0) {
		$stmt = $conn->prepare('SELECT * FROM products WHERE product_id = ?');
		$stmt->bind_param('i', $edit_id);
		$stmt->execute();
		$selected = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Edit Products - Admin</title>
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

<main class="admin-edit-wrap">
	<section class="admin-table-card">
		<h2>Select Product to Edit</h2>
		<div class="admin-table-wrap">
			<table class="admin-table">
				<tr><th>ID</th><th>Name</th><th>Price</th><th>Actions</th></tr>
				<?php while ($p = $products->fetch_assoc()): ?>
					<tr>
						<td><?= (int)$p['product_id'] ?></td>
						<td><?= htmlspecialchars($p['name']) ?></td>
						<td>Rs. <?= number_format($p['price'], 2) ?></td>
						<td>
							<a class="table-action" href="edit-product.php?id=<?= (int)$p['product_id'] ?>">Edit</a>
							<a class="table-action danger" href="delete-product.php?id=<?= (int)$p['product_id'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
						</td>
					</tr>
				<?php endwhile; ?>
			</table>
		</div>
	</section>

	<?php if ($selected): ?>
	<section class="admin-form-card" style="margin-top:16px">
		<h2>Edit Product #<?= (int)$selected['product_id'] ?></h2>
		<?php if (!empty($_GET['error'])): ?>
			<div class="contact-success" style="background:#fdecec;color:#9f1f1f"><?= htmlspecialchars($_GET['error']) ?></div>
		<?php endif; ?>
		<form method="POST" enctype="multipart/form-data">
			<input type="hidden" name="update_product" value="1">
			<input type="hidden" name="product_id" value="<?= (int)$selected['product_id'] ?>">
			<div class="form-group"><label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($selected['name']) ?>" required></div>
			<div class="form-group"><label>Description</label><input type="text" name="description" value="<?= htmlspecialchars($selected['description']) ?>" required></div>
			<div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" value="<?= htmlspecialchars($selected['price']) ?>" required></div>
			<div class="form-group"><label>Upload New Image</label><input type="file" name="image_file" accept="image/*"></div>
			<div class="form-group"><label>Image URL</label><input type="text" name="image_url" value="<?= htmlspecialchars($selected['image_url']) ?>"></div>
			<div class="form-group">
				<label>Category</label>
				<select name="category_id" class="checkout-select" required>
					<?php while ($cat = $categories->fetch_assoc()): ?>
						<option value="<?= (int)$cat['category_id'] ?>" <?= (int)$cat['category_id'] === (int)$selected['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['category_name']) ?></option>
					<?php endwhile; ?>
				</select>
			</div>
			<button type="submit" class="checkout-btn">Update Product</button>
		</form>
	</section>
	<?php endif; ?>
</main>
</body>
</html>
