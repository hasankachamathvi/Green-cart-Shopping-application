<?php
session_start();
include("../config/db.php");

$user_id = $_SESSION['user_id'] ?? 1;
$checkout_error = $_SESSION['checkout_error'] ?? '';
unset($_SESSION['checkout_error']);

$cart_sql = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
$cart_sql->bind_param("i", $user_id);
$cart_sql->execute();
$cart_result = $cart_sql->get_result();

if ($cart_result->num_rows === 0) {
		header("Location: cart.php");
		exit;
}

$cart_id = $cart_result->fetch_assoc()['cart_id'];
$items_sql = "SELECT p.name, p.price, ci.quantity
							FROM cart_items ci JOIN products p ON ci.product_id = p.product_id
							WHERE ci.cart_id = $cart_id";
$items_result = $conn->query($items_sql);

$items = [];
$subtotal = 0;
while ($row = $items_result->fetch_assoc()) {
		$items[] = $row;
		$subtotal += $row['price'] * $row['quantity'];
}

if (!$items) {
		header("Location: cart.php");
		exit;
}

$delivery = $subtotal >= 1500 ? 0 : 150;
$total = $subtotal + $delivery;
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Checkout & Payment - GreenCart</title>
	<link rel="stylesheet" href="../assets/css/style.css?v=20260425">
</head>
<body class="checkout-screen">
<nav class="nav">
	<a class="nav-logo" href="index.php"><span>🌿</span> GreenCart</a>
	<div class="nav-right">
		<a href="cart.php" class="back-btn">← Back to Cart</a>
		<a href="products.php" class="back-btn">Products</a>
	</div>
</nav>

<main class="checkout-page">
	<section class="checkout-form-card">
		<h1>Checkout</h1>
		<p>Enter delivery and payment details to complete your order.</p>

		<?php if ($checkout_error): ?>
			<div class="auth-error" style="margin-top:14px"><?= htmlspecialchars($checkout_error) ?></div>
		<?php endif; ?>

		<form action="../api/checkout.php" method="POST" class="checkout-form">
			<div class="form-group">
				<label>Full Name</label>
				<input type="text" name="full_name" required>
			</div>
			<div class="form-group">
				<label>Phone Number</label>
				<input type="text" name="phone" required>
			</div>
			<div class="form-group">
				<label>Address</label>
				<input type="text" name="address_line" required>
			</div>
			<div class="form-group">
				<label>City</label>
				<input type="text" name="city" required>
			</div>

			<div class="form-group">
				<label>Payment Method</label>
				<select name="payment_method" class="checkout-select" required>
					<option value="cash_on_delivery">Cash on Delivery</option>
					<option value="card">Card</option>
					<option value="bank_transfer">Bank Transfer</option>
				</select>
			</div>

			<button type="submit" class="checkout-btn" style="margin-top:8px">Confirm & Place Order</button>
		</form>
	</section>

	<aside class="checkout-summary-card">
		<h2>Order Summary</h2>
		<div class="checkout-items">
			<?php foreach ($items as $item): ?>
				<div class="checkout-item-row">
					<span><?= htmlspecialchars($item['name']) ?> x <?= (int)$item['quantity'] ?></span>
					<span>Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="summary-divider"></div>
		<div class="checkout-item-row"><strong>Subtotal</strong><strong>Rs. <?= number_format($subtotal, 2) ?></strong></div>
		<div class="checkout-item-row"><span>Delivery</span><span><?= $delivery === 0 ? 'FREE' : 'Rs. ' . number_format($delivery, 2) ?></span></div>
		<div class="checkout-item-row checkout-grand-total"><strong>Total</strong><strong>Rs. <?= number_format($total, 2) ?></strong></div>
	</aside>
</main>
</body>
</html>
