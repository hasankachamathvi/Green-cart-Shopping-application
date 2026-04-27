<?php
// Email Configuration and Helper Functions

return [
    // Email sender configuration
    'from_email' => 'noreply@greencart.local',
    'from_name' => 'GreenCart',
    
    // For production, use actual SMTP settings
    // 'smtp_host' => 'smtp.mailtrap.io',
    // 'smtp_port' => 587,
    // 'smtp_user' => 'your_user',
    // 'smtp_pass' => 'your_pass',
    
    // For localhost/development, we'll use PHP's mail() function
    'use_mail_function' => true,
];

/**
 * Send order confirmation email
 * 
 * @param mysqli $conn Database connection
 * @param int $order_id Order ID
 * @param string $user_email User's email
 * @param string $user_name User's name
 * @param array $items Order items
 * @param float $total Total amount
 * @return bool Success status
 */
function sendOrderConfirmationEmail($conn, $order_id, $user_email, $user_name, $items, $total) {
    $config = include(__DIR__ . '/email.php');
    
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Build HTML email
    $subject = "Order Confirmation #{$order_id} - GreenCart";
    
    $itemsList = '';
    foreach ($items as $item) {
        $subtotal = $item['price'] * $item['quantity'];
        $itemsList .= "
            <tr style=\"border-bottom: 1px solid #eee; padding: 12px 0;\">
                <td style=\"padding: 10px; text-align: left;\">{$item['name']}</td>
                <td style=\"padding: 10px; text-align: center;\">{$item['quantity']}</td>
                <td style=\"padding: 10px; text-align: right;\">Rs. " . number_format($item['price'], 2) . "</td>
                <td style=\"padding: 10px; text-align: right;\">Rs. " . number_format($subtotal, 2) . "</td>
            </tr>";
    }
    
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2d5016; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 20px; }
            .footer { background: #eee; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
            table { width: 100%; border-collapse: collapse; }
            .total-row { background: #2d5016; color: white; font-weight: bold; }
            .total-row td { padding: 12px; text-align: right; }
            .button { display: inline-block; padding: 12px 24px; background: #8b7c5e; color: white; text-decoration: none; border-radius: 4px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌿 GreenCart</h1>
                <p>Order Confirmation</p>
            </div>
            
            <div class='content'>
                <p>Hello <strong>{$user_name}</strong>,</p>
                
                <p>Thank you for your order! Your order has been received and is being processed.</p>
                
                <h3>Order Details</h3>
                <p><strong>Order ID:</strong> #{$order_id}</p>
                <p><strong>Order Date:</strong> " . date('d M Y, H:i') . "</p>
                
                <h3>Items Ordered</h3>
                <table style=\"border: 1px solid #ddd;\">
                    <thead style=\"background: #f0f0f0;\">
                        <tr>
                            <th style=\"padding: 10px; text-align: left;\">Product</th>
                            <th style=\"padding: 10px; text-align: center;\">Qty</th>
                            <th style=\"padding: 10px; text-align: right;\">Price</th>
                            <th style=\"padding: 10px; text-align: right;\">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsList}
                    </tbody>
                </table>
                
                <table style=\"margin-top: 20px; width: 100%; text-align: right;\">
                    <tr class='total-row'>
                        <td colspan='3' style=\"padding: 15px; text-align: right;\">Total Amount: <strong>Rs. " . number_format($total, 2) . "</strong></td>
                    </tr>
                </table>
                
                <p style=\"margin-top: 20px;\">Your order will be delivered soon. You can track your order status by logging into your account.</p>
                
                <a href='http://localhost/Shopping-cart-application/pages/order-history.php' class='button'>View Order Status</a>
                
                <p style=\"margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666;\">
                    If you have any questions, please contact our support team at support@greencart.local
                </p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2026 GreenCart. All rights reserved.</p>
                <p>This is an automated email. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Send email
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$config['from_name']} <{$config['from_email']}>\r\n";
    $headers .= "Reply-To: support@greencart.local\r\n";
    
    return mail($user_email, $subject, $htmlBody, $headers);
}

/**
 * Send order status update email
 */
function sendOrderStatusUpdateEmail($user_email, $user_name, $order_id, $status) {
    $config = include(__DIR__ . '/email.php');
    
    $statusMessages = [
        'pending' => 'Your order is being prepared',
        'completed' => 'Your order has been completed and is ready for delivery',
        'cancelled' => 'Your order has been cancelled',
    ];
    
    $message = $statusMessages[$status] ?? 'Your order status has been updated';
    
    $subject = "Order #{$order_id} - Status Update";
    
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2d5016; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 20px; }
            .footer { background: #eee; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
            .status-badge { display: inline-block; padding: 8px 16px; background: #4CAF50; color: white; border-radius: 4px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌿 GreenCart</h1>
                <p>Order Status Update</p>
            </div>
            
            <div class='content'>
                <p>Hello <strong>{$user_name}</strong>,</p>
                
                <p>Your order status has been updated:</p>
                
                <p><strong>Order ID:</strong> #{$order_id}</p>
                <p style=\"margin-top: 20px;\"><span class='status-badge'>" . strtoupper($status) . "</span></p>
                
                <p style=\"margin-top: 20px;\">{$message}</p>
                
                <p>Thank you for shopping with GreenCart!</p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2026 GreenCart. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$config['from_name']} <{$config['from_email']}>\r\n";
    
    return mail($user_email, $subject, $htmlBody, $headers);
}

/**
 * Send welcome/registration confirmation email
 */
function sendWelcomeEmail($user_email, $user_name) {
    $config = include(__DIR__ . '/email.php');
    
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    $subject = "Welcome to GreenCart! 🌿";
    
    $htmlBody = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #2d5016; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 20px; }
            .footer { background: #eee; padding: 15px; text-align: center; font-size: 12px; color: #666; border-radius: 0 0 8px 8px; }
            .button { display: inline-block; padding: 12px 24px; background: #8b7c5e; color: white; text-decoration: none; border-radius: 4px; margin-top: 15px; margin-right: 10px; }
            .features { margin: 20px 0; }
            .feature-item { background: white; padding: 15px; margin: 10px 0; border-left: 4px solid #8b7c5e; }
            .feature-icon { font-size: 24px; margin-right: 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🌿 GreenCart</h1>
                <p>Welcome Aboard!</p>
            </div>
            
            <div class='content'>
                <p>Hello <strong>{$user_name}</strong>,</p>
                
                <p>Thank you for joining <strong>GreenCart</strong>! We're excited to have you as part of our community of fresh produce lovers.</p>
                
                <h3>What's Next?</h3>
                <p>You can now:</p>
                <div class='features'>
                    <div class='feature-item'>
                        <span class='feature-icon'>🛍️</span> Browse our collection of fresh vegetables, fruits, and artisan products
                    </div>
                    <div class='feature-item'>
                        <span class='feature-icon'>🛒</span> Add items to your cart and checkout with ease
                    </div>
                    <div class='feature-item'>
                        <span class='feature-icon'>📦</span> Track your orders in real-time from your profile
                    </div>
                    <div class='feature-item'>
                        <span class='feature-icon'>💚</span> Enjoy our loyalty program and exclusive deals
                    </div>
                </div>
                
                <h3>Start Shopping Now</h3>
                <p>Head over to our store and discover fresh, organic products delivered right to your door.</p>
                
                <a href='http://localhost/Shopping-cart-application/pages/products.php' class='button'>Shop Now →</a>
                <a href='http://localhost/Shopping-cart-application/pages/profile.php' class='button'>View Profile</a>
                
                <h3>Questions?</h3>
                <p>If you need any help, feel free to reach out to our support team at <strong>support@greencart.local</strong></p>
                
                <p style=\"margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666;\">
                    Happy shopping with GreenCart!<br>
                    <strong>GreenCart Team</strong>
                </p>
            </div>
            
            <div class='footer'>
                <p>&copy; 2026 GreenCart. All rights reserved.</p>
                <p>This is an automated email. Please do not reply to this email.</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Send email
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$config['from_name']} <{$config['from_email']}>\r\n";
    $headers .= "Reply-To: support@greencart.local\r\n";
    
    return mail($user_email, $subject, $htmlBody, $headers);
}

?>
