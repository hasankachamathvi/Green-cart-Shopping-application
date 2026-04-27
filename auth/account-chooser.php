<?php
session_start();
include(__DIR__ . '/../config/db.php');
include(__DIR__ . '/oauth-helpers.php');

$provider = $_GET['provider'] ?? '';
$redirect = safeRedirectTarget($_GET['redirect'] ?? '../pages/products.php');

if (!in_array($provider, ['google', 'facebook'])) {
    header('Location: login.php?redirect=' . urlencode($redirect));
    exit;
}

// Demo accounts for local testing
$demoAccounts = [
    'google' => [
        ['email' => 'hasanka.wijesinghe23@gmail.com', 'name' => 'Hasanka Wijesinghe', 'avatar' => 'H'],
        ['email' => 'hasanka.chamathvi@gmail.com', 'name' => 'Hasanka Chamathvi', 'avatar' => 'H'],
        ['email' => 'chamathvi.wijesinghe@gmail.com', 'name' => 'Chamathvi Wijesinghe', 'avatar' => 'C'],
        ['email' => 'greentracking.ordering@gmail.com', 'name' => 'Green Tracking Ordering', 'avatar' => 'G'],
    ],
    'facebook' => [
        ['email' => 'hasanka@facebook.com', 'name' => 'Hasanka Wijesinghe', 'avatar' => 'H'],
        ['email' => 'demo.user@facebook.com', 'name' => 'Demo User', 'avatar' => 'D'],
        ['email' => 'john.doe@facebook.com', 'name' => 'John Doe', 'avatar' => 'J'],
        ['email' => 'jane.smith@facebook.com', 'name' => 'Jane Smith', 'avatar' => 'J'],
    ]
];

$accounts = $demoAccounts[$provider] ?? [];
$providerName = ucfirst($provider);
$icon = $provider === 'google' ? '🔍' : '📘';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedEmail = $_POST['email'] ?? '';
    $selectedName = $_POST['name'] ?? '';
    
    if (filter_var($selectedEmail, FILTER_VALIDATE_EMAIL)) {
        $user = loginOrCreateSocialUser($conn, $selectedName, $selectedEmail, $provider);
        if ($user) {
            completeLogin($user, $redirect);
        }
    }
    header('Location: login.php?redirect=' . urlencode($redirect) . '&error=unable_to_login');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Account - GreenCart</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=20260425">
    <style>
        .account-chooser {
            max-width: 500px;
            margin: 0 auto;
        }
        .provider-header {
            text-align: center;
            margin-bottom: 24px;
            font-size: 28px;
            font-weight: 600;
            color: #333;
        }
        .provider-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 32px;
            font-size: 14px;
        }
        .account-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .account-item {
            display: flex;
            align-items: center;
            padding: 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
            text-decoration: none;
        }
        .account-item:hover {
            background: #f5f5f5;
            border-color: #bbb;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .account-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 20px;
            color: #fff;
            margin-right: 16px;
            flex-shrink: 0;
        }
        .account-info {
            flex: 1;
            text-align: left;
        }
        .account-name {
            font-weight: 500;
            color: #333;
            display: block;
            margin-bottom: 4px;
        }
        .account-email {
            font-size: 12px;
            color: #999;
        }
        .account-signed-out {
            font-size: 11px;
            color: #d32f2f;
            margin-top: 2px;
        }
        .divider {
            margin: 24px 0;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        .add-account-btn {
            display: flex;
            align-items: center;
            padding: 16px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
            text-decoration: none;
            color: #1f73be;
            font-weight: 500;
            justify-content: center;
            width: 100%;
            box-sizing: border-box;
        }
        .add-account-btn:hover {
            background: #f5f5f5;
            border-color: #1f73be;
        }
        .back-link {
            display: block;
            margin-top: 16px;
            text-align: center;
            color: #1f73be;
            text-decoration: none;
            font-size: 13px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        /* Provider-specific colors */
        .google .account-avatar {
            background: #4285f4;
        }
        .google .add-account-btn {
            color: #4285f4;
            border-color: #4285f4;
        }
        .google .add-account-btn:hover {
            background: #f5f5f5;
        }
        .facebook .account-avatar {
            background: #1877f2;
        }
        .facebook .add-account-btn {
            color: #1877f2;
            border-color: #1877f2;
        }
        .facebook .add-account-btn:hover {
            background: #f5f5f5;
        }
    </style>
</head>
<body class="auth-body auth-screen">
    <div class="auth-container">
        <div class="auth-logo">🌿 GreenCart</div>
        <div class="auth-card account-chooser <?= $provider ?>">
            <div class="provider-header">
                <?php
                    if ($provider === 'google') {
                        echo '🔍 Sign in with Google';
                    } else {
                        echo '📘 Sign in with Facebook';
                    }
                ?>
            </div>
            <div class="provider-subtitle">Choose an account to continue to GreenCart</div>

            <div class="account-list">
                <?php foreach ($accounts as $account): ?>
                    <form method="POST" class="account-item" style="display: flex; cursor: pointer;">
                        <input type="hidden" name="email" value="<?= htmlspecialchars($account['email']) ?>">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($account['name']) ?>">
                        
                        <div class="account-avatar"><?= htmlspecialchars(substr($account['avatar'], 0, 1)) ?></div>
                        <div class="account-info">
                            <span class="account-name"><?= htmlspecialchars($account['name']) ?></span>
                            <span class="account-email"><?= htmlspecialchars($account['email']) ?></span>
                        </div>
                        <button type="submit" style="display: none;"></button>
                        <div style="cursor: pointer; user-select: none;">→</div>
                    </form>
                <?php endforeach; ?>
            </div>

            <div class="divider">OR</div>

            <form method="POST" class="add-account-btn">
                <input type="text" name="email" placeholder="Enter email address..." required style="
                    width: 100%;
                    padding: 12px;
                    border: none;
                    border-radius: 4px;
                    background: #f5f5f5;
                    margin-bottom: 8px;
                " onclick="this.focus()">
                <input type="text" name="name" placeholder="Enter name..." required style="
                    width: 100%;
                    padding: 12px;
                    border: none;
                    border-radius: 4px;
                    background: #f5f5f5;
                " onclick="this.focus()">
                <button type="submit" style="
                    width: 100%;
                    padding: 10px;
                    background: <?= $provider === 'google' ? '#4285f4' : '#1877f2' ?>;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    margin-top: 8px;
                    cursor: pointer;
                    font-weight: 500;
                    transition: opacity 0.2s;
                " onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    Continue with New Account
                </button>
            </form>

            <a href="login.php?redirect=<?= urlencode($redirect) ?>" class="back-link">Back to Login</a>
        </div>
    </div>

    <script>
        // Make account items clickable
        document.querySelectorAll('.account-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (e.target.tagName !== 'INPUT') {
                    this.querySelector('button[type="submit"]').click();
                }
            });
        });
    </script>
</body>
</html>
