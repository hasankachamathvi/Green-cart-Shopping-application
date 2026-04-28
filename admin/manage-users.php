<?php
session_start();
include(__DIR__ . '/../config/db.php');
include(__DIR__ . '/admin-auth.php');

// Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Handle user actions
$action = $_GET['action'] ?? '';
$message = '';

if ($action === 'delete' && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    
    // Don't delete if user has orders
    $orderCheck = $conn->query("SELECT COUNT(*) as cnt FROM orders WHERE user_id = $user_id");
    $orderCount = $orderCheck->fetch_assoc()['cnt'];
    
    if ($orderCount == 0) {
        $conn->query("DELETE FROM carts WHERE user_id = $user_id");
        $conn->query("DELETE FROM users WHERE user_id = $user_id");
        $message = '✓ User deleted successfully';
    } else {
        $message = '✗ Cannot delete user with existing orders';
    }
    header('Location: manage-users.php?message=' . urlencode($message));
    exit;
}

// Get message from session
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Fetch all users with their statistics
$usersQuery = "
    SELECT 
        u.user_id,
        u.name,
        u.email,
        u.login_type,
        u.created_at,
        COUNT(DISTINCT o.order_id) as total_orders,
        COALESCE(SUM(o.total_amount), 0) as total_spent
    FROM users u
    LEFT JOIN orders o ON u.user_id = o.user_id
    GROUP BY u.user_id, u.name, u.email, u.login_type, u.created_at
    ORDER BY u.created_at DESC
";

$usersResult = $conn->query($usersQuery);
$users = [];

while ($user = $usersResult->fetch_assoc()) {
    $users[] = $user;
}

// Statistics
$totalUsers = count($users);
$activeUsers = count(array_filter($users, fn($u) => $u['total_orders'] > 0));

function getLoginTypeIcon($type) {
    $icons = [
        'google' => '🔍',
        'facebook' => '📘',
        'passkey' => '🔐',
        'manual' => '👤',
    ];
    return $icons[$type] ?? '?';
}

function formatDate($date) {
    return date('d M Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - GreenCart Admin</title>
    <style>
        .admin-main {
            flex: 1;
            padding: 30px;
            background: #f5f5f5;
        }
        
        .header {
            margin-bottom: 30px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #2d5016;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #2d5016;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 13px;
            color: #999;
            text-transform: uppercase;
        }
        
        .users-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h2 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
        }
        
        .search-box input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .users-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .users-table thead {
            background: #f9f9f9;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .users-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .users-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        .users-table tbody tr:hover {
            background: #fafafa;
        }
        
        .user-email {
            color: #666;
            font-size: 13px;
        }
        
        .login-type-badge {
            display: inline-block;
            padding: 4px 8px;
            background: #f0f0f0;
            border-radius: 4px;
            font-size: 12px;
            color: #666;
            margin-right: 5px;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-action {
            padding: 6px 12px;
            border: 1px solid #ddd;
            background: white;
            color: #666;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .btn-action:hover {
            background: #f5f5f5;
            border-color: #999;
        }
        
        .btn-delete {
            color: #f44336;
            border-color: #f44336;
        }
        
        .btn-delete:hover {
            background: #ffebee;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .message {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
    <div class="admin-wrapper">
        <?php include(__DIR__ . '/admin-sidebar.php'); ?>
        
        <div class="admin-main">
            <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
            
            <div class="header">
                <h1>👥 Manage Users</h1>
                <p style="color: #999; margin: 5px 0;">View and manage all registered users</p>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?= strpos($message, '✗') === 0 ? 'error' : 'success' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $totalUsers ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $activeUsers ?></div>
                    <div class="stat-label">Active Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($users, fn($u) => $u['login_type'] === 'google')) ?></div>
                    <div class="stat-label">Google Users</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($users, fn($u) => $u['login_type'] === 'facebook')) ?></div>
                    <div class="stat-label">Facebook Users</div>
                </div>
            </div>
            
            <div class="users-table-container">
                <div class="table-header">
                    <h2>All Users (<?= count($users) ?>)</h2>
                    <div class="search-box">
                        <input type="text" id="userSearch" placeholder="Search by name or email...">
                    </div>
                </div>
                
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <p>No users found</p>
                    </div>
                <?php else: ?>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User Name</th>
                                <th>Email</th>
                                <th>Login Type</th>
                                <th>Orders</th>
                                <th>Total Spent</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr class="user-row" data-name="<?= htmlspecialchars(strtolower($user['name'])) ?>" data-email="<?= htmlspecialchars(strtolower($user['email'])) ?>">
                                    <td>
                                        <strong><?= htmlspecialchars($user['name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                                    </td>
                                    <td>
                                        <span class="login-type-badge">
                                            <?= getLoginTypeIcon($user['login_type']) ?> 
                                            <?= ucfirst($user['login_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= $user['total_orders'] ?></td>
                                    <td>Rs. <?= number_format($user['total_spent'], 2) ?></td>
                                    <td><?= formatDate($user['created_at']) ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="?action=view&user_id=<?= $user['user_id'] ?>" class="btn-action">View</a>
                                            <?php if ($user['total_orders'] == 0): ?>
                                                <a href="?action=delete&user_id=<?= $user['user_id'] ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Search functionality
        const searchInput = document.getElementById('userSearch');
        const userRows = document.querySelectorAll('.user-row');
        
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            userRows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
