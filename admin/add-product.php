<?php
include(__DIR__ . '/admin-auth.php');
ensureAdminSetup($conn);
requireAdmin();

$message = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$name = trim($_POST['name'] ?? '');
		$description = trim($_POST['description'] ?? '');
		$price = (float)($_POST['price'] ?? 0);
		$image_url = trim($_POST['image_url'] ?? '');
		$category_id = (int)($_POST['category_id'] ?? 0);
		$new_category_name = trim($_POST['new_category_name'] ?? '');
		[$uploadOk, $uploadedFilename] = handleProductImageUpload($_FILES['image_file'] ?? []);

		if (!$uploadOk) {
				$message = $uploadedFilename;
		}

		if ($uploadedFilename !== '') {
				$image_url = $uploadedFilename;
		}

		if ($new_category_name !== '') {
				$existing = $conn->prepare('SELECT category_id FROM categories WHERE LOWER(category_name) = LOWER(?) LIMIT 1');
				$existing->bind_param('s', $new_category_name);
				$existing->execute();
				$row = $existing->get_result()->fetch_assoc();

				if ($row) {
						$category_id = (int)$row['category_id'];
				} else {
						$createCategory = $conn->prepare('INSERT INTO categories (category_name) VALUES (?)');
						$createCategory->bind_param('s', $new_category_name);
						$createCategory->execute();
						$category_id = (int)$createCategory->insert_id;
				}
		}

		if ($message === '' && $name !== '' && $price > 0 && $category_id > 0) {
				$stmt = $conn->prepare('INSERT INTO products (name, description, price, image_url, category_id) VALUES (?, ?, ?, ?, ?)');
				$stmt->bind_param('ssdsi', $name, $description, $price, $image_url, $category_id);
				$stmt->execute();
				$message = 'Product added successfully.';
		} elseif ($message === '') {
				$message = 'Name, valid price, and category are required.';
		}
}

$categories = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_name ASC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Manage Product - Admin</title>
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
	<h1>Manage Product</h1>
	<?php if ($message): ?><div class="contact-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>

	<form method="POST" enctype="multipart/form-data" class="admin-form-card">
		<div class="form-group"><label>Name</label><input type="text" name="name" required></div>
		<div class="form-group"><label>Description</label><input type="text" name="description" required></div>
		<div class="form-group"><label>Price</label><input type="number" step="0.01" name="price" required></div>
		<div class="form-group"><label>Upload Image</label><input type="file" name="image_file" accept="image/*"></div>
		<div class="form-group"><label>Image URL (or filename in assets/images)</label><input type="text" name="image_url"></div>
		<div class="form-group">
			<label>Category</label>
			<select name="category_id" class="checkout-select" required>
				<option value="">Select category</option>
				<?php while ($cat = $categories->fetch_assoc()): ?>
					<option value="<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
				<?php endwhile; ?>
			</select>
		</div>
		<div class="form-group"><label>Or Create New Category</label><input type="text" name="new_category_name" placeholder="e.g., Dairy"></div>
		<button type="submit" class="checkout-btn">Save Product</button>
	</form>

	<div style="margin-top:18px">
		<a href="edit-product.php" class="hero-ghost">Manage Existing Products</a>
	</div>
</main>
</body>
</html>
