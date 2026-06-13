<?php
// Load .env from root directory safely
$envPath = dirname(__DIR__) . '/.env';
$env = [];

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments
        if (strpos($line, '#') === 0 || strpos($line, ';') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, '"\'  ');
            $env[$key] = $value;
            if (getenv($key) === false && !isset($_ENV[$key]) && !isset($_SERVER[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

if (!function_exists('shopmart_env')) {
function shopmart_env($keys, $default = null) {
    global $env;

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

        if (isset($env[$key]) && $env[$key] !== '') {
            return $env[$key];
        }
    }

    return $default;
}
}

if (!function_exists('shopmart_mysql_url_config')) {
function shopmart_mysql_url_config($url): array {
    if (empty($url)) {
        return [];
    }

    $parts = parse_url($url);
    if ($parts === false) {
        return [];
    }

    return [
        'host' => $parts['host'] ?? null,
        'port' => $parts['port'] ?? null,
        'dbname' => isset($parts['path']) ? ltrim($parts['path'], '/') : null,
        'username' => isset($parts['user']) ? rawurldecode($parts['user']) : null,
        'password' => isset($parts['pass']) ? rawurldecode($parts['pass']) : null,
    ];
}
}

$mysqlUrlConfig = shopmart_mysql_url_config(shopmart_env('MYSQL_URL', ''));

$appUrl = shopmart_env('APP_URL', '');
$railwayDomain = shopmart_env(['RAILWAY_PUBLIC_DOMAIN', 'RAILWAY_STATIC_URL'], '');
if ($appUrl === '' && $railwayDomain !== '') {
    $appUrl = preg_match('#^https?://#i', $railwayDomain) ? $railwayDomain : 'https://' . $railwayDomain;
}

// Define APPURL if not already defined
if (!defined('APPURL')) {
    define('APPURL', rtrim($appUrl !== '' ? $appUrl : 'http://localhost/shopmart', '/') . '/');
}

if (!function_exists('shopmart_error_redirect')) {
function shopmart_error_redirect(string $type): void {
    $url = APPURL . 'error.php?type=' . rawurlencode($type);
    if (!headers_sent()) {
        header('Location: ' . $url);
    } else {
        echo '<script>window.location.replace(' . json_encode($url) . ');</script>';
    }
    exit;
}
}

// If critical configs (like DB_HOST or Railway MYSQLHOST) are completely missing, assume failure.
if (empty(shopmart_env(['DB_HOST', 'MYSQLHOST'], $mysqlUrlConfig['host'] ?? ''))) {
    error_log('[SHOPMART] CRITICAL: .env file invalid or missing required keys at: ' . $envPath);
    shopmart_error_redirect('500');
}

$host     = shopmart_env(['DB_HOST', 'MYSQLHOST'], $mysqlUrlConfig['host'] ?? 'localhost');
$port     = (int) shopmart_env(['DB_PORT', 'MYSQLPORT'], $mysqlUrlConfig['port'] ?? 3306);
$dbname   = shopmart_env(['DB_NAME', 'MYSQLDATABASE'], $mysqlUrlConfig['dbname'] ?? 'shopmart');
$username = shopmart_env(['DB_USERNAME', 'MYSQLUSER'], $mysqlUrlConfig['username'] ?? 'root');
$password = shopmart_env(['DB_PASSWORD', 'MYSQLPASSWORD'], $mysqlUrlConfig['password'] ?? '');
$appEnv   = shopmart_env('APP_ENV', 'production');
$appDebug = filter_var(shopmart_env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);

// Session security hardening
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', 1);
    if ($appEnv === 'production') {
        ini_set('session.cookie_secure', 1);
    }
}

if (!function_exists('csrf_token')) {
function csrf_token(): string {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}
}

if (!function_exists('csrf_field')) {
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . esc_attr(csrf_token()) . '">';
}
}

if (!function_exists('verify_csrf_token')) {
function verify_csrf_token(): bool {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return !empty($_POST['csrf_token'])
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}
}

if (!function_exists('require_valid_csrf_token')) {
function require_valid_csrf_token(): void {
    if (!verify_csrf_token()) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}
}

if (!function_exists('esc_html')) {
function esc_html($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
}

if (!function_exists('esc_attr')) {
function esc_attr($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
}

if (!function_exists('esc_url')) {
function esc_url($value): string {
    $url = filter_var((string) $value, FILTER_SANITIZE_URL);
    if ($url === '') {
        return '';
    }

    if (preg_match('/^\s*javascript:/i', $url)) {
        return '';
    }

    return esc_attr($url);
}
}

if (!function_exists('safe_request_method')) {
function safe_request_method(): string {
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
}
}

if (!function_exists('safe_int')) {
function safe_int($value, int $default = 0): int {
    return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int) $value : $default;
}
}

if (!function_exists('safe_text')) {
function safe_text($value, int $maxLength = 255): string {
    $text = trim(strip_tags((string) $value));
    if (function_exists('mb_substr')) {
        return mb_substr($text, 0, $maxLength);
    }

    return substr($text, 0, $maxLength);
}
}

if (!function_exists('safe_email')) {
function safe_email($value): string {
    $email = filter_var((string) $value, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}
}

// Database connection
try {
    $conn = new PDO(
        "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    error_log('[SHOPMART] DB Connection failed: ' . $e->getMessage());
    shopmart_error_redirect('503');
}
?>
