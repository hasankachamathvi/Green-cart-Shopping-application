<?php
session_start();
$feedback_success = $_SESSION['feedback_success'] ?? '';
$feedback_error = $_SESSION['feedback_error'] ?? '';
unset($_SESSION['feedback_success'], $_SESSION['feedback_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us - GreenTrack</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="contact-screen">
<nav class="nav">
  <a class="nav-logo" href="index.php"><span>🌿</span> GreenTrack</a>
  <div class="nav-right">
    <a href="index.php" class="back-btn">Home</a>
    <a href="products.php" class="back-btn">Product</a>
    <a href="contact.php" class="back-btn">Contact Us</a>
    <a href="../auth/login.php" class="logout-btn">Log In</a>
  </div>
</nav>

<section class="contact-hero">
  <img src="https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=1600&q=80" alt="Contact GreenTrack">
  <div class="contact-hero-overlay">
    <h1>Contact GreenTrack</h1>
    <p>Need support, custom orders, or supplier partnership details? Send us a message.</p>
  </div>
</section>

<main class="contact-main">
  <section class="contact-card">
    <h2>Send Feedback</h2>
    <?php if ($feedback_success): ?>
      <div class="contact-success"><?= htmlspecialchars($feedback_success) ?></div>
    <?php endif; ?>
    <?php if ($feedback_error): ?>
      <div class="auth-error"><?= htmlspecialchars($feedback_error) ?></div>
    <?php endif; ?>

    <form action="../api/submit-feedback.php" method="POST" class="contact-form">
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required>
      </div>
      <div class="form-group">
        <label>Category</label>
        <select name="category" class="checkout-select" required>
          <option value="General">General</option>
          <option value="Product">Product</option>
          <option value="Delivery">Delivery</option>
          <option value="Payment">Payment</option>
          <option value="Service">Service</option>
        </select>
      </div>
      <div class="form-group">
        <label>Message</label>
        <textarea name="message" rows="5" class="contact-textarea" required></textarea>
      </div>
      <button type="submit" class="checkout-btn">Submit Feedback</button>
    </form>
  </section>

  <aside class="contact-info-card">
    <h3>Get in touch</h3>
    <p><strong>Email:</strong> support@greentrack.local</p>
    <p><strong>Phone:</strong> +94 77 123 4567</p>
    <p><strong>Office:</strong> Colombo, Sri Lanka</p>
    <p class="contact-note">Your feedback is visible in the admin dashboard for quick handling and service improvement.</p>
  </aside>
</main>
</body>
</html>
