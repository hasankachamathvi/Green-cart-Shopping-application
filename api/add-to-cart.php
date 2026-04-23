<?php
include("../config/db.php");

// Assume user_id = 1 (for testing)
$user_id = 1;

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// Check if cart exists
$cart_sql = "SELECT * FROM carts WHERE user_id = $user_id";
$cart_result = $conn->query($cart_sql);

if ($cart_result->num_rows > 0) {
	$cart = $cart_result->fetch_assoc();
	$cart_id = $cart['cart_id'];
} else {
	// Create new cart
	$conn->query("INSERT INTO carts (user_id) VALUES ($user_id)");
	$cart_id = $conn->insert_id;
}

// Check if product already in cart
$check_sql = "SELECT * FROM cart_items 
			  WHERE cart_id = $cart_id AND product_id = $product_id";
$check_result = $conn->query($check_sql);

if ($check_result->num_rows > 0) {
	// Update quantity
	$conn->query("UPDATE cart_items 
				  SET quantity = quantity + $quantity 
				  WHERE cart_id = $cart_id AND product_id = $product_id");
} else {
	// Insert new item
	$conn->query("INSERT INTO cart_items (cart_id, product_id, quantity) 
				  VALUES ($cart_id, $product_id, $quantity)");
}

header("Location: ../pages/cart.php");
?>
