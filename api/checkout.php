<?php
session_start();
include("../config/db.php");

$user_id = $_SESSION['user_id'] ?? 1;

// Get cart
$cart_sql = $conn->prepare("SELECT cart_id FROM carts WHERE user_id = ?");
$cart_sql->bind_param("i", $user_id);
$cart_sql->execute();
$cart_result = $cart_sql->get_result();

if ($cart_result->num_rows === 0) {
    header("Location: ../pages/cart.php");
    exit;
}

$cart_id = $cart_result->fetch_assoc()['cart_id'];

// Calculate total
$items_sql = "SELECT ci.product_id, ci.quantity, p.price
              FROM cart_items ci JOIN products p ON ci.product_id = p.product_id
              WHERE ci.cart_id = $cart_id";
$items_result = $conn->query($items_sql);
$items = [];
$total = 0;

while ($row = $items_result->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

if (empty($items)) {
    header("Location: ../pages/cart.php");
    exit;
}

// Create order
$order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
$order_stmt->bind_param("id", $user_id, $total);
$order_stmt->execute();
$order_id = $conn->insert_id;

// Add order items
$item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($items as $item) {
    $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
    $item_stmt->execute();
}

// Clear cart
$conn->query("DELETE FROM cart_items WHERE cart_id = $cart_id");

$_SESSION['order_success'] = $order_id;
header("Location: ../pages/order-success.php");
exit;
