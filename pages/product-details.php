<?php
session_start();
include("../config/db.php");

$product_id = (int)($_GET['id'] ?? 0);
if ($product_id <= 0) {
		header("Location: products.php");
		exit;
}

$stmt = $conn->prepare("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id WHERE p.product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
		header("Location: products.php");
		exit;
}

function productImagePath($image_url) {
		if (!$image_url) return '';
		if (preg_match('/^https?:\/\//i', $image_url)) return $image_url;
		return "../assets/images/" . $image_url;
}

$img = productImagePath($product['image_url']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($product['name']) ?> - GreenTrack</title>
	<link rel="stylesheet" href="../assets/css/style.css?v=20260425">
</head>
<body class="details-screen">
<nav class="nav">
	<a class="nav-logo" href="index.php"><span>🌿</span> GreenTrack</a>
	<div class="nav-right">
		<a href="products.php" class="back-btn">← Back to Products</a>
		<a href="contact.php" class="back-btn">Contact Us</a>
	</div>
</nav>

<main class="product-details-wrap">
	<section class="product-details-image-card">
		<?php if ($img): ?>
			<img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="details-main-image">
		<?php else: ?>
			<div class="details-image-fallback">Fresh Product</div>
		<?php endif; ?>
	</section>

	<section class="product-details-content-card">
		<p class="details-category"><?= htmlspecialchars($product['category_name'] ?? 'General') ?></p>
		<h1><?= htmlspecialchars($product['name']) ?></h1>
		<p class="details-description"><?= htmlspecialchars($product['description']) ?></p>
		<p class="details-price">Rs. <?= number_format($product['price'], 2) ?></p>

		<form action="../api/add-to-cart.php" method="POST" class="details-cart-form">
			<input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
			<label>Quantity</label>
			<input type="number" name="quantity" value="1" min="1" class="details-qty-input">
			<button type="submit" class="checkout-btn">Add to Cart</button>
		</form>
	</section>
</main>
</body>
</html>
