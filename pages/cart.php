<?php
session_start();
include("../config/db.php");
$user_id = $_SESSION['user_id'] ?? 1;

// Get cart
$cart_sql = "SELECT * FROM carts WHERE user_id = $user_id";
$cart_result = $conn->query($cart_sql);
$cart_items = [];
$total = 0;

if ($cart_result->num_rows > 0) {
    $cart = $cart_result->fetch_assoc();
    $cart_id = $cart['cart_id'];

    $sql = "SELECT p.product_id, p.name, p.price, p.description, ci.quantity, ci.cart_item_id
            FROM cart_items ci
            JOIN products p ON ci.product_id = p.product_id
            WHERE ci.cart_id = $cart_id";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
}

function getEmoji($name) {
    $map = ['carrot'=>'🥕','tomato'=>'🍅','apple'=>'🍎','banana'=>'🍌',
        'chocolate cake'=>'🎂','butter biscuit'=>'🍪','broccoli'=>'🥦',
        'spinach'=>'🥬','mango'=>'🥭','grapes'=>'🍇','vanilla'=>'🍰',
        'cracker'=>'🫙','potato'=>'🥔','onion'=>'🧅'];
    $name = strtolower($name);
    foreach ($map as $key => $emoji) {
        if (str_contains($name, $key)) return $emoji;
    }
    return '🛒';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Cart – GreenCart</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=20260425">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
</head>
<body class="cart-screen">

<nav class="nav">
  <div class="nav-logo"><a href="products.php" style="color:inherit;text-decoration:none;display:flex;align-items:center;gap:8px"><span>🌿</span> GreenCart</a></div>
  <div class="nav-right">
    <a href="products.php" class="back-btn">← Continue Shopping</a>
  </div>
</nav>

<div class="cart-page">
  <h1 class="page-title">Your Cart</h1>

  <?php if (empty($cart_items)): ?>
    <div class="empty-state">
      <div style="font-size:64px;margin-bottom:16px">🛒</div>
      <h2>Your cart is empty</h2>
      <p>Add some fresh items from our shop!</p>
      <a href="products.php" class="hero-cta" style="display:inline-block;margin-top:20px;text-decoration:none">Browse Products →</a>
    </div>
  <?php else: ?>

  <div class="cart-page-layout">
    <!-- ITEMS -->
    <div class="cart-page-items">
      <?php foreach ($cart_items as $item): ?>
      <div class="cart-page-item" id="item-<?= $item['cart_item_id'] ?>">
        <div class="cart-page-emoji"><?= getEmoji($item['name']) ?></div>
        <div class="cart-page-info">
          <div class="cart-page-name"><?= htmlspecialchars($item['name']) ?></div>
          <div class="cart-page-desc"><?= htmlspecialchars($item['description']) ?></div>
          <div class="cart-page-price">Rs. <?= number_format($item['price'], 2) ?> each</div>
        </div>
        <div class="cart-page-qty">
          <form action="../api/update-cart.php" method="POST" style="display:inline">
            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
            <input type="hidden" name="action" value="decrease">
            <button class="qty-btn" type="submit">−</button>
          </form>
          <span class="qty-num"><?= $item['quantity'] ?></span>
          <form action="../api/update-cart.php" method="POST" style="display:inline">
            <input type="hidden" name="cart_item_id" value="<?= $item['cart_item_id'] ?>">
            <input type="hidden" name="action" value="increase">
            <button class="qty-btn" type="submit">+</button>
          </form>
        </div>
        <div class="cart-page-subtotal">
          Rs. <?= number_format($item['price'] * $item['quantity'], 2) ?>
        </div>
        <a href="../api/remove-from-cart.php?id=<?= $item['cart_item_id'] ?>" class="remove-btn" title="Remove">🗑</a>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- SUMMARY -->
    <div class="order-summary">
      <h2 class="summary-title">Order Summary</h2>
      <div class="summary-row">
        <span>Subtotal</span>
        <span>Rs. <?= number_format($total, 2) ?></span>
      </div>
      <div class="summary-row">
        <span>Delivery</span>
        <span class="<?= $total >= 1500 ? 'free-delivery' : '' ?>"><?= $total >= 1500 ? 'FREE' : 'Rs. 150.00' ?></span>
      </div>
      <div class="summary-divider"></div>
      <div class="summary-row summary-total">
        <span>Total</span>
        <span>Rs. <?= number_format($total >= 1500 ? $total : $total + 150, 2) ?></span>
      </div>
      <?php if ($total < 1500): ?>
      <div class="delivery-hint">Add Rs. <?= number_format(1500 - $total, 2) ?> more for free delivery!</div>
      <?php endif; ?>
      <a href="checkout.php" class="checkout-btn" style="width:100%;margin-top:16px">Proceed to Payment →</a>
    </div>
  </div>

  <?php endif; ?>
</div>

</body>
</html>
