<?php
$root = dirname(__DIR__);
$sqlPath = $root . '/database/shopmart_railway_import.sql';

function env_value(array $keys, $default = null) {
    foreach ($keys as $key) {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
    }

    return $default;
}

if (!file_exists($sqlPath)) {
    fwrite(STDERR, "[shopmart] DB bootstrap skipped: SQL file not found at {$sqlPath}\n");
    exit(0);
}

$host = env_value(['DB_HOST', 'MYSQLHOST'], 'localhost');
$port = (int) env_value(['DB_PORT', 'MYSQLPORT'], 3306);
$db = env_value(['DB_NAME', 'MYSQLDATABASE'], 'railway');
$user = env_value(['DB_USERNAME', 'MYSQLUSER'], 'root');
$password = env_value(['DB_PASSWORD', 'MYSQLPASSWORD'], '');
$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

$pdo = null;
$lastError = null;
for ($attempt = 1; $attempt <= 30; $attempt++) {
    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        break;
    } catch (PDOException $e) {
        $lastError = $e->getMessage();
        fwrite(STDERR, "[shopmart] Waiting for MySQL ({$attempt}/30): {$lastError}\n");
        sleep(2);
    }
}

if (!$pdo) {
    fwrite(STDERR, "[shopmart] DB bootstrap failed: cannot connect to MySQL.\n");
    exit(1);
}

$hasProducts = false;
try {
    $table = $pdo->query("SHOW TABLES LIKE 'products'")->fetchColumn();
    if ($table) {
        $count = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
        $hasProducts = $count > 0;
    }
} catch (PDOException $e) {
    $hasProducts = false;
}

if ($hasProducts) {
    fwrite(STDOUT, "[shopmart] DB bootstrap skipped: products table already has data.\n");
    exit(0);
}

$sql = file_get_contents($sqlPath);
$statements = array_filter(array_map('trim', explode(';', $sql)));

fwrite(STDOUT, "[shopmart] DB bootstrap importing {$sqlPath} into database {$db}.\n");

try {
    foreach ($statements as $statement) {
        if ($statement === '' || strpos($statement, '--') === 0 && substr_count($statement, "\n") === 0) {
            continue;
        }
        $pdo->exec($statement);
    }

    fwrite(STDOUT, "[shopmart] DB bootstrap completed.\n");
} catch (PDOException $e) {
    fwrite(STDERR, "[shopmart] DB bootstrap failed: " . $e->getMessage() . "\n");
    exit(1);
}
