<?php
include(__DIR__ . '/admin-auth.php');
ensureAdminSetup($conn);
requireAdmin();

$message = '';
$edit_id = (int)($_GET['edit'] ?? 0);

function getFirstAvailableCategoryId(mysqli $conn): int {
		$result = $conn->query('SELECT category_id FROM categories ORDER BY category_id ASC');
		$nextId = 1;
		while ($row = $result->fetch_assoc()) {
				$currentId = (int)$row['category_id'];
				if ($currentId === $nextId) {
						$nextId++;
				} elseif ($currentId > $nextId) {
						break;
				}
		}
		return $nextId;
}

function redirectWithMessage(string $msg): void {
		header('Location: manage-category.php?msg=' . urlencode($msg));
		exit;
}

if (isset($_GET['msg'])) {
		$message = trim((string)$_GET['msg']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_category'])) {
		$category_name = trim($_POST['category_name'] ?? '');
		if ($category_name !== '') {
				$nextId = getFirstAvailableCategoryId($conn);
				$stmt = $conn->prepare('INSERT INTO categories (category_id, category_name) VALUES (?, ?)');
				$stmt->bind_param('is', $nextId, $category_name);
				$stmt->execute();
				redirectWithMessage('Category added successfully.');
		} else {
				redirectWithMessage('Category name cannot be empty.');
		}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_category'])) {
		$id = (int)($_POST['category_id'] ?? 0);
		$category_name = trim($_POST['category_name'] ?? '');
		
		if ($id > 0 && $category_name !== '') {
				$stmt = $conn->prepare('UPDATE categories SET category_name = ? WHERE category_id = ?');
				$stmt->bind_param('si', $category_name, $id);
				$stmt->execute();
				redirectWithMessage('Category updated successfully.');
		} else {
				redirectWithMessage('Invalid category data.');
		}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_category'])) {
		$id = (int)($_POST['category_id'] ?? 0);
		$stmt = $conn->prepare('DELETE FROM categories WHERE category_id = ?');
		$stmt->bind_param('i', $id);
		$stmt->execute();
		redirectWithMessage('Category deleted successfully.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_auto_increment'])) {
		$conn->query('ALTER TABLE categories AUTO_INCREMENT = 1');
		redirectWithMessage('Auto-increment reset to 1.');
}

$categories = $conn->query('SELECT category_id, category_name FROM categories ORDER BY category_id ASC');

$selected = null;
if ($edit_id > 0) {
		$stmt = $conn->prepare('SELECT category_id, category_name FROM categories WHERE category_id = ?');
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
	<title>Manage Categories - Admin</title>
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

<main class="admin-form-wrap">
	<h1>Manage Categories</h1>
	<?php if ($message): ?><div class="contact-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
	
	<form method="POST" class="admin-inline-form">
		<input type="hidden" name="new_category" value="1">
		<input type="text" name="category_name" placeholder="New category name" required>
		<button type="submit" class="checkout-btn" style="width:auto;padding:10px 16px">Add Category</button>
	</form>

	<div style="margin-top:12px;margin-bottom:18px">
		<form method="POST" style="display:inline">
			<input type="hidden" name="reset_auto_increment" value="1">
			<button type="submit" class="hero-ghost" style="display:inline-block;padding:8px 14px;font-size:12px;border:0;cursor:pointer" formnovalidate onclick="return confirm('Reset category IDs to start from 1?')">Reset Auto-Increment to 1</button>
		</form>
	</div>

	<div class="admin-table-wrap" style="margin-top:18px">
		<table class="admin-table">
			<tr><th>ID</th><th>Name</th><th>Actions</th></tr>
			<?php 
			$categories->data_seek(0);
			while ($cat = $categories->fetch_assoc()): 
			?>
			<tr>
				<td><?= (int)$cat['category_id'] ?></td>
				<td><?= htmlspecialchars($cat['category_name']) ?></td>
				<td>
					<a class="table-action" href="manage-category.php?edit=<?= (int)$cat['category_id'] ?>">Edit</a>
					<form method="POST" style="display:inline;margin:0">
						<input type="hidden" name="delete_category" value="1">
						<input type="hidden" name="category_id" value="<?= (int)$cat['category_id'] ?>">
						<button type="submit" class="table-action danger" formnovalidate onclick="return confirm('Delete this category? Products in this category will be uncategorized.')">Delete</button>
					</form>
				</td>
			</tr>
			<?php endwhile; ?>
		</table>
	</div>

	<?php if ($selected): ?>
	<section class="admin-form-card" style="margin-top:24px">
		<h2>Edit Category #<?= (int)$selected['category_id'] ?></h2>
		<form method="POST">
			<input type="hidden" name="update_category" value="1">
			<input type="hidden" name="category_id" value="<?= (int)$selected['category_id'] ?>">
			<div class="form-group">
				<label>Category Name</label>
				<input type="text" name="category_name" value="<?= htmlspecialchars($selected['category_name']) ?>" required>
			</div>
			<button type="submit" class="checkout-btn">Update Category</button>
		</form>
		<a href="manage-category.php" class="hero-ghost" style="display:inline-block;margin-top:10px">Cancel</a>
	</section>
	<?php endif; ?>
</main>
</body>
</html>
