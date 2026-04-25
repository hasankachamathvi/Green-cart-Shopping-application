<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About Us - GreenCart</title>
  <link rel="stylesheet" href="../assets/css/style.css?v=20260425">
</head>
<body class="about-screen">
<nav class="nav">
  <a class="nav-logo" href="index.php"><span>🌿</span> GreenCart</a>
  <div class="nav-right">
    <a href="index.php" class="back-btn">Home</a>
    <a href="products.php" class="back-btn">Product</a>
    <a href="about.php" class="back-btn">About Us</a>
    <a href="contact.php" class="back-btn">Contact Us</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="../auth/logout.php" class="logout-btn">Log Out</a>
    <?php else: ?>
      <a href="../auth/login.php" class="logout-btn">Log In</a>
    <?php endif; ?>
  </div>
</nav>

<section class="about-hero">
  <img src="https://images.unsplash.com/photo-1457296898342-cdd24585d095?auto=format&fit=crop&w=1600&q=80" alt="GreenCart team and farm network">
  <div class="about-hero-overlay">
    <h1>About GreenCart</h1>
    <p>We connect local farms to families through a reliable, transparent, and easy grocery experience.</p>
  </div>
</section>

<main class="about-main">
  <section class="about-card">
    <h2>Who We Are</h2>
    <p>GreenCart is an online grocery marketplace focused on fresh produce, healthy choices, and practical technology. Our platform makes it simple to browse categories like vegetables, fruits, cakes, and biscuits, then order confidently with clear pricing and secure checkout.</p>
  </section>

  <section class="about-grid">
    <article class="about-card">
      <h3>Our Mission</h3>
      <p>Deliver farm-fresh products quickly while supporting local suppliers and reducing food waste through smarter inventory and order tracking.</p>
    </article>
    <article class="about-card">
      <h3>Our Vision</h3>
      <p>Build a trusted digital grocery ecosystem where quality, affordability, and convenience are available to every household.</p>
    </article>
    <article class="about-card">
      <h3>What We Value</h3>
      <p>Quality first, transparent sourcing, user-friendly design, secure authentication, and responsive customer support.</p>
    </article>
  </section>

  <section class="about-stats">
    <div class="about-stat"><strong>100+</strong><span>Products managed</span></div>
    <div class="about-stat"><strong>4</strong><span>Core categories</span></div>
    <div class="about-stat"><strong>24/7</strong><span>Online shopping access</span></div>
    <div class="about-stat"><strong>1</strong><span>Unified admin panel</span></div>
  </section>
</main>

<footer class="site-footer">
  <div>© <?= date('Y') ?> GreenCart. All rights reserved.</div>
  <div class="footer-links">
    <a href="products.php">Products</a>
    <a href="about.php">About</a>
    <a href="contact.php">Contact</a>
  </div>
</footer>
</body>
</html>
