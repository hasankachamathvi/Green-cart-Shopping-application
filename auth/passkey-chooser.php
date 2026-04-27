<?php
session_start();
include(__DIR__ . '/../config/db.php');
include(__DIR__ . '/oauth-helpers.php');

$redirect = safeRedirectTarget($_GET['redirect'] ?? '../pages/products.php');
$action = $_GET['action'] ?? 'choose';

// Demo passkey accounts
$demoAccounts = [
    ['email' => 'hasanka.wijesinghe23@gmail.com', 'name' => 'Hasanka Wijesinghe', 'avatar' => 'H'],
    ['email' => 'chamathvi.wijesinghe@gmail.com', 'name' => 'Chamathvi Wijesinghe', 'avatar' => 'C'],
    ['email' => 'demo.user@passkey.com', 'name' => 'Demo User', 'avatar' => 'D'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passkey Authentication - GreenCart</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=20260425">
    <style>
        .passkey-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .passkey-title {
            text-align: center;
            margin-bottom: 24px;
            font-size: 28px;
            font-weight: 600;
            color: #333;
        }
        .passkey-subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 32px;
            font-size: 14px;
        }
        .option-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }
        .option-card {
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
            text-decoration: none;
            color: #333;
        }
        .option-card:hover {
            background: #f5f5f5;
            border-color: #8b7c5e;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .option-icon {
            font-size: 32px;
            margin-bottom: 8px;
        }
        .option-text {
            font-weight: 500;
            font-size: 14px;
        }
        .account-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
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
            border-color: #8b7c5e;
        }
        .account-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #8b7c5e;
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
        .back-link {
            display: block;
            text-align: center;
            color: #8b7c5e;
            text-decoration: none;
            font-size: 13px;
            margin-top: 16px;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .demo-note {
            text-align: center;
            font-size: 12px;
            color: #999;
            margin-top: 16px;
            padding: 12px;
            background: #f9f9f9;
            border-radius: 6px;
        }
    </style>
</head>
<body class="auth-body auth-screen">
    <div class="auth-container">
        <div class="auth-logo">🌿 GreenCart</div>
        <div class="auth-card passkey-container">
            <div class="passkey-title">🔐 Passkey Login</div>
            <div class="passkey-subtitle">Use biometric or security key to sign in</div>

            <?php if ($action === 'choose'): ?>
                <div class="option-grid">
                    <a href="?action=register&redirect=<?= urlencode($redirect) ?>" class="option-card">
                        <div class="option-icon">➕</div>
                        <div class="option-text">Register Passkey</div>
                    </a>
                    <a href="?action=login&redirect=<?= urlencode($redirect) ?>" class="option-card">
                        <div class="option-icon">✓</div>
                        <div class="option-text">Sign In with Passkey</div>
                    </a>
                </div>
                
                <div style="text-align: center; margin: 24px 0; color: #999; font-size: 12px;">OR USE DEMO ACCOUNT</div>
                
                <div class="account-list">
                    <?php foreach ($demoAccounts as $account): ?>
                        <form method="POST" action="passkey-auth.php" class="account-item" style="display: flex;">
                            <input type="hidden" name="action" value="demo-login">
                            <input type="hidden" name="email" value="<?= htmlspecialchars($account['email']) ?>">
                            <input type="hidden" name="name" value="<?= htmlspecialchars($account['name']) ?>">
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
                            
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

            <?php elseif ($action === 'register'): ?>
                <div style="padding: 20px; background: #f5f5f5; border-radius: 8px; text-align: center;">
                    <p style="margin: 0; color: #666; font-size: 14px;">Register a passkey using your biometric or security key</p>
                    <form id="passkey-register" method="POST" style="margin-top: 16px;">
                        <input type="email" name="email" placeholder="Enter email" required style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            margin-bottom: 12px;
                            box-sizing: border-box;
                        ">
                        <input type="text" name="name" placeholder="Enter name" required style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            margin-bottom: 12px;
                            box-sizing: border-box;
                        ">
                        <button type="submit" class="social-btn" style="background: #8b7c5e; width: 100%;">Register Passkey</button>
                    </form>
                </div>

            <?php elseif ($action === 'login'): ?>
                <div style="padding: 20px; background: #f5f5f5; border-radius: 8px; text-align: center;">
                    <p style="margin: 0; color: #666; font-size: 14px;">Use your registered passkey to sign in</p>
                    <form id="passkey-login" method="POST" style="margin-top: 16px;">
                        <input type="email" name="email" placeholder="Enter registered email" required style="
                            width: 100%;
                            padding: 12px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            margin-bottom: 12px;
                            box-sizing: border-box;
                        ">
                        <button type="submit" class="social-btn" style="background: #8b7c5e; width: 100%;">Sign In with Passkey</button>
                    </form>
                </div>
            <?php endif; ?>

            <a href="login.php?redirect=<?= urlencode($redirect) ?>" class="back-link">Back to Login</a>
        </div>
    </div>
</body>
</html>
