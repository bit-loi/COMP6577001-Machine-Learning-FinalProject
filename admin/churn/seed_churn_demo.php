<?php
session_start();
require_once '../../config/config.php';

// Simple auth check
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die('Unauthorized. Please login as admin.');
}

// -------------------------------------------------------
// Create tables if they don't exist yet
// -------------------------------------------------------
$conn->exec("
CREATE TABLE IF NOT EXISTS churn_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL,
    snapshot_date DATE NOT NULL,
    country VARCHAR(100),
    orders_last_window INT,
    revenue_last_window DECIMAL(12, 2),
    recency_days INT,
    customer_age_days INT,
    predicted_churn_probability DECIMAL(6, 5),
    predicted_churn TINYINT,
    risk_level VARCHAR(20),
    recommended_action VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_customer_snapshot (customer_id, snapshot_date),
    INDEX idx_customer (customer_id),
    INDEX idx_risk (risk_level),
    INDEX idx_snapshot (snapshot_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$conn->exec("
CREATE TABLE IF NOT EXISTS retention_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL,
    churn_score_id INT NULL,
    action_type VARCHAR(100),
    action_status VARCHAR(50) DEFAULT 'done',
    admin_note TEXT,
    actioned_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_customer (customer_id),
    INDEX idx_churn_score (churn_score_id),
    FOREIGN KEY (churn_score_id) REFERENCES churn_scores(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// -------------------------------------------------------
// Risk level + recommended action logic
// -------------------------------------------------------
function calcRisk(float $prob, float $revenue): array {
    if ($prob >= 0.75 && $revenue >= 100) {
        return ['Critical', 'Send urgent retention offer'];
    } elseif ($prob >= 0.75) {
        return ['Critical', 'Send reactivation email'];
    } elseif ($prob >= 0.45) {
        return ['At Risk', 'Send personalized promo'];
    } else {
        return ['Loyal', 'Maintain normal engagement'];
    }
}

// -------------------------------------------------------
// Sample customer data (mimics UCI Online Retail dataset)
// -------------------------------------------------------
$snapshot = '2011-12-01';
$customers = [
    ['17850', 'United Kingdom', 12, 340.20,  88, 420, 0.91234],
    ['13047', 'United Kingdom',  5, 120.50,  45, 300, 0.62100],
    ['12583', 'France',         20, 800.00,  12, 500, 0.18500],
    ['15100', 'Germany',         3,  55.75, 120, 210, 0.88900],
    ['17511', 'United Kingdom',  8, 210.30,  60, 380, 0.71300],
    ['13748', 'Australia',      15, 650.00,  20, 480, 0.22000],
    ['14680', 'Netherlands',     2,  38.50, 150, 180, 0.93450],
    ['16029', 'United Kingdom',  6, 175.80,  55, 350, 0.59800],
    ['15838', 'Spain',          11, 290.00,  30, 410, 0.31200],
    ['17404', 'Germany',         4,  90.20, 105, 240, 0.80100],
    ['14527', 'France',         18, 720.00,  15, 520, 0.14300],
    ['16684', 'United Kingdom',  7, 195.40,  72, 360, 0.66700],
    ['13085', 'Norway',          1,  22.00, 180, 120, 0.97100],
    ['15658', 'United Kingdom', 25, 980.00,   8, 600, 0.09800],
    ['17392', 'Belgium',         9, 260.00,  40, 430, 0.44500],
    ['14156', 'Portugal',        3,  70.00, 135, 200, 0.85600],
    ['16244', 'United Kingdom', 14, 480.00,  25, 460, 0.27300],
    ['13829', 'Sweden',          2,  45.50, 160, 150, 0.91700],
    ['17760', 'United Kingdom', 10, 320.00,  50, 390, 0.53400],
    ['15499', 'Germany',         6, 140.00,  80, 320, 0.75800],
];

$inserted = 0;
$updated  = 0;
$errors   = [];

$sql = "INSERT INTO churn_scores
            (customer_id, snapshot_date, country, orders_last_window, revenue_last_window,
             recency_days, customer_age_days, predicted_churn_probability, predicted_churn,
             risk_level, recommended_action)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            country = VALUES(country),
            orders_last_window = VALUES(orders_last_window),
            revenue_last_window = VALUES(revenue_last_window),
            recency_days = VALUES(recency_days),
            customer_age_days = VALUES(customer_age_days),
            predicted_churn_probability = VALUES(predicted_churn_probability),
            predicted_churn = VALUES(predicted_churn),
            risk_level = VALUES(risk_level),
            recommended_action = VALUES(recommended_action)";

$stmt = $conn->prepare($sql);

foreach ($customers as $c) {
    [$custId, $country, $orders, $revenue, $recency, $age, $prob] = $c;
    [$risk, $action] = calcRisk($prob, $revenue);
    $churn = $prob >= 0.5 ? 1 : 0;
    try {
        $stmt->execute([$custId, $snapshot, $country, $orders, $revenue, $recency, $age, $prob, $churn, $risk, $action]);
        if ($stmt->rowCount() == 1) $inserted++;
        else $updated++;
    } catch (PDOException $e) {
        $errors[] = "Customer $custId: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Churn Demo Seeder — Shopmart</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .card { background: #fff; border-radius: 20px; border: 1px solid #f1f5f9; padding: 40px 48px; max-width: 560px; width: 100%; box-shadow: 0 8px 32px rgba(0,0,0,0.06); }
        .icon { width: 56px; height: 56px; border-radius: 16px; background: #dcfce7; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
        h1 { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0 0 6px; }
        p  { color: #64748b; font-size: 0.875rem; margin: 0 0 24px; }
        .stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .stat { background: #f8fafc; border-radius: 12px; padding: 16px; text-align: center; }
        .stat-n { font-size: 2rem; font-weight: 800; color: #0f172a; }
        .stat-l { font-size: 0.72rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 2px; }
        .errors { background: #fef2f2; border: 1px solid #fecaca; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.8rem; color: #991b1b; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 10px; font-size: 0.875rem; font-weight: 700; text-decoration: none; transition: all .15s; }
        .btn-primary { background: #EE4D2D; color: #fff; }
        .btn-primary:hover { background: #C53D20; }
        .btn-ghost { background: #f1f5f9; color: #475569; margin-left: 8px; }
        .btn-ghost:hover { background: #e2e8f0; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <h1>Churn Demo Seeder</h1>
        <p>Sample churn scores have been loaded into the database (snapshot: <strong><?php echo $snapshot; ?></strong>).</p>
        <div class="stats">
            <div class="stat"><div class="stat-n" style="color:#16a34a;"><?php echo $inserted; ?></div><div class="stat-l">Inserted</div></div>
            <div class="stat"><div class="stat-n" style="color:#2563eb;"><?php echo $updated; ?></div><div class="stat-l">Updated</div></div>
        </div>
        <?php if ($errors): ?>
        <div class="errors"><strong>Errors:</strong><ul style="margin:8px 0 0 16px;"><?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <a href="<?php echo APPURL; ?>admin/churn/" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
            View Churn Dashboard
        </a>
        <a href="<?php echo APPURL; ?>admin/" class="btn btn-ghost">← Back to Admin</a>
    </div>
</body>
</html>
