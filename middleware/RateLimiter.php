<?php
/**
 * Smart Rate Limiter
 * Auto-detect Redis dan fallback ke file storage jika tidak tersedia
 * 
 * Usage:
 *   RateLimiter::login();      // 5 attempts per 15 minutes
 *   RateLimiter::register();   // 10 attempts per 5 minutes
 *   RateLimiter::api();        // 1000 attempts per 1 minute
 *   RateLimiter::general();    // 60 attempts per 1 minute
 *   RateLimiter::limit(100, 1); // Custom: 100 attempts per 1 minute
 */

class RateLimiter {
    private $storage;
    private $maxAttempts;
    private $decayMinutes;
    
    /**
     * Constructor
     */
    public function __construct($maxAttempts = 60, $decayMinutes = 1) {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
        $this->storage = $this->initStorage();
    }
    
    /**
     * Initialize storage (Redis atau File)
     */
    private function initStorage() {
        // Coba gunakan Redis jika extension tersedia dan class Redis ada
        if (extension_loaded('redis') && class_exists('Redis')) {
            try {
                return new RedisStorage();
            } catch (Exception $e) {
                error_log('[RateLimiter] Redis not available, falling back to file storage: ' . $e->getMessage());
            }
        }
        
        // Fallback ke file storage
        return new FileStorage();
    }
    
    /**
     * Get client IP address
     * Gunakan REMOTE_ADDR secara default untuk mencegah IP spoofing.
     * Header X-Forwarded-For HANYA dipercaya jika server ada di belakang trusted proxy.
     */
    private function getClientIP() {
        $trustedProxy = getenv('TRUSTED_PROXY') ?: '';
        $remoteAddr   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Hanya percaya X-Forwarded-For jika request datang dari trusted proxy IP
        if (!empty($trustedProxy) && $remoteAddr === $trustedProxy) {
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                // Ambil IP pertama (client asli), bukan yang terakhir (proxy)
                $ips = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
                $clientIp = filter_var($ips[0], FILTER_VALIDATE_IP);
                if ($clientIp) {
                    return $clientIp;
                }
            }
        }

