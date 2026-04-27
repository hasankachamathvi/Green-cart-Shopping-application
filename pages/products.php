<?php
session_start();
include("../config/db.php");
$is_logged_in = isset($_SESSION['user_id']);
$login_url = '../auth/login.php?redirect=' . urlencode('../pages/products.php');
$selected_category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;

function productImagePath($image_url) {
  if (!$image_url) return "../assets/images/default-product.svg";
  if (preg_match('/^https?:\/\//i', $image_url)) return $image_url;
  return "../assets/images/" . $image_url;
}

// Fetch categories with product counts
$cats_result = $conn->query("SELECT c.category_id, c.category_name, COUNT(p.product_id) AS product_count
                            FROM categories c
                            LEFT JOIN products p ON p.category_id = c.category_id
                            GROUP BY c.category_id, c.category_name
                            ORDER BY c.category_name ASC");
$categories = [];
while ($row = $cats_result->fetch_assoc()) $categories[] = $row;

// Fetch products, optionally filtered by category
if ($selected_category_id > 0) {
  $stmt = $conn->prepare("SELECT p.*, c.category_name
                         FROM products p
                         LEFT JOIN categories c ON p.category_id = c.category_id
                         WHERE p.category_id = ?
                         ORDER BY p.name ASC");
  $stmt->bind_param("i", $selected_category_id);
  $stmt->execute();
  $prods_result = $stmt->get_result();
} else {
  $prods_result = $conn->query("SELECT p.*, c.category_name
                               FROM products p
                               LEFT JOIN categories c ON p.category_id = c.category_id
                               ORDER BY c.category_name ASC, p.name ASC");
}

if (!$prods_result) die("Products query failed: " . $conn->error);

$products = [];
while ($row = $prods_result->fetch_assoc()) $products[] = $row;

$selected_category_name = 'All Categories';
if ($selected_category_id > 0) {
  foreach ($categories as $cat) {
    if ((int)$cat['category_id'] === $selected_category_id) {
      $selected_category_name = $cat['category_name'];
      break;
    }
  }
}

// Get cart items for this user
$cart_items = [];
if ($is_logged_in) {
  $user_id = (int)$_SESSION['user_id'];
  $cart_sql = "SELECT ci.product_id, ci.quantity FROM cart_items ci
               JOIN carts ca ON ci.cart_id = ca.cart_id
               WHERE ca.user_id = $user_id";
  $cart_result = $conn->query($cart_sql);
  while ($row = $cart_result->fetch_assoc()) {
      $cart_items[$row['product_id']] = $row['quantity'];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GreenCart – Fresh Groceries</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=20260425">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
</head>
<body class="products-page">

<!-- NAVBAR -->
<nav class="nav">
  <a class="nav-logo" href="index.php"><span>🌿</span> GreenCart</a>
  <div class="nav-right">
    <a href="index.php" class="back-btn">Home</a>
    <a href="products.php" class="back-btn">Product</a>
    <a href="about.php" class="back-btn">About Us</a>
    <a href="contact.php" class="back-btn">Contact Us</a>
    <?php if ($is_logged_in): ?>
      <a href="profile.php" class="back-btn">Profile</a>
    <?php endif; ?>
    <input class="nav-search" type="text" placeholder="Search products..." id="searchInput" oninput="filterProducts()">
    <button class="cart-btn" onclick="toggleCart()">
      🛒 Cart <span class="cart-count" id="cartCount">0</span>
    </button>
    <?php if ($is_logged_in): ?>
      <a href="../auth/logout.php" class="logout-btn">Logout</a>
    <?php else: ?>
      <a href="../auth/login.php?redirect=../pages/products.php" class="logout-btn">Log In</a>
    <?php endif; ?>
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
    <a class="cat-chip<?= $selected_category_id === 0 ? ' active' : '' ?>" href="products.php">All</a>
    <?php foreach ($categories as $cat): ?>
      <a class="cat-chip<?= $selected_category_id === (int)$cat['category_id'] ? ' active' : '' ?>" href="products.php?category=<?= (int)$cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?> (<?= (int)$cat['product_count'] ?>)</a>
    <?php endforeach; ?>
  </div>
</div>

<!-- PRODUCTS -->
<div class="products-section">
  <div style="max-width:1200px;margin:0 auto 14px auto;padding:0 16px;color:#365238;font-weight:600;">
    Showing: <?= htmlspecialchars($selected_category_name) ?> (<?= count($products) ?> products)
  </div>
  <div class="products-grid" id="productsGrid">
    <?php if (empty($products)): ?>
      <div style="grid-column:1/-1;background:#fff;border:1px solid #e3e7df;border-radius:14px;padding:28px;text-align:center;color:#5e6f59;">
        No products found in this category.
      </div>
    <?php endif; ?>
    <?php foreach ($products as $p):
      $qty = $cart_items[$p['product_id']] ?? 0;
      $emoji = getEmoji($p['name']);
      $imgPath = productImagePath($p['image_url']);
    ?>
    <div class="product-card" data-category="<?= $p['category_id'] ?>" data-name="<?= strtolower($p['name']) ?>">
      <div class="in-cart-badge<?= $qty > 0 ? ' show' : '' ?>" id="badge-<?= $p['product_id'] ?>"><?= $qty ?> in cart</div>
      <a href="product-details.php?id=<?= $p['product_id'] ?>" style="text-decoration:none">
        <div class="product-img">
          <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="product-img-tag" onerror="this.onerror=null;this.src='../assets/images/default-product.svg';this.alt='Default product image';">
          <span class="product-fallback-emoji" style="display:none"><?= $emoji ?></span>
        </div>
      </a>
      <div class="product-info">
        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
        <div class="product-desc"><?= htmlspecialchars($p['description']) ?></div>
        <div class="product-footer">
          <div class="product-price">Rs.<?= number_format($p['price'], 2) ?><span>/unit</span></div>
          <?php if ($is_logged_in): ?>
            <button class="add-btn" onclick="addToCart(<?= $p['product_id'] ?>, '<?= addslashes($p['name']) ?>', <?= $p['price'] ?>, '<?= $emoji ?>')">+</button>
          <?php else: ?>
            <a class="add-btn" href="<?= htmlspecialchars($login_url) ?>" style="text-decoration:none">+</a>
          <?php endif; ?>
        </div>
        <a href="product-details.php?id=<?= $p['product_id'] ?>" class="details-link">View details</a>
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
    if (strpos($name, $key) !== false) return $emoji;
    }
    return '🛒';
}
?>

<!-- Pass PHP cart data to JS -->
<script>
window.canAddToCart = <?= $is_logged_in ? 'true' : 'false' ?>;
window.loginUrl = <?= json_encode($login_url) ?>;
const initialCart = <?= json_encode(array_map(fn($pid, $qty) => ['id' => $pid, 'qty' => $qty], array_keys($cart_items), array_values($cart_items))) ?>;
</script>
<script src="../assets/js/cart.js"></script>
</body>
</html>
