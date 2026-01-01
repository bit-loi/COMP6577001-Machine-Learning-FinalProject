<?php
/**
 * Contoh Penggunaan Rate Limiter
 * Sekarang lebih simple - semua dalam 1 file!
 */

require_once __DIR__ . '/../middleware/RateLimiter.php';

echo "🛡️ Rate Limiter Examples\n";
echo str_repeat("=", 60) . "\n\n";

// ============================================
// CARA 1: Static Methods (PALING MUDAH!)
// ============================================

echo "1️⃣ Static Methods (Recommended)\n";
echo str_repeat("-", 60) . "\n";

// Login endpoint
// RateLimiter::login();

// Register endpoint
// RateLimiter::register();

// Forgot password
// RateLimiter::forgotPassword();

// Contact form
// RateLimiter::contact();

// API endpoint
// RateLimiter::api();

// General/default
// RateLimiter::general();

echo "✅ Usage: RateLimiter::login();\n";
echo "✅ Usage: RateLimiter::register();\n";
echo "✅ Usage: RateLimiter::api();\n\n";

// ============================================
// CARA 2: Custom Limit
// ============================================

echo "2️⃣ Custom Limit\n";
echo str_repeat("-", 60) . "\n";

// Custom: 100 attempts per 5 minutes
// RateLimiter::limit(100, 5);

echo "✅ Usage: RateLimiter::limit(100, 5);\n";
echo "   → 100 attempts per 5 minutes\n\n";

// ============================================
// CARA 3: Manual Control (Advanced)
// ============================================

echo "3️⃣ Manual Control\n";
echo str_repeat("-", 60) . "\n";

$limiter = new RateLimiter(10, 1); // 10 attempts per 1 minute

echo "Testing rate limiter...\n";

for ($i = 1; $i <= 12; $i++) {
    if ($limiter->attempt()) {
        $remaining = $limiter->remaining();
        echo "  Request #$i: ✅ Allowed (Remaining: $remaining)\n";
    } else {
        $retryAfter = $limiter->availableIn();
        echo "  Request #$i: ❌ Rate Limited (Retry after: $retryAfter seconds)\n";
    }
}

// Clear for next test
$limiter->clear();

echo "\n";

// ============================================
// CARA 4: Check Storage Type
// ============================================

echo "4️⃣ Storage Information\n";
echo str_repeat("-", 60) . "\n";

$limiter = new RateLimiter();
$info = $limiter->getInfo();

echo "Storage Type: " . $info['storage_type'] . "\n";
echo "Max Attempts: " . $info['max_attempts'] . "\n";
echo "Decay Minutes: " . $info['decay_minutes'] . "\n\n";

if ($info['storage_type'] === 'RedisStorage') {
    echo "🚀 Using Redis - High Performance!\n";
} else {
    echo "📁 Using File Storage - Fallback mode\n";
    echo "💡 Install Redis extension for better performance\n";
}

echo "\n";

// ============================================
// CARA 5: Per-User Rate Limiting
// ============================================

echo "5️⃣ Per-User Rate Limiting\n";
echo str_repeat("-", 60) . "\n";

// Contoh: limit per user ID, bukan per IP
$userId = 123;
// RateLimiter::limit(50, 1, 'user_' . $userId);

echo "✅ Usage: RateLimiter::limit(50, 1, 'user_' . \$userId);\n";
echo "   → Limit per user, bukan per IP\n\n";

// ============================================
// Summary
// ============================================

echo str_repeat("=", 60) . "\n";
echo "📚 Quick Reference:\n";
echo str_repeat("=", 60) . "\n";
echo "RateLimiter::login()           → 5 attempts / 15 min\n";
echo "RateLimiter::register()        → 10 attempts / 5 min\n";
echo "RateLimiter::forgotPassword()  → 3 attempts / 30 min\n";
echo "RateLimiter::contact()         → 5 attempts / 10 min\n";
echo "RateLimiter::api()             → 1000 attempts / 1 min\n";
echo "RateLimiter::general()         → 60 attempts / 1 min\n";
echo "RateLimiter::limit(\$max, \$min) → Custom limit\n";
echo str_repeat("=", 60) . "\n";

?>
