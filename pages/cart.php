<?php
include("../config/db.php");

$user_id = 1;

// Get cart
$cart_sql = "SELECT * FROM carts WHERE user_id = $user_id";
$cart_result = $conn->query($cart_sql);

if ($cart_result->num_rows == 0) {
	echo "Cart is empty";
	exit;
}

$cart = $cart_result->fetch_assoc();
$cart_id = $cart['cart_id'];

// Get cart items
$sql = "SELECT p.name, p.price, ci.quantity, ci.cart_item_id
		FROM cart_items ci
		JOIN products p ON ci.product_id = p.product_id
		WHERE ci.cart_id = $cart_id";

$result = $conn->query($sql);

$total = 0;
?>

<h2>Your Cart</h2>

<table border="1" cellpadding="10">
<tr>
	<th>Product</th>
	<th>Price</th>
	<th>Quantity</th>
	<th>Subtotal</th>
	<th>Action</th>
</tr>

<?php while($row = $result->fetch_assoc()) { 
	$subtotal = $row['price'] * $row['quantity'];
	$total += $subtotal;
?>

<tr>
	<td><?php echo $row['name']; ?></td>
	<td><?php echo $row['price']; ?></td>
	<td><?php echo $row['quantity']; ?></td>
	<td><?php echo $subtotal; ?></td>
	<td>
		<a href="../api/remove-from-cart.php?id=<?php echo $row['cart_item_id']; ?>">Remove</a>
	</td>
</tr>

<?php } ?>

<tr>
	<td colspan="3"><b>Total</b></td>
	<td colspan="2">Rs. <?php echo $total; ?></td>
</tr>
</table>
