<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirect=../pages/checkout.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$full_name = trim($_POST['full_name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address_line = trim($_POST['address_line'] ?? '');
$city = trim($_POST['city'] ?? '');
$payment_method = $_POST['payment_method'] ?? 'cash_on_delivery';

if ($full_name === '' || $phone === '' || $address_line === '' || $city === '') {
    $_SESSION['checkout_error'] = 'Please fill all required checkout fields.';
    header("Location: ../pages/checkout.php");
    exit;
}

$allowed_methods = ['cash_on_delivery', 'card', 'bank_transfer'];
if (!in_array($payment_method, $allowed_methods, true)) {
    $payment_method = 'cash_on_delivery';
}

// Ensure extra tables/columns exist for feedback + payment handling.
$conn->query("CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    user_id INT NOT NULL,
    method ENUM('cash_on_delivery','card','bank_transfer') DEFAULT 'cash_on_delivery',
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','paid','failed') DEFAULT 'pending',
    transaction_ref VARCHAR(120) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (order_id),
    INDEX (user_id)
)");

$orderColumns = [];
$orderColRs = $conn->query("SHOW COLUMNS FROM orders");
if ($orderColRs) {
    while ($col = $orderColRs->fetch_assoc()) {
        $orderColumns[$col['Field']] = true;
    }
}

if (!isset($orderColumns['customer_name'])) {
    $conn->query("ALTER TABLE orders ADD COLUMN customer_name VARCHAR(120) NULL");
}
if (!isset($orderColumns['phone'])) {
    $conn->query("ALTER TABLE orders ADD COLUMN phone VARCHAR(30) NULL");
}
if (!isset($orderColumns['address_line'])) {
    $conn->query("ALTER TABLE orders ADD COLUMN address_line VARCHAR(255) NULL");
}
if (!isset($orderColumns['city'])) {
    $conn->query("ALTER TABLE orders ADD COLUMN city VARCHAR(120) NULL");
}
if (!isset($orderColumns['payment_method'])) {
    $conn->query("ALTER TABLE orders ADD COLUMN payment_method VARCHAR(40) NULL");
}
if (!isset($orderColumns['payment_status'])) {
    $conn->query("ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) NULL");
}

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
$payment_status = $payment_method === 'cash_on_delivery' ? 'pending' : 'paid';

$order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, customer_name, phone, address_line, city, payment_method, payment_status) VALUES (?, ?, 'pending', ?, ?, ?, ?, ?, ?)");
$order_stmt->bind_param("idssssss", $user_id, $total, $full_name, $phone, $address_line, $city, $payment_method, $payment_status);
$order_stmt->execute();
$order_id = $conn->insert_id;

// Add order items
$item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
foreach ($items as $item) {
    $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
    $item_stmt->execute();
}

// Record payment row for admin payment tracking.
$transaction_ref = strtoupper(substr($payment_method, 0, 3)) . '-' . date('YmdHis') . '-' . $order_id;
$pay_stmt = $conn->prepare("INSERT INTO payments (order_id, user_id, method, amount, status, transaction_ref) VALUES (?, ?, ?, ?, ?, ?)");
$pay_stmt->bind_param("iisdss", $order_id, $user_id, $payment_method, $total, $payment_status, $transaction_ref);
$pay_stmt->execute();

// Clear cart
$conn->query("DELETE FROM cart_items WHERE cart_id = $cart_id");

$_SESSION['order_success'] = $order_id;
$_SESSION['payment_method'] = $payment_method;
$_SESSION['payment_status'] = $payment_status;
header("Location: ../pages/order-success.php");
exit;
