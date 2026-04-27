<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirect=' . urlencode($_SERVER['HTTP_REFERER'] ?? '../pages/products.php'));
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$product_id = (int) $_POST['product_id'];
$quantity   = max(1, (int) ($_POST['quantity'] ?? 1));

// Get or create cart
$cart_sql = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
$cart_sql->bind_param("i", $user_id);
$cart_sql->execute();
$cart_result = $cart_sql->get_result();

if ($cart_result->num_rows > 0) {
    $cart_id = $cart_result->fetch_assoc()['cart_id'];
} else {
    $new_cart = $conn->prepare("INSERT INTO carts (user_id) VALUES (?)");
    $new_cart->bind_param("i", $user_id);
    $new_cart->execute();
    $cart_id = $conn->insert_id;
}

// Check if already in cart
$check = $conn->prepare("SELECT cart_item_id FROM cart_items WHERE cart_id = ? AND product_id = ?");
$check->bind_param("ii", $cart_id, $product_id);
$check->execute();
$check_result = $check->get_result();

if ($check_result->num_rows > 0) {
    $update = $conn->prepare("UPDATE cart_items SET quantity = quantity + ? WHERE cart_id = ? AND product_id = ?");
    $update->bind_param("iii", $quantity, $cart_id, $product_id);
    $update->execute();
} else {
    $insert = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
    $insert->bind_param("iii", $cart_id, $product_id, $quantity);
    $insert->execute();
}

header("Location: ../pages/products.php");
exit;
