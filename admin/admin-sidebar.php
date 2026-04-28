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
    @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@400;500;700&display=swap');

    body.admin-page {
        margin: 0;
        min-height: 100vh;
        display: block !important;
        font-family: 'DM Sans', sans-serif;
        background: #f5f5f5;
        color: #2d5016;
    }

    body.admin-page h1,
    body.admin-page h2,
    body.admin-page h3,
    body.admin-page .header h1,
    body.admin-page .admin-subtitle,
    body.admin-page .admin-table-card h2,
    body.admin-page .admin-form-card h2 {
        font-family: 'Playfair Display', serif;
    }

    .admin-wrapper {
        display: flex;
        min-height: 100vh;
        background: #f5f5f5;
        font-family: 'DM Sans', sans-serif;
    }

    .admin-main,
    .admin-wrap,
    .admin-form-wrap,
    .admin-edit-wrap {
        flex: 1;
        padding: 30px;
        background: #f5f5f5;
        box-sizing: border-box;
    }
    
    .admin-sidebar-nav {
        width: 250px;
        background: #2d5016;
        color: white;
        font-family: 'DM Sans', sans-serif;
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

            .admin-main,
            .admin-wrap,
            .admin-form-wrap,
            .admin-edit-wrap {
                flex: 1;
                padding: 30px;
                background: #f5f5f5;
                box-sizing: border-box;
            }

            .admin-main h1,
            .admin-wrap h1,
            .admin-form-wrap h1,
            .admin-edit-wrap h1 {
                font-size: 28px;
                color: #2d5016;
                margin: 0 0 10px;
            }

            .admin-subtitle {
                color: #6e7d67;
                margin: 0 0 24px;
                font-size: 15px;
            }

            .admin-cards,
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 16px;
                margin-bottom: 24px;
            }

            .admin-card,
            .stat-card {
                background: #fff;
                padding: 18px 20px;
                border-radius: 14px;
                border: 1px solid #dce8d4;
                box-shadow: 0 1px 0 rgba(0,0,0,0.02);
            }

            .admin-card h3,
            .stat-label {
                margin: 0 0 10px;
                font-size: 15px;
                color: #5f6f58;
                font-weight: 600;
            }

            .admin-card p,
            .stat-value {
                margin: 0;
                color: #2d5016;
                font-size: 32px;
                font-weight: 700;
            }

            .admin-table-card,
            .admin-form-card,
            .users-table-container {
                background: #fff;
                border: 1px solid #dce8d4;
                border-radius: 16px;
                padding: 16px;
                margin-bottom: 18px;
                box-shadow: 0 1px 0 rgba(0,0,0,0.02);
            }

            .admin-table-card h2,
            .admin-form-card h2,
            .users-table-container h2 {
                margin: 0 0 14px;
                color: #2d5016;
                font-size: 22px;
            }

            .admin-table-wrap {
                overflow-x: auto;
            }

            .admin-table,
            .users-table {
                width: 100%;
                border-collapse: collapse;
                min-width: 720px;
            }

            .admin-table th,
            .users-table th {
                background: #edf6e5;
                color: #355525;
                text-align: left;
                padding: 14px 12px;
                font-size: 13px;
                text-transform: uppercase;
                letter-spacing: .02em;
            }

            .admin-table td,
            .users-table td {
                padding: 14px 12px;
                border-bottom: 1px solid #eef2ea;
                color: #334033;
                vertical-align: top;
            }

            .admin-table tr:hover td,
            .users-table tbody tr:hover td {
                background: #fbfdf8;
            }

            .table-action,
            .btn-action {
                display: inline-block;
                padding: 7px 12px;
                margin-right: 6px;
                border-radius: 8px;
                border: 1px solid #d5e3cb;
                background: #eef6e8;
                color: #355525;
                text-decoration: none;
                font-size: 13px;
                font-weight: 600;
            }

            .table-action.danger,
            .btn-delete {
                background: #fff1f1;
                border-color: #f2c8c8;
                color: #b53b3b;
            }

            .contact-success,
            .message.success {
                background: #eef8e7;
                border: 1px solid #d7e7cb;
                color: #355525;
                border-radius: 12px;
                padding: 12px 14px;
                margin-bottom: 16px;
            }

            .message.error {
                background: #fff0f0;
                border: 1px solid #f1c4c4;
                color: #a43a3a;
                border-radius: 12px;
                padding: 12px 14px;
                margin-bottom: 16px;
            }

            .admin-inline-form {
                display: flex;
                gap: 10px;
                align-items: center;
                flex-wrap: wrap;
            }

            .admin-inline-form input,
            .admin-form-card input,
            .admin-form-card textarea,
            .admin-form-card select,
            .search-box input {
                width: 100%;
                box-sizing: border-box;
                border: 1px solid #dce8d4;
                border-radius: 12px;
                padding: 12px 14px;
                font-family: 'DM Sans', sans-serif;
                font-size: 14px;
                outline: none;
                background: #fff;
            }

            .admin-form-card .form-group {
                margin-bottom: 14px;
            }

            .admin-form-card label {
                display: block;
                margin-bottom: 8px;
                color: #355525;
                font-weight: 600;
            }

            .checkout-btn,
            .hero-ghost,
            .btn-action,
            .checkout-select {
                font-family: 'DM Sans', sans-serif;
            }

            .checkout-btn {
                display: inline-block;
                width: 100%;
                border: 0;
                border-radius: 14px;
                background: #355525;
                color: #fff;
                padding: 14px 18px;
                font-weight: 700;
                cursor: pointer;
            }

            .hero-ghost {
                display: inline-block;
                background: #fff;
                color: #355525;
                border: 1px solid #dce8d4;
                border-radius: 14px;
                padding: 12px 16px;
                text-decoration: none;
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

    @media (max-width: 900px) {
        .admin-wrapper {
            flex-direction: column;
        }

        .admin-sidebar-nav {
            width: 100%;
            height: auto;
            position: relative;
        }

        .admin-main,
        .admin-wrap,
        .admin-form-wrap,
        .admin-edit-wrap {
            padding: 18px;
        }
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
