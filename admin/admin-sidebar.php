<?php
// Admin Sidebar Navigation
// Used by: manage-users.php, manage-orders.php, manage-payments.php, manage-feedback.php
if (!isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!-- Admin Sidebar included in admin pages -->
<style>
    .admin-wrapper {
        display: flex;
        min-height: 100vh;
        background: #f5f5f5;
    }
    
    .admin-sidebar-nav {
        width: 250px;
        background: #2d5016;
        color: white;
        padding: 20px 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        position: sticky;
        top: 0;
        height: 100vh;
        overflow-y: auto;
    }
    
    .admin-sidebar-header {
        padding: 20px;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 20px;
    }
    
    .admin-sidebar-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    
    .admin-sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .admin-sidebar-menu li {
        padding: 0;
        margin: 0;
    }
    
    .admin-sidebar-menu a {
        display: block;
        padding: 12px 20px;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: all 0.2s ease;
        border-left: 3px solid transparent;
    }
    
    .admin-sidebar-menu a:hover {
        background: rgba(255,255,255,0.1);
        color: white;
        border-left-color: #8b7c5e;
    }
    
    .admin-sidebar-menu a.active {
        background: rgba(255,255,255,0.15);
        color: white;
        border-left-color: #8b7c5e;
        font-weight: 600;
    }
</style>

<nav class="admin-sidebar-nav">
    <div class="admin-sidebar-header">
        <h3>🌿 GreenCart Admin</h3>
        <p style="margin: 10px 0 0 0; font-size: 12px; color: rgba(255,255,255,0.6);">
            <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?>
        </p>
    </div>
    
    <ul class="admin-sidebar-menu">
        <li><a href="dashboard.php" <?= strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false ? 'class="active"' : '' ?>>📊 Dashboard</a></li>
        <li><a href="manage-users.php" <?= strpos($_SERVER['REQUEST_URI'], 'manage-users.php') !== false ? 'class="active"' : '' ?>>👥 Users</a></li>
        <li><a href="manage-orders.php" <?= strpos($_SERVER['REQUEST_URI'], 'manage-orders.php') !== false ? 'class="active"' : '' ?>>📦 Orders</a></li>
        <li><a href="manage-payments.php" <?= strpos($_SERVER['REQUEST_URI'], 'manage-payments.php') !== false ? 'class="active"' : '' ?>>💳 Payments</a></li>
        <li><a href="manage-feedback.php" <?= strpos($_SERVER['REQUEST_URI'], 'manage-feedback.php') !== false ? 'class="active"' : '' ?>>💬 Feedback</a></li>
        <li><a href="add-product.php" <?= strpos($_SERVER['REQUEST_URI'], 'add-product.php') !== false ? 'class="active"' : '' ?>>🛍️ Products</a></li>
        <li><a href="manage-category.php" <?= strpos($_SERVER['REQUEST_URI'], 'manage-category.php') !== false ? 'class="active"' : '' ?>>📂 Categories</a></li>
    </ul>
    
    <div style="padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 20px;">
        <a href="../pages/index.php" style="display: block; padding: 8px 0; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 13px; margin-bottom: 8px;">← Back to Store</a>
        <a href="dashboard.php?logout=1" style="display: block; padding: 8px 0; color: #ff6b6b; text-decoration: none; font-size: 13px;">Log Out</a>
    </div>
</nav>
