<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>GreenTrack - Fresh Organic Marketplace</title>
	<link rel="stylesheet" href="../assets/css/style.css">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
<nav class="nav">
	<a class="nav-logo" href="index.php"><span>🌿</span> GreenTrack</a>
	<div class="nav-right">
		<a href="index.php" class="back-btn">Home</a>
		<a href="products.php" class="back-btn">Product</a>
		<a href="contact.php" class="back-btn">Contact Us</a>
		<?php if (isset($_SESSION['user_id'])): ?>
			<a href="products.php" class="back-btn">Shop</a>
			<a href="../auth/logout.php" class="logout-btn">Log Out</a>
		<?php else: ?>
			<a href="../auth/login.php" class="logout-btn">Log In</a>
			<a href="../auth/register.php" class="logout-btn">Register</a>
		<?php endif; ?>
	</div>
</nav>

<section class="home-hero">
	<div class="hero-banner-overlay"></div>
	<img class="hero-banner-img" src="https://images.unsplash.com/photo-1461354464878-ad92f492a5a0?auto=format&fit=crop&w=1600&q=80" alt="Fresh vegetables banner">
	<div class="home-hero-content">
		<p class="hero-kicker">Farm to Door in Hours</p>
		<h1>Welcome to the world of GreenTrack</h1>
		<p>At GreenTrack, we bring fresh, sustainable, and organic produce to your doorstep with transparent sourcing and trusted quality.</p>
		<div class="hero-actions">
			<a href="products.php" class="hero-cta">View Products</a>
			<a href="contact.php" class="hero-ghost">Talk to Us</a>
		</div>
	</div>
</section>

<section class="home-services">
	<h2>Our Services</h2>
	<p class="home-subtitle">Your gateway to fresh, sustainable living</p>
	<div class="service-grid">
		<article class="service-card">
			<img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?auto=format&fit=crop&w=900&q=80" alt="Organic products">
			<div class="service-card-body">
				<h3>Organic Product Selection</h3>
				<p>Seasonal vegetables, fruits, and bakery picks curated for freshness and nutrition.</p>
			</div>
		</article>
		<article class="service-card">
			<img src="https://images.unsplash.com/photo-1506617420156-8e4536971650?auto=format&fit=crop&w=900&q=80" alt="Fast delivery van">
			<div class="service-card-body">
				<h3>Fast Local Delivery</h3>
				<p>Daily delivery slots with status updates so your order arrives fresh and on time.</p>
			</div>
		</article>
		<article class="service-card">
			<img src="https://images.unsplash.com/photo-1471193945509-9ad0617afabf?auto=format&fit=crop&w=900&q=80" alt="Quality guarantee">
			<div class="service-card-body">
				<h3>Quality Guaranteed</h3>
				<p>Carefully sourced produce with strict quality checks and easy replacement support.</p>
			</div>
		</article>
	</div>
</section>

<section class="home-split">
	<div class="split-image-wrap">
		<img src="https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80" alt="Basket of produce">
	</div>
	<div class="split-content">
		<h2>Freshness backed by data and trust</h2>
		<p>From supplier to shelf, each product is tagged, reviewed, and delivered with full visibility. Build your cart, checkout securely, and track every order.</p>
		<ul class="feature-list">
			<li>Daily restocked products and category-wise browsing</li>
			<li>Secure login, registration, and smooth cart checkout</li>
			<li>Dedicated admin panel for product and payment management</li>
		</ul>
		<a href="products.php" class="hero-cta">Start Shopping</a>
	</div>
</section>

<footer class="site-footer">
	<div>© <?= date('Y') ?> GreenTrack. All rights reserved.</div>
	<div class="footer-links">
		<a href="products.php">Products</a>
		<a href="contact.php">Contact</a>
		<a href="../admin/dashboard.php">Admin</a>
	</div>
</footer>
</body>
</html>
