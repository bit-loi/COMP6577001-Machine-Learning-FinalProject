<?php
/**
 * Contoh Penggunaan Rate Limiter dengan Helper Functions
 * Lebih mudah dan konfigurasi dari .env
 */

require_once __DIR__ . '/../middleware/rate_limit_helpers.php';

// ============================================
// CARA TERMUDAH: Gunakan Helper Functions
// ============================================

// 1. Login Page
// rateLimitLogin(); // 5 attempts per 15 menit (dari .env)

// 2. Register Page
// rateLimitRegister(); // 10 attempts per 5 menit (dari .env)

// 3. Forgot Password
// rateLimitForgotPassword(); // 3 attempts per 30 menit (dari .env)

// 4. Contact Form
// rateLimitContact(); // 5 attempts per 10 menit (dari .env)

// 5. API Endpoint
// rateLimitAPI(); // 1000 attempts per 1 menit (dari .env)

// 6. General/Default
// rateLimitGeneral(); // 60 attempts per 1 menit (dari .env)

// ============================================
// CONTOH IMPLEMENTASI LENGKAP
// ============================================

// Detect endpoint dan apply rate limit yang sesuai
$currentPage = basename($_SERVER['PHP_SELF']);

switch ($currentPage) {
    case 'login.php':
        rateLimitLogin();
        echo "Login page - Rate limit: 5 attempts per 15 minutes\n";
        break;
        
    case 'register.php':
        rateLimitRegister();
        echo "Register page - Rate limit: 10 attempts per 5 minutes\n";
        break;
        
    case 'forgot-password.php':
        rateLimitForgotPassword();
        echo "Forgot password - Rate limit: 3 attempts per 30 minutes\n";
        break;
        
    case 'contact.php':
        rateLimitContact();
        echo "Contact form - Rate limit: 5 attempts per 10 minutes\n";
        break;
        
    default:
        rateLimitGeneral();
        echo "General page - Rate limit: 60 attempts per 1 minute\n";
        break;
}

// ============================================
// CUSTOM RATE LIMIT
// ============================================

// Jika Anda ingin membuat rate limit custom
// Tambahkan di .env:
// RATE_LIMIT_CHECKOUT_MAX=20
// RATE_LIMIT_CHECKOUT_DECAY=5

// Lalu gunakan:
// rateLimit('CHECKOUT', 20, 5);

// ============================================
// CONTOH REAL WORLD
// ============================================

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Limiter Demo</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .endpoint {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }
        .endpoint h3 {
            color: #667eea;
            margin-bottom: 10px;
        }
        .endpoint code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .limit-info {
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛡️ Rate Limiter Demo</h1>
        
        <div class="endpoint">
            <h3>1. Login Endpoint</h3>
            <code>rateLimitLogin();</code>
            <div class="limit-info">
                ⏱️ Limit: 5 attempts per 15 minutes<br>
                🎯 Use case: Mencegah brute force attack
            </div>
        </div>
        
        <div class="endpoint">
            <h3>2. Register Endpoint</h3>
            <code>rateLimitRegister();</code>
            <div class="limit-info">
                ⏱️ Limit: 10 attempts per 5 minutes<br>
                🎯 Use case: Mencegah spam account creation
            </div>
        </div>
        
        <div class="endpoint">
            <h3>3. Forgot Password</h3>
            <code>rateLimitForgotPassword();</code>
            <div class="limit-info">
                ⏱️ Limit: 3 attempts per 30 minutes<br>
                🎯 Use case: Mencegah abuse forgot password
            </div>
        </div>
        
        <div class="endpoint">
            <h3>4. Contact Form</h3>
            <code>rateLimitContact();</code>
            <div class="limit-info">
                ⏱️ Limit: 5 attempts per 10 minutes<br>
                🎯 Use case: Mencegah spam messages
            </div>
        </div>
        
        <div class="endpoint">
            <h3>5. API Endpoint</h3>
            <code>rateLimitAPI();</code>
            <div class="limit-info">
                ⏱️ Limit: 1000 attempts per 1 minute<br>
                🎯 Use case: Developer-friendly API rate limit
            </div>
        </div>
        
        <div class="endpoint">
            <h3>6. General/Default</h3>
            <code>rateLimitGeneral();</code>
            <div class="limit-info">
                ⏱️ Limit: 60 attempts per 1 minute<br>
                🎯 Use case: Default untuk semua endpoint lainnya
            </div>
        </div>
        
        <div class="success">
            ✅ <strong>Rate limiter aktif!</strong><br>
            Semua endpoint sudah terlindungi dari spam dan abuse.
        </div>
    </div>
</body>
</html>
