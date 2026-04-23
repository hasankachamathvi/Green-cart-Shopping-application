<?php
include("../config/db.php");
?>

<!DOCTYPE html>
<html>
<head>
	<title>Products</title>
</head>
<body>

<h2>Products</h2>

<?php
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
?>

<div style="border:1px solid #000; padding:10px; margin:10px;">
	<img src="../assets/images/<?php echo $row['image_url']; ?>" width="100"><br>
	<b><?php echo $row['name']; ?></b><br>
	Price: Rs. <?php echo $row['price']; ?><br>

	<form action="../api/add-to-cart.php" method="POST">
		<input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
		<input type="number" name="quantity" value="1" min="1">
		<button type="submit">Add to Cart</button>
	</form>
</div>

<?php } ?>

</body>
</html>
