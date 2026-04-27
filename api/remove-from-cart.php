<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirect=../pages/cart.php');
    exit;
}

$id = (int) $_GET['id'];
$stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../pages/cart.php");
exit;
