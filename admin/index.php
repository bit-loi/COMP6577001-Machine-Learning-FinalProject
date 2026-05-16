<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/config.php';

// Fetch real statistics from database
try {
    $totalProducts = $conn->query("SELECT COUNT(*) as c FROM products")->fetch(PDO::FETCH_OBJ)->c;
    $totalUsers = $conn->query("SELECT COUNT(*) as c FROM users WHERE role = 'customer' OR is_admin = 0")->fetch(PDO::FETCH_OBJ)->c;
    $totalOrders = $conn->query("SELECT COUNT(*) as c FROM orders")->fetch(PDO::FETCH_OBJ)->c;
    $totalRevenue = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as r FROM orders WHERE status = 'completed'")->fetch(PDO::FETCH_OBJ)->r;
    $pendingOrders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'pending'")->fetch(PDO::FETCH_OBJ)->c;
    $completedOrders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'completed'")->fetch(PDO::FETCH_OBJ)->c;
    $cancelledOrders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'cancelled'")->fetch(PDO::FETCH_OBJ)->c;
    $processingOrders = $conn->query("SELECT COUNT(*) as c FROM orders WHERE status = 'processing'")->fetch(PDO::FETCH_OBJ)->c;

    // Recent orders
    $recentOrders = $conn->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 8")->fetchAll(PDO::FETCH_OBJ);

    // Low stock products
    $lowStockProducts = $conn->query("SELECT * FROM products WHERE stock < 10 AND status = 1 ORDER BY stock ASC LIMIT 5")->fetchAll(PDO::FETCH_OBJ);

    // Top selling products
    $topProducts = $conn->query("SELECT p.name, p.price, COALESCE(SUM(oi.quantity), 0) as total_sold FROM products p LEFT JOIN order_items oi ON p.id = oi.product_id GROUP BY p.id ORDER BY total_sold DESC LIMIT 5")->fetchAll(PDO::FETCH_OBJ);

    // Monthly revenue for chart (real data)
    $monthlyRevenue = [];
    for ($m = 1; $m <= 12; $m++) {
        $stmt = $conn->prepare("SELECT COALESCE(SUM(total_amount), 0) as rev FROM orders WHERE MONTH(created_at) = ? AND YEAR(created_at) = ? AND status = 'completed'");
        $stmt->execute([$m, date('Y')]);
        $monthlyRevenue[] = (float)$stmt->fetch(PDO::FETCH_OBJ)->rev;
    }

} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $totalProducts = $totalUsers = $totalOrders = $totalRevenue = $pendingOrders = $completedOrders = $cancelledOrders = $processingOrders = 0;
    $recentOrders = $lowStockProducts = $topProducts = [];
    $monthlyRevenue = array_fill(0, 12, 0);
}
?>
<?php
$pageTitle = 'Overview';
$pageDescription = date('l, F j, Y');
require_once 'includes/header.php';
?>
        <!-- Stats -->
        <style>
            .stat-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); }
            .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
            .stat-info { flex: 1; }
            .stat-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 4px; }
            .stat-number { font-size: 1.25rem; font-weight: 800; color: #0f172a; line-height: 1; }
        </style>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
            <div class="stat-card">
                <div class="stat-icon" style="background: #FFF4ED; color: #EE4D2D;">
                    <i data-lucide="package" style="width: 24px; height: 24px;"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Total Products</div>
                    <div class="stat-number"><?php echo number_format($totalProducts); ?></div>
                    <div style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px;">In catalogue</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #EFF6FF; color: #3b82f6;">
                    <i data-lucide="users" style="width: 24px; height: 24px;"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Customers</div>
                    <div class="stat-number"><?php echo number_format($totalUsers); ?></div>
                    <div style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px;">Registered users</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #FFF7ED; color: #f59e0b;">
                    <i data-lucide="shopping-cart" style="width: 24px; height: 24px;"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-number"><?php echo number_format($totalOrders); ?></div>
                    <div style="font-size: 0.65rem; color: #f59e0b; margin-top: 4px; font-weight: 600;"><?php echo $pendingOrders; ?> pending</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #F0FDF4; color: #10b981;">
                    <i data-lucide="dollar-sign" style="width: 24px; height: 24px;"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Revenue</div>
                    <div class="stat-number">$<?php echo number_format($totalRevenue, 0); ?></div>
                    <div style="font-size: 0.65rem; color: #10b981; margin-top: 4px; font-weight: 600;">Completed</div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div class="table-card">
                <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 20px;">Revenue Overview — <?php echo date('Y'); ?></div>
                <div style="height: 280px;"><canvas id="salesChart"></canvas></div>
            </div>
            <div class="table-card">
                <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 20px;">Order Status</div>
                <div style="height: 280px;"><canvas id="statusChart"></canvas></div>
            </div>
        </div>

        <!-- Low Stock & Top Products -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div class="table-card">
                <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="alert-triangle" style="width:16px;height:16px;color:#EAB308;"></i> Low Stock Alert
                </div>
                <?php if($lowStockProducts): ?>
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Stock</th><th>Price</th></tr></thead>
                    <tbody>
                    <?php foreach($lowStockProducts as $lsp): ?>
                        <tr>
                            <td style="font-weight: 600; color: #0f172a;"><?php echo htmlspecialchars($lsp->name); ?></td>
                            <td>
                                <?php if($lsp->stock <= 3): ?>
                                    <span class="badge badge-danger"><?php echo $lsp->stock; ?> left</span>
                                <?php else: ?>
                                    <span class="badge badge-warning"><?php echo $lsp->stock; ?> left</span>
                                <?php endif; ?>
                            </td>
                            <td class="mono font-semibold text-shopmart-600">$<?php echo number_format($lsp->price, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #94a3b8;">
                    <i data-lucide="check-circle" style="width:32px; height:32px; color: #4ade80; margin: 0 auto 12px; display: block;"></i>
                    All products are well-stocked
                </div>
                <?php endif; ?>
            </div>
            <div class="table-card">
                <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 20px;">Top Selling Products</div>
                <?php if($topProducts && $topProducts[0]->total_sold > 0): ?>
                <table class="data-table">
                    <thead><tr><th>Product</th><th>Sold</th><th>Price</th></tr></thead>
                    <tbody>
                    <?php foreach($topProducts as $tp): ?>
                        <tr>
                            <td style="font-weight: 500; color: #333;"><?php echo htmlspecialchars($tp->name); ?></td>
                            <td style="font-weight: 600; color: #FF6B35;"><?php echo $tp->total_sold; ?></td>
                            <td>$<?php echo number_format($tp->price, 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 40px; color: #aaa;">
                    <i data-lucide="bar-chart-3" style="width:32px; height:32px; color: #ddd; margin: 0 auto 12px; display: block;"></i>
                    No sales data available yet
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="table-card">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 0;">Recent Orders</div>
                <a href="<?php echo APPURL; ?>admin/orders/" style="font-size: 0.8rem; color: #EE4D2D; text-decoration: none; font-weight: 600;">View all →</a>
            </div>
            <table class="data-table">
                <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                <?php if($recentOrders): ?>
                    <?php foreach($recentOrders as $order): ?>
                    <tr>
                        <td><span style="font-family: monospace; color: #888;">#<?php echo str_pad($order->id, 5, '0', STR_PAD_LEFT); ?></span></td>
                        <td style="font-weight: 500; color: #333;"><?php echo htmlspecialchars($order->username ?? 'Guest'); ?></td>
                        <td style="color: #999; font-size: 0.8rem;"><?php echo date('M d, Y', strtotime($order->created_at)); ?></td>
                        <td style="font-weight: 600; color: #333;">$<?php echo number_format($order->total_amount, 2); ?></td>
                        <td>
                            <?php
                            $bc = match($order->status) {
                                'completed' => 'badge-completed',
                                'pending' => 'badge-pending',
                                'processing' => 'badge-processing',
                                'cancelled' => 'badge-cancelled',
                                default => 'badge-pending'
                            };
                            ?>
                            <span class="badge <?php echo $bc; ?>"><?php echo ucfirst($order->status); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center; color: #aaa; padding: 40px;">No orders yet</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
// Real revenue data from DB
const monthlyData = <?php echo json_encode($monthlyRevenue); ?>;
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Revenue',
            data: monthlyData,
            borderColor: '#FF6B35',
            backgroundColor: 'rgba(255,107,53,0.08)',
            tension: 0.4, fill: true, borderWidth: 2,
            pointBackgroundColor: '#FF6B35',
            pointRadius: 3, pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { color: '#aaa', font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { color: '#aaa', font: { size: 11 } } }
        }
    }
});

// Real status data from DB
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completed','Pending','Processing','Cancelled'],
        datasets: [{
            data: [<?php echo "$completedOrders, $pendingOrders, $processingOrders, $cancelledOrders"; ?>],
            backgroundColor: ['#4ade80','#fbbf24','#60a5fa','#f87171'],
            borderWidth: 0, borderRadius: 4
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { color: '#888', font: { size: 11 }, padding: 16, boxWidth: 12 } } },
        cutout: '65%'
    }
});
    </script>
<?php require_once 'includes/footer.php'; ?>
