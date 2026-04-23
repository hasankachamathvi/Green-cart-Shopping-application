<?php
session_start();
include("../config/db.php");

$id = (int) $_GET['id'];
$stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_item_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: ../pages/cart.php");
exit;
