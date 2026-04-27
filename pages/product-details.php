<?php
session_start();
include("../config/db.php");
$is_logged_in = isset($_SESSION['user_id']);
$login_url = '../auth/login.php?redirect=' . urlencode('../pages/product-details.php?id=' . (int)($_GET['id'] ?? 0));

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
		if (!$image_url) return '../assets/images/default-product.svg';
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
	<title><?= htmlspecialchars($product['name']) ?> - GreenCart</title>
	<link rel="stylesheet" href="../assets/css/style.css?v=20260425">
</head>
<body class="details-screen">
<nav class="nav">
	<a class="nav-logo" href="index.php"><span>🌿</span> GreenCart</a>
	<div class="nav-right">
		<a href="products.php" class="back-btn">← Back to Products</a>
		<a href="about.php" class="back-btn">About Us</a>
		<a href="contact.php" class="back-btn">Contact Us</a>
		<?php if ($is_logged_in): ?>
			<a href="profile.php" class="back-btn">Profile</a>
			<a href="../auth/logout.php" class="logout-btn">Log Out</a>
		<?php else: ?>
			<a href="../auth/login.php?redirect=../pages/product-details.php?id=<?= (int)$product_id ?>" class="logout-btn">Log In</a>
		<?php endif; ?>
	</div>
</nav>

<main class="product-details-wrap">
	<section class="product-details-image-card">
		<img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="details-main-image" onerror="this.onerror=null;this.src='../assets/images/default-product.svg';this.alt='Default product image';">
	</section>

	<section class="product-details-content-card">
		<p class="details-category"><?= htmlspecialchars($product['category_name'] ?? 'General') ?></p>
		<h1><?= htmlspecialchars($product['name']) ?></h1>
		<p class="details-description"><?= htmlspecialchars($product['description']) ?></p>
		<p class="details-price">Rs. <?= number_format($product['price'], 2) ?></p>

		<?php if ($is_logged_in): ?>
		<form action="../api/add-to-cart.php" method="POST" class="details-cart-form">
			<input type="hidden" name="product_id" value="<?= (int)$product['product_id'] ?>">
			<label>Quantity</label>
			<input type="number" name="quantity" value="1" min="1" class="details-qty-input">
			<button type="submit" class="checkout-btn">Add to Cart</button>
		</form>
		<?php else: ?>
		<div class="auth-note" style="margin-top:12px">
			<a href="<?= htmlspecialchars($login_url) ?>" class="checkout-btn" style="display:inline-block;text-decoration:none">Log in to add to cart</a>
		</div>
		<?php endif; ?>
	</section>
</main>
</body>
</html>
