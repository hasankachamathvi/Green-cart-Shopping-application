<?php
session_start();
include("../config/db.php");
$user_id = $_SESSION['user_id'] ?? 1;

// Fetch categories
$cats_result = $conn->query("SELECT * FROM categories");
$categories = [];
while ($row = $cats_result->fetch_assoc()) $categories[] = $row;

// Fetch products
$prods_result = $conn->query("SELECT p.*, c.cateagory_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id");
$products = [];
while ($row = $prods_result->fetch_assoc()) $products[] = $row;

// Get cart items for this user
$cart_items = [];
$cart_sql = "SELECT ci.product_id, ci.quantity FROM cart_items ci
             JOIN carts ca ON ci.cart_id = ca.cart_id
             WHERE ca.user_id = $user_id";
$cart_result = $conn->query($cart_sql);
while ($row = $cart_result->fetch_assoc()) {
    $cart_items[$row['product_id']] = $row['quantity'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FreshMart – Fresh Groceries</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>

<!-- NAVBAR -->
<nav class="nav">
  <div class="nav-logo"><span>🌿</span> FreshMart</div>
  <div class="nav-right">
    <input class="nav-search" type="text" placeholder="Search products..." id="searchInput" oninput="filterProducts()">
    <button class="cart-btn" onclick="toggleCart()">
      🛒 Cart <span class="cart-count" id="cartCount">0</span>
    </button>
    <a href="../auth/logout.php" class="logout-btn">Logout</a>
  </div>
</nav>

<!-- HERO -->
<div class="hero">
  <div class="hero-text">
    <div class="hero-badge">🚚 Free delivery over Rs. 1,500</div>
    <h1>Fresh from the <em>farm</em> to your door</h1>
    <p>Organic vegetables, seasonal fruits, artisan cakes & more — delivered fresh daily.</p>
    <button class="hero-cta" onclick="document.querySelector('.products-section').scrollIntoView({behavior:'smooth'})">Shop Now →</button>
  </div>
  <div class="hero-emoji">🥦</div>
</div>

<!-- PROMO BANNER -->
<div class="promo-banner">
  <span class="icon">🎉</span>
  <p><strong>Weekend deal:</strong> 10% off all fruits & vegetables today only!</p>
</div>

<!-- CATEGORIES -->
<div class="section">
  <p class="section-title">Shop by Category</p>
  <div class="cats" id="catChips">
    <button class="cat-chip active" onclick="setCategory('all', this)">All</button>
    <?php foreach ($categories as $cat): ?>
      <button class="cat-chip" onclick="setCategory('<?= $cat['category_id'] ?>', this)"><?= htmlspecialchars($cat['category_name']) ?></button>
    <?php endforeach; ?>
  </div>
</div>

<!-- PRODUCTS -->
<div class="products-section">
  <div class="products-grid" id="productsGrid">
    <?php foreach ($products as $p):
      $qty = $cart_items[$p['product_id']] ?? 0;
      $emoji = getEmoji($p['name']);
    ?>
    <div class="product-card" data-category="<?= $p['category_id'] ?>" data-name="<?= strtolower($p['name']) ?>">
      <div class="in-cart-badge<?= $qty > 0 ? ' show' : '' ?>" id="badge-<?= $p['product_id'] ?>"><?= $qty ?> in cart</div>
      <div class="product-img"><?= $emoji ?></div>
      <div class="product-info">
        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="product-desc"><?= htmlspecialchars($p['description']) ?></div>
        <div class="product-footer">
          <div class="product-price">Rs.<?= number_format($p['price'], 2) ?><span>/unit</span></div>
          <button class="add-btn" onclick="addToCart(<?= $p['product_id'] ?>, '<?= addslashes($p['name']) ?>', <?= $p['price'] ?>, '<?= $emoji ?>')">+</button>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- CART PANEL -->
<div class="cart-overlay" id="cartOverlay" onclick="handleOverlayClick(event)">
  <div class="cart-panel" id="cartPanel">
    <div class="cart-header">
      <h2>Your Cart</h2>
      <button class="close-btn" onclick="toggleCart()">✕</button>
    </div>
    <div class="cart-items" id="cartItems">
      <div class="cart-empty">
        <div class="empty-icon">🛒</div>
        <p>Your cart is empty</p>
        <p style="font-size:12px;margin-top:4px">Add some fresh items!</p>
      </div>
    </div>
    <div class="cart-footer" id="cartFooter" style="display:none">
      <div class="cart-total-row">
        <span class="cart-total-label">Total</span>
        <span class="cart-total-price" id="cartTotal">Rs. 0</span>
      </div>
      <a href="cart.php" class="checkout-btn">View Full Cart →</a>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>

<?php
function getEmoji($name) {
    $map = [
        'carrot'=>'🥕','tomato'=>'🍅','apple'=>'🍎','banana'=>'🍌',
        'chocolate cake'=>'🎂','butter biscuit'=>'🍪','broccoli'=>'🥦',
        'spinach'=>'🥬','mango'=>'🥭','grapes'=>'🍇','vanilla'=>'🍰',
        'cracker'=>'🫙','potato'=>'🥔','onion'=>'🧅','lemon'=>'🍋',
        'orange'=>'🍊','strawberry'=>'🍓','watermelon'=>'🍉',
    ];
    $name = strtolower($name);
    foreach ($map as $key => $emoji) {
        if (str_contains($name, $key)) return $emoji;
    }
    return '🛒';
}
?>

<!-- Pass PHP cart data to JS -->
<script>
const initialCart = <?= json_encode(array_map(fn($pid, $qty) => ['id' => $pid, 'qty' => $qty], array_keys($cart_items), array_values($cart_items))) ?>;
</script>
<script src="../assets/js/cart.js"></script>
</body>
</html>
