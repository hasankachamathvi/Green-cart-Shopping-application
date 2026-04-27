<?php
include("../config/db.php");

function productImagePath($image_url) {
	if (!$image_url) return '';
	if (preg_match('/^https?:\/\//i', $image_url)) return $image_url;
	return "../assets/images/" . $image_url;
}
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
	$img = productImagePath($row['image_url']);
?>

<div style="border:1px solid #000; padding:10px; margin:10px;">
	<?php if ($img): ?>
		<img src="<?php echo htmlspecialchars($img); ?>" width="100" alt="<?php echo htmlspecialchars($row['name']); ?>"><br>
	<?php endif; ?>
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
