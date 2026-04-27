<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php?redirect=../pages/order-history.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Get user info
$userStmt = $conn->prepare("SELECT name, email FROM users WHERE user_id = ?");
$userStmt->bind_param("i", $user_id);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

// Get all orders for this user
$ordersStmt = $conn->prepare("
    SELECT order_id, total_amount, status, order_date 
    FROM orders 
    WHERE user_id = ? 
    ORDER BY order_date DESC
");
$ordersStmt->bind_param("i", $user_id);
$ordersStmt->execute();
$ordersResult = $ordersStmt->get_result();
$orders = [];

while ($order = $ordersResult->fetch_assoc()) {
    // Get items for each order
    $itemsStmt = $conn->prepare("
        SELECT oi.quantity, oi.price, p.name 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.product_id 
        WHERE oi.order_id = ?
    ");
    $itemsStmt->bind_param("i", $order['order_id']);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    $items = [];
    
    while ($item = $itemsResult->fetch_assoc()) {
        $items[] = $item;
    }
    
    $order['items'] = $items;
    $orders[] = $order;
}

function getStatusBadgeColor($status) {
    $colors = [
        'pending' => '#FF9800',
        'completed' => '#4CAF50',
        'cancelled' => '#f44336',
    ];
    return $colors[$status] ?? '#999';
}

function formatDate($date) {
    return date('d M Y, H:i', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History – GreenCart</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=20260427">
    <style>
        .order-history-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .order-history-header {
            margin-bottom: 30px;
        }
        
        .order-history-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #2d5016;
        }
        
        .order-history-header p {
            color: #666;
            font-size: 14px;
        }
        
        .orders-grid {
            display: grid;
            gap: 20px;
        }
        
        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #8b7c5e;
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .order-id {
            font-size: 16px;
            font-weight: 600;
            color: #2d5016;
        }
        
        .order-date {
            font-size: 13px;
            color: #999;
        }
        
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            color: white;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .order-items {
            margin: 15px 0;
            padding: 15px 0;
            border-top: 1px solid #f0f0f0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        
        .item-name {
            flex: 1;
            color: #333;
        }
        
        .item-qty {
            color: #999;
            margin: 0 10px;
        }
        
        .item-price {
            color: #2d5016;
            font-weight: 600;
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
        }
        
        .order-total {
            font-size: 16px;
            font-weight: 600;
            color: #2d5016;
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-action {
            padding: 8px 16px;
            border: 1px solid #8b7c5e;
            background: white;
            color: #8b7c5e;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .btn-action:hover {
            background: #8b7c5e;
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            margin: 10px 0;
        }
        
        .btn-shop {
            display: inline-block;
            padding: 12px 24px;
            background: #8b7c5e;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 15px;
            transition: all 0.2s ease;
        }
        
        .btn-shop:hover {
            background: #2d5016;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #8b7c5e;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <?php include("../includes/navbar.php"); ?>
    
    <div class="order-history-container">
        <a href="../pages/products.php" class="back-link">← Back to Shop</a>
        
        <div class="order-history-header">
            <h1>📦 Your Orders</h1>
            <p>Welcome back, <strong><?= htmlspecialchars($user['name']) ?></strong></p>
        </div>
        
        <?php if (empty($orders)): ?>
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <h3>No Orders Yet</h3>
                <p>You haven't placed any orders yet.</p>
                <a href="../pages/products.php" class="btn-shop">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="orders-grid">
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">Order #<?= $order['order_id'] ?></div>
                                <div class="order-date"><?= formatDate($order['order_date']) ?></div>
                            </div>
                            <span class="status-badge" style="background-color: <?= getStatusBadgeColor($order['status']) ?>;">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>
                        
                        <div class="order-items">
                            <?php foreach ($order['items'] as $item): ?>
                                <div class="order-item">
                                    <span class="item-name"><?= htmlspecialchars($item['name']) ?></span>
                                    <span class="item-qty">× <?= $item['quantity'] ?></span>
                                    <span class="item-price">Rs. <?= number_format($item['price'], 2) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="order-footer">
                            <div class="order-total">
                                Total: Rs. <?= number_format($order['total_amount'], 2) ?>
                            </div>
                            <div class="order-actions">
                                <a href="#" class="btn-action">View Details</a>
                                <?php if ($order['status'] === 'completed'): ?>
                                    <a href="#" class="btn-action">Reorder</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include("../includes/footer.php"); ?>
</body>
</html>
