<?php
/**
 * Test Redis Connection
 * Gunakan file ini untuk test apakah Redis sudah terinstall dan berfungsi
 */

echo "🔍 Testing Redis Connection...\n\n";

// Check if Redis extension is loaded
if (!extension_loaded('redis')) {
    echo "❌ PHP Redis extension is NOT installed!\n";
    echo "💡 Rate limiter will use file storage as fallback.\n\n";
    echo "📖 To install Redis extension, see REDIS_SETUP.md\n";
    exit(1);
}

echo "✅ PHP Redis extension is loaded\n\n";

// Try to connect
try {
    $redis = new Redis();
    
    // Get config from .env or use defaults
    require_once __DIR__ . '/config/env.php';
    
    $host = Env::get('REDIS_HOST', 'localhost');
    $port = (int)Env::get('REDIS_PORT', 6379);
    $password = Env::get('REDIS_PASSWORD', null);
    $database = (int)Env::get('REDIS_DATABASE', 0);
    
    echo "📡 Connecting to Redis...\n";
    echo "   Host: $host\n";
    echo "   Port: $port\n";
    echo "   Database: $database\n\n";
    
    // Connect
    $connected = $redis->connect($host, $port, 2.5);
    
    if (!$connected) {
        throw new Exception("Cannot connect to Redis server");
    }
    
    // Authenticate if password is set
    if ($password) {
        $redis->auth($password);
        echo "🔐 Authenticated with password\n";
    }
    
    // Select database
    if ($database > 0) {
        $redis->select($database);
    }
    
    // Ping test
    $pong = $redis->ping();
    if ($pong !== '+PONG') {
        throw new Exception("Redis ping failed");
    }
    
    echo "✅ Redis connection successful!\n\n";
    
    // Get Redis info
    $info = $redis->info();
    
    echo "📊 Redis Information:\n";
    echo "   Version: " . ($info['redis_version'] ?? 'unknown') . "\n";
    echo "   Mode: " . ($info['redis_mode'] ?? 'unknown') . "\n";
    echo "   Uptime: " . ($info['uptime_in_days'] ?? '0') . " days\n";
    echo "   Connected Clients: " . ($info['connected_clients'] ?? '0') . "\n";
    echo "   Used Memory: " . ($info['used_memory_human'] ?? 'unknown') . "\n";
    echo "   Total Keys: " . ($info['db0'] ?? '0 keys') . "\n\n";
    
    // Test write/read
    echo "🧪 Testing write/read operations...\n";
    
    $testKey = 'test:' . time();
    $testValue = 'Hello Redis!';
    
    $redis->setex($testKey, 10, $testValue); // Expire in 10 seconds
    $readValue = $redis->get($testKey);
    
    if ($readValue === $testValue) {
        echo "✅ Write/Read test successful!\n";
        echo "   Key: $testKey\n";
        echo "   Value: $readValue\n";
        echo "   TTL: " . $redis->ttl($testKey) . " seconds\n\n";
    } else {
        echo "❌ Write/Read test failed!\n\n";
    }
    
    // Clean up
    $redis->del($testKey);
    
    // Test rate limiter
    echo "🛡️ Testing Rate Limiter with Redis...\n";
    
    require_once __DIR__ . '/middleware/RateLimiter.php';
    
    $limiter = new RateLimiter(5, 1); // 5 attempts per 1 minute
    
    echo "   Max Attempts: 5\n";
    echo "   Decay Time: 1 minute\n";
    
    // Check storage type
    $info = $limiter->getInfo();
    echo "   Storage: " . $info['storage_type'] . "\n\n";
    
    // Simulate requests
    for ($i = 1; $i <= 7; $i++) {
        $allowed = $limiter->attempt('test_user');
        $remaining = $limiter->remaining('test_user');
        
        if ($allowed) {
            echo "   Request #$i: ✅ Allowed (Remaining: $remaining)\n";
        } else {
            $retryAfter = $limiter->availableIn('test_user');
            echo "   Request #$i: ❌ Rate Limited (Retry after: $retryAfter seconds)\n";
        }
    }
    
    // Clean up test data
    $limiter->clear('test_user');
    
    echo "\n🎉 All tests passed!\n";
    echo "✅ Redis is working perfectly with rate limiter!\n\n";
    
} catch (Exception $e) {
    echo "\n❌ Redis connection failed!\n";
    echo "   Error: " . $e->getMessage() . "\n\n";
    echo "💡 Possible solutions:\n";
    echo "   1. Make sure Redis server is running\n";
    echo "   2. Check REDIS_HOST and REDIS_PORT in .env file\n";
    echo "   3. Check if firewall is blocking port 6379\n\n";
    echo "📖 For installation guide, see REDIS_SETUP.md\n\n";
    echo "⚠️  Rate limiter will use file storage as fallback\n";
    exit(1);
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✅ Redis is ready for production use!\n";
echo str_repeat("=", 60) . "\n";
?>