        return $remoteAddr;
    }
    
    /**
     * Get rate limit key
     */
    private function getKey($identifier = null) {
        if ($identifier === null) {
            $identifier = $this->getClientIP();
        }
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        return 'rate_limit:' . md5($identifier . '|' . $uri);
    }
    
    /**
     * Check if request is allowed
     */
    public function attempt($identifier = null) {
        $key = $this->getKey($identifier);
        $ttl = $this->decayMinutes * 60;
        
        $attempts = $this->storage->get($key);
        
        if ($attempts === null) {
            $this->storage->set($key, 1, $ttl);
            return true;
        }
        
        if ($attempts >= $this->maxAttempts) {
            return false;
        }
        
        $this->storage->increment($key);
        return true;
    }
    
    /**
     * Get remaining attempts
     */
    public function remaining($identifier = null) {
        $key = $this->getKey($identifier);
        $attempts = (int)$this->storage->get($key);
        return max(0, $this->maxAttempts - $attempts);
    }
    
    /**
     * Get seconds until reset
     */
    public function availableIn($identifier = null) {
        $key = $this->getKey($identifier);
        return $this->storage->ttl($key);
    }
    
    /**
     * Clear rate limit
     */
    public function clear($identifier = null) {
        $key = $this->getKey($identifier);
        $this->storage->delete($key);
    }
    
    /**
     * Handle rate limit exceeded
     */
    public function handleExceeded($message = null) {
        if ($message === null) {
            $message = 'Too many requests. Please try again later.';
        }
        
        $retryAfter = $this->availableIn();
        
        http_response_code(429);
        header('Retry-After: ' . $retryAfter);
        header('X-RateLimit-Limit: ' . $this->maxAttempts);
        header('X-RateLimit-Remaining: 0');
        header('X-RateLimit-Reset: ' . (time() + $retryAfter));
        
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode([
                'error' => $message,
                'retry_after' => $retryAfter,
                'retry_after_human' => $this->formatSeconds($retryAfter)
            ]);
        } else {
            echo $message . ' Retry after ' . $this->formatSeconds($retryAfter) . '.';
        }
        
        exit;
    }
    
    /**
     * Format seconds to human readable
     */
    private function formatSeconds($seconds) {
        if ($seconds < 60) return $seconds . ' seconds';
        if ($seconds < 3600) return ceil($seconds / 60) . ' minutes';
        return ceil($seconds / 3600) . ' hours';
    }
    
    /**
     * Get storage info
     */
    public function getInfo() {
        return [
            'storage_type' => get_class($this->storage),
            'max_attempts' => $this->maxAttempts,
            'decay_minutes' => $this->decayMinutes,
        ];
    }
    
    // ========================================
    // Static Helper Methods
    // ========================================
    
    /**
     * Generic rate limit middleware
     */
    public static function limit($maxAttempts = 60, $decayMinutes = 1, $identifier = null) {
        $limiter = new self($maxAttempts, $decayMinutes);
        
        if (!$limiter->attempt($identifier)) {
            $limiter->handleExceeded();
        }
        
        header('X-RateLimit-Limit: ' . $maxAttempts);
        header('X-RateLimit-Remaining: ' . $limiter->remaining($identifier));
        header('X-RateLimit-Reset: ' . (time() + $limiter->availableIn($identifier)));
    }
    
    /**
     * Login rate limit (5 attempts per 15 minutes)
     */
    public static function login($identifier = null) {
        $max = (int)(getenv('RATE_LIMIT_LOGIN_MAX') ?: 5);
        $decay = (int)(getenv('RATE_LIMIT_LOGIN_DECAY') ?: 15);
        self::limit($max, $decay, $identifier);
    }
    
    /**
     * Register rate limit (10 attempts per 5 minutes)
     */
    public static function register($identifier = null) {
        $max = (int)(getenv('RATE_LIMIT_REGISTER_MAX') ?: 10);
        $decay = (int)(getenv('RATE_LIMIT_REGISTER_DECAY') ?: 5);
        self::limit($max, $decay, $identifier);
    }
    
    /**
     * Forgot password rate limit (3 attempts per 30 minutes)
     */
    public static function forgotPassword($identifier = null) {
        $max = (int)(getenv('RATE_LIMIT_FORGOT_PASSWORD_MAX') ?: 3);
        $decay = (int)(getenv('RATE_LIMIT_FORGOT_PASSWORD_DECAY') ?: 30);
        self::limit($max, $decay, $identifier);
    }
    
    /**
     * Contact form rate limit (5 attempts per 10 minutes)
     */
    public static function contact($identifier = null) {
        $max = (int)(getenv('RATE_LIMIT_CONTACT_MAX') ?: 5);
        $decay = (int)(getenv('RATE_LIMIT_CONTACT_DECAY') ?: 10);
        self::limit($max, $decay, $identifier);
    }
    
    /**
     * API rate limit (1000 attempts per 1 minute)
     */
    public static function api($identifier = null) {
        $max = (int)(getenv('RATE_LIMIT_API_MAX') ?: 1000);
        $decay = (int)(getenv('RATE_LIMIT_API_DECAY') ?: 1);
        self::limit($max, $decay, $identifier);
    }
    
    /**
     * General rate limit (60 attempts per 1 minute)
     */
    public static function general($identifier = null) {
        $max = (int)(getenv('RATE_LIMIT_GENERAL_MAX') ?: 60);
        $decay = (int)(getenv('RATE_LIMIT_GENERAL_DECAY') ?: 1);
        self::limit($max, $decay, $identifier);
    }
}

// ========================================
// Storage Interfaces
// ========================================

/**
 * Redis Storage (High Performance)
 */
class RedisStorage {
    private $redis;

