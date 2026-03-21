<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/config.php';

// Fetch statistics
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
    $stmt->execute();
    $totalProducts = $stmt->fetch(PDO::FETCH_OBJ)->total;

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE is_admin = 0");
    $stmt->execute();
    $totalUsers = $stmt->fetch(PDO::FETCH_OBJ)->total;

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
    $stmt->execute();
    $totalOrders = $stmt->fetch(PDO::FETCH_OBJ)->total;

    $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as revenue FROM orders WHERE status = 'completed'");
    $stmt->execute();
    $totalRevenue = $stmt->fetch(PDO::FETCH_OBJ)->revenue;

    $stmt = $conn->prepare("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8");
    $stmt->execute();
    $recentOrders = $stmt->fetchAll(PDO::FETCH_OBJ);

    // Anomaly detection: orders with unusually high amounts (> 3x average)
    $stmt = $conn->prepare("SELECT AVG(total_amount) as avg_amount FROM orders");
    $stmt->execute();
    $avgAmount = $stmt->fetch(PDO::FETCH_OBJ)->avg_amount ?? 0;
    $anomalyThreshold = $avgAmount * 3;

    $stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.total_amount > :threshold ORDER BY o.total_amount DESC LIMIT 10");
    $stmt->execute([':threshold' => max($anomalyThreshold, 100)]);
    $anomalyOrders = $stmt->fetchAll(PDO::FETCH_OBJ);

    // Pending orders count
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
    $stmt->execute();
    $pendingOrders = $stmt->fetch(PDO::FETCH_OBJ)->total;

} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $totalProducts = $totalUsers = $totalOrders = $totalRevenue = $pendingOrders = 0;
    $recentOrders = $anomalyOrders = [];
    $avgAmount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Premeditatio Malorum</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #050505; color: white; margin: 0; display: flex; min-height: 100vh; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .serif { font-family: 'Playfair Display', serif; }

        /* Sidebar */
        .sidebar { width: 240px; min-height: 100vh; background: #0a0a0a; border-right: 1px solid rgba(255,255,255,0.06); display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 50; }
        .sidebar-logo { padding: 28px 24px; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; color: rgba(255,255,255,0.4); font-size: 0.8rem; font-weight: 500; text-decoration: none; transition: all 0.15s; margin-bottom: 2px; cursor: pointer; }
        .nav-item:hover { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.8); }
        .nav-item.active { background: rgba(255,255,255,0.08); color: white; }
        .nav-item svg { width: 16px; height: 16px; flex-shrink: 0; }
        .nav-section { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(255,255,255,0.2); padding: 16px 12px 8px; }

        /* Main content */
        .main { margin-left: 240px; flex: 1; min-height: 100vh; }
        .topbar { height: 64px; border-bottom: 1px solid rgba(255,255,255,0.06); display: flex; align-items: center; justify-content: space-between; padding: 0 32px; background: rgba(5,5,5,0.8); backdrop-filter: blur(12px); position: sticky; top: 0; z-index: 40; }
        .content { padding: 32px; }

        /* Cards */
        .stat-card { background: #0a0a0a; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 24px; transition: border-color 0.2s; }
        .stat-card:hover { border-color: rgba(255,255,255,0.12); }
        .stat-number { font-size: 2rem; font-weight: 700; color: white; line-height: 1; margin: 8px 0 4px; }
        .stat-label { font-size: 0.75rem; color: rgba(255,255,255,0.35); text-transform: uppercase; letter-spacing: 0.1em; }
        .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }

        /* Table */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.3); padding: 12px 16px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .data-table td { padding: 14px 16px; font-size: 0.8rem; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.04); }
        .data-table tr:hover td { background: rgba(255,255,255,0.02); }
        .data-table tr:last-child td { border-bottom: none; }

        /* Badge */
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.05em; }
        .badge-pending { background: rgba(251,191,36,0.1); color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
        .badge-completed { background: rgba(74,222,128,0.1); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
        .badge-processing { background: rgba(96,165,250,0.1); color: #60a5fa; border: 1px solid rgba(96,165,250,0.2); }
        .badge-cancelled { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
        .badge-anomaly { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }

        /* Chart card */
        .chart-card { background: #0a0a0a; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 24px; }
        .card-title { font-size: 0.8rem; font-weight: 600; letter-spacing: 0.05em; color: rgba(255,255,255,0.6); text-transform: uppercase; margin-bottom: 20px; }

        /* Anomaly alert */
        .anomaly-alert { background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.2); border-radius: 12px; padding: 24px; }
        .pulse-dot { width: 8px; height: 8px; background: #ef4444; border-radius: 50%; animation: pulse 2s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.5; transform: scale(1.2); } }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 2px; }
    </style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            <span style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 0.85rem; font-weight: 700; color: white;">Premeditatio Malorum</span>
        </div>
        <div style="font-size: 0.65rem; color: rgba(255,255,255,0.2); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 4px;">Admin Console</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Overview</div>
        <a href="<?php echo APPURL; ?>admin/" class="nav-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="<?php echo APPURL; ?>admin/orders/" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Orders
            <?php if ($pendingOrders > 0): ?>
            <span style="margin-left: auto; background: rgba(251,191,36,0.15); color: #fbbf24; font-size: 0.65rem; font-weight: 700; padding: 2px 7px; border-radius: 10px;"><?php echo $pendingOrders; ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section">Catalogue</div>
        <a href="<?php echo APPURL; ?>admin/products/" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            Products
        </a>
        <a href="<?php echo APPURL; ?>admin/categories/" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            Categories
        </a>

        <div class="nav-section">Users</div>
        <a href="<?php echo APPURL; ?>admin/users/" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Customers
        </a>

        <div class="nav-section">Intelligence</div>
        <a href="<?php echo APPURL; ?>admin/simulation.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
            ML Simulation
        </a>
        <a href="#anomaly-section" class="nav-item" onclick="document.getElementById('anomaly-section').scrollIntoView({behavior:'smooth'}); return false;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Anomaly Detection
            <?php if (count($anomalyOrders) > 0): ?>
            <span style="margin-left: auto; background: rgba(239,68,68,0.15); color: #ef4444; font-size: 0.65rem; font-weight: 700; padding: 2px 7px; border-radius: 10px;"><?php echo count($anomalyOrders); ?></span>
            <?php endif; ?>
        </a>

        <div style="margin-top: auto; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.06); margin-top: 32px;">
            <a href="<?php echo APPURL; ?>" target="_blank" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                View Store
            </a>
            <a href="<?php echo APPURL; ?>auth/logout.php" class="nav-item" style="color: rgba(248,113,113,0.6);" onmouseover="this.style.color='#f87171'; this.style.background='rgba(239,68,68,0.05)';" onmouseout="this.style.color='rgba(248,113,113,0.6)'; this.style.background='';">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </div>
    </nav>
</aside>

<!-- ===== MAIN CONTENT ===== -->
<div class="main">

    <!-- Topbar -->
    <div class="topbar">
        <div>
            <div style="font-size: 1rem; font-weight: 600; color: white;">Dashboard</div>
            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.3); margin-top: 1px;"><?php echo date('l, F j, Y'); ?></div>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <?php if (count($anomalyOrders) > 0): ?>
            <div style="display: flex; align-items: center; gap: 8px; padding: 6px 14px; border-radius: 20px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);">
                <div class="pulse-dot"></div>
                <span style="font-size: 0.75rem; color: #ef4444; font-weight: 600;"><?php echo count($anomalyOrders); ?> Anomalies Detected</span>
            </div>
            <?php endif; ?>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: white;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div>
                    <div style="font-size: 0.8rem; font-weight: 600; color: white;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div style="font-size: 0.65rem; color: rgba(255,255,255,0.3);">Administrator</div>
                </div>
            </div>
        </div>
    </div>

    <div class="content">

        <!-- Stats Grid -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="stat-label">Total Books</div>
                        <div class="stat-number"><?php echo number_format($totalProducts); ?></div>
                        <div style="font-size: 0.7rem; color: rgba(255,255,255,0.25); margin-top: 4px;">In catalogue</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(255,255,255,0.05);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="stat-label">Customers</div>
                        <div class="stat-number"><?php echo number_format($totalUsers); ?></div>
                        <div style="font-size: 0.7rem; color: rgba(255,255,255,0.25); margin-top: 4px;">Registered users</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(255,255,255,0.05);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="stat-label">Total Orders</div>
                        <div class="stat-number"><?php echo number_format($totalOrders); ?></div>
                        <div style="font-size: 0.7rem; color: rgba(251,191,36,0.6); margin-top: 4px;"><?php echo $pendingOrders; ?> pending</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(255,255,255,0.05);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="stat-label">Revenue</div>
                        <div class="stat-number">$<?php echo number_format($totalRevenue, 0); ?></div>
                        <div style="font-size: 0.7rem; color: rgba(74,222,128,0.6); margin-top: 4px;">Completed orders</div>
                    </div>
                    <div class="stat-icon" style="background: rgba(255,255,255,0.05);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div class="chart-card">
                <div class="card-title">Sales Overview — <?php echo date('Y'); ?></div>
                <canvas id="salesChart" height="100"></canvas>
            </div>
            <div class="chart-card">
                <div class="card-title">Order Status</div>
                <canvas id="statusChart" height="180"></canvas>
            </div>
        </div>

        <!-- Anomaly Detection Section -->
        <div id="anomaly-section" style="margin-bottom: 24px;">
            <div class="anomaly-alert">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                    <div class="pulse-dot"></div>
                    <div>
                        <div style="font-size: 0.85rem; font-weight: 700; color: #ef4444;">Anomaly Detection</div>
                        <div style="font-size: 0.75rem; color: rgba(239,68,68,0.6); margin-top: 2px;">
                            <?php if (count($anomalyOrders) > 0): ?>
                                <?php echo count($anomalyOrders); ?> suspicious transaction(s) detected — avg order: $<?php echo number_format($avgAmount, 2); ?>, threshold: $<?php echo number_format(max($anomalyThreshold, 100), 2); ?>
                            <?php else: ?>
                                No anomalies detected. All transactions appear normal.
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="margin-left: auto; font-family: 'JetBrains Mono', monospace; font-size: 0.65rem; color: rgba(239,68,68,0.4);">ML_SERVICE · ACTIVE</div>
                </div>

                <?php if (count($anomalyOrders) > 0): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Amount</th>
                            <th>vs. Average</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($anomalyOrders as $order): ?>
                        <tr>
                            <td><span class="mono" style="color: #ef4444;">#<?php echo str_pad($order->id, 5, '0', STR_PAD_LEFT); ?></span></td>
                            <td style="color: white; font-weight: 500;"><?php echo htmlspecialchars($order->username ?? 'Guest'); ?></td>
                            <td style="color: rgba(255,255,255,0.4); font-size: 0.75rem;"><?php echo htmlspecialchars($order->email ?? '—'); ?></td>
                            <td><span style="color: #ef4444; font-weight: 700; font-family: 'JetBrains Mono', monospace;">$<?php echo number_format($order->total_amount, 2); ?></span></td>
                            <td>
                                <?php $ratio = $avgAmount > 0 ? round($order->total_amount / $avgAmount, 1) : 0; ?>
                                <span class="badge badge-anomaly"><?php echo $ratio; ?>× avg</span>
                            </td>
                            <td><span class="badge badge-<?php echo $order->status; ?>"><?php echo ucfirst($order->status); ?></span></td>
                            <td style="color: rgba(255,255,255,0.4); font-size: 0.75rem;"><?php echo date('M d, Y', strtotime($order->created_at)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 32px; color: rgba(74,222,128,0.5); font-size: 0.8rem;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 12px; display: block;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    All transactions are within normal parameters
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="chart-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                <div class="card-title" style="margin-bottom: 0;">Recent Orders</div>
                <a href="<?php echo APPURL; ?>admin/orders/" style="font-size: 0.75rem; color: rgba(255,255,255,0.4); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">View all →</a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentOrders): ?>
                        <?php foreach ($recentOrders as $order): ?>
                        <tr>
                            <td><span class="mono" style="color: rgba(255,255,255,0.5);">#<?php echo str_pad($order->id, 5, '0', STR_PAD_LEFT); ?></span></td>
                            <td style="color: white; font-weight: 500;"><?php echo htmlspecialchars($order->username ?? 'Guest'); ?></td>
                            <td style="color: rgba(255,255,255,0.4); font-size: 0.75rem;"><?php echo date('M d, Y', strtotime($order->created_at)); ?></td>
                            <td><span class="mono" style="color: white; font-weight: 600;">$<?php echo number_format($order->total_amount, 2); ?></span></td>
                            <td>
                                <?php
                                $badgeClass = match($order->status) {
                                    'completed' => 'badge-completed',
                                    'pending' => 'badge-pending',
                                    'processing' => 'badge-processing',
                                    'cancelled' => 'badge-cancelled',
                                    default => 'badge-pending'
                                };
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($order->status); ?></span>
                            </td>
                            <td>
                                <a href="<?php echo APPURL; ?>admin/orders/view.php?id=<?php echo $order->id; ?>" style="font-size: 0.75rem; color: rgba(255,255,255,0.4); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.4)'">View →</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; color: rgba(255,255,255,0.2); padding: 40px;">No orders yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
        datasets: [{
            label: 'Revenue',
            data: [1200, 1900, 1500, 2100, 2400, 2800, 2600, 3200, 2900, 3400, 3800, 4200],
            borderColor: 'rgba(255,255,255,0.6)',
            backgroundColor: 'rgba(255,255,255,0.03)',
            tension: 0.4, fill: true, borderWidth: 1.5,
            pointBackgroundColor: 'rgba(255,255,255,0.8)',
            pointRadius: 3, pointHoverRadius: 5
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 11 } } }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completed', 'Pending', 'Processing', 'Cancelled'],
        datasets: [{
            data: [45, 30, 15, 10],
            backgroundColor: ['rgba(74,222,128,0.7)', 'rgba(251,191,36,0.7)', 'rgba(96,165,250,0.7)', 'rgba(248,113,113,0.7)'],
            borderWidth: 0, borderRadius: 4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.4)', font: { size: 11 }, padding: 16, boxWidth: 10 } }
        },
        cutout: '65%'
    }
});
</script>
</body>
</html>
