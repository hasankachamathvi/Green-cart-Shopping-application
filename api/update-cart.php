<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirect=../pages/cart.php');
    exit;
}

$id     = (int) $_POST['cart_item_id'];
$action = $_POST['action'];

if ($action === 'increase') {
    $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE cart_item_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
} elseif ($action === 'decrease') {
    // If qty would drop to 0, remove the item
    $check = $conn->prepare("SELECT quantity FROM cart_items WHERE cart_item_id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();

    if ($res && $res['quantity'] <= 1) {
        $del = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
        $del->bind_param("i", $id);
        $del->execute();
    } else {
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = quantity - 1 WHERE cart_item_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

header("Location: ../pages/cart.php");
exit;