    private function env($keys, $default = null) {
        foreach ((array) $keys as $key) {
            $value = getenv($key);
            if ($value !== false && $value !== '') {
                return $value;
            }

            if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
                return $_ENV[$key];
            }

            if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
                return $_SERVER[$key];
            }
        }

        return $default;
    }

    private function redisUrlConfig($url) {
        if (empty($url)) {
            return [];
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return [];
        }

        $database = 0;
        if (!empty($parts['path'])) {
            $path = ltrim($parts['path'], '/');
            $database = ctype_digit($path) ? (int) $path : 0;
        }

        return [
            'host' => $parts['host'] ?? null,
            'port' => $parts['port'] ?? null,
            'username' => isset($parts['user']) ? rawurldecode($parts['user']) : null,
            'password' => isset($parts['pass']) ? rawurldecode($parts['pass']) : null,
            'database' => $database,
        ];
    }
    
    public function __construct() {
        $this->redis = new Redis();

        $urlConfig = $this->redisUrlConfig($this->env('REDIS_URL', ''));
        $host = $this->env(['REDIS_HOST', 'REDISHOST'], $urlConfig['host'] ?? 'localhost');
        $port = (int) $this->env(['REDIS_PORT', 'REDISPORT'], $urlConfig['port'] ?? 6379);
        $username = $this->env(['REDIS_USER', 'REDISUSER'], $urlConfig['username'] ?? null);
        $password = $this->env(['REDIS_PASSWORD', 'REDISPASSWORD'], $urlConfig['password'] ?? null);
        $database = (int) $this->env(['REDIS_DATABASE', 'REDISDATABASE'], $urlConfig['database'] ?? 0);
        
        if (!$this->redis->connect($host, $port, 2.5)) {
            throw new Exception("Cannot connect to Redis");
        }
        
        if ($password) {
            $username ? $this->redis->auth([$username, $password]) : $this->redis->auth($password);
        }
        
        if ($database > 0) {
            $this->redis->select($database);
        }
    }
    
    public function get($key) {
        $value = $this->redis->get($key);
        return $value === false ? null : (int)$value;
    }
    
    public function set($key, $value, $ttl) {
        $this->redis->setex($key, $ttl, $value);
    }
    
    public function increment($key) {
        return $this->redis->incr($key);
    }
    
    public function delete($key) {
        $this->redis->del($key);
    }
    
    public function ttl($key) {
        $ttl = $this->redis->ttl($key);
        return max(0, $ttl);
    }
}

/**
 * File Storage (Fallback)
 */
class FileStorage {
    private $storageFile;
    
    public function __construct() {
        $this->storageFile = __DIR__ . '/../storage/rate_limit.json';
        
        $storageDir = dirname($this->storageFile);
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }
    }
    
    private function loadData() {
        if (!file_exists($this->storageFile)) {
            return [];
        }
        $data = file_get_contents($this->storageFile);
        return json_decode($data, true) ?: [];
    }
    
    private function saveData($data) {
        $fp = fopen($this->storageFile, 'c');
        if (!$fp) return;
        // Exclusive lock untuk mencegah race condition
        if (flock($fp, LOCK_EX)) {
            ftruncate($fp, 0);
            fwrite($fp, json_encode($data));
            fflush($fp);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
    
    private function cleanExpired($data) {
        $now = time();
        foreach ($data as $key => $entry) {
            if ($now > $entry['expires_at']) {
                unset($data[$key]);
            }
        }
        return $data;
    }
    
    public function get($key) {
        $data = $this->loadData();
        $data = $this->cleanExpired($data);
        
        if (!isset($data[$key])) {
            return null;
        }
        
        return (int)$data[$key]['value'];
    }
    
    public function set($key, $value, $ttl) {
        $data = $this->loadData();
        $data = $this->cleanExpired($data);
        
        $data[$key] = [
            'value' => $value,
            'expires_at' => time() + $ttl
        ];
        
        $this->saveData($data);
    }
    
    public function increment($key) {
        $data = $this->loadData();
        $data = $this->cleanExpired($data);
        
        if (isset($data[$key])) {
            $data[$key]['value']++;
            $this->saveData($data);
            return $data[$key]['value'];
        }
        
        return 1;
    }
    
    public function delete($key) {
        $data = $this->loadData();
        if (isset($data[$key])) {
            unset($data[$key]);
            $this->saveData($data);
        }
    }
    
    public function ttl($key) {
        $data = $this->loadData();
        
        if (!isset($data[$key])) {
            return 0;
        }
        
        $remaining = $data[$key]['expires_at'] - time();
        return max(0, $remaining);
    }
}
