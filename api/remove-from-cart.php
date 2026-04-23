<?php
include("../config/db.php");

$id = $_GET['id'];

$conn->query("DELETE FROM cart_items WHERE cart_item_id = $id");

header("Location: ../pages/cart.php");
?>
