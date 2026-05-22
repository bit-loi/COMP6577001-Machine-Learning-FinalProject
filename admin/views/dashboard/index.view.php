<?php
/**
 * Dashboard View Template
 * 
 * Renders the admin dashboard overview page.
 * Expects variables: $stats, $recentOrders, $lowStockProducts, $topProducts, $monthlyRevenue
 */
?>
<link rel="stylesheet" href="<?php echo APPURL; ?>admin/assets/css/admin-components.css">

<!-- Stats -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon" style="background: #FFF4ED; color: #EE4D2D;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.27 6.96 8.73 5.04 8.73-5.04"/><path d="M12 22.08V12"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Products</div>
            <div class="stat-number"><?php echo number_format($stats->totalProducts); ?></div>
            <div style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px;">In catalogue</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #EFF6FF; color: #3b82f6;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Customers</div>
            <div class="stat-number"><?php echo number_format($stats->totalUsers); ?></div>
            <div style="font-size: 0.65rem; color: #94a3b8; margin-top: 4px;">Registered users</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #FFF7ED; color: #f59e0b;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Total Orders</div>
            <div class="stat-number"><?php echo number_format($stats->totalOrders); ?></div>
            <div style="font-size: 0.65rem; color: #f59e0b; margin-top: 4px; font-weight: 600;"><?php echo $stats->pendingOrders; ?> pending</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background: #F0FDF4; color: #10b981;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div class="stat-info">
            <div class="stat-label">Revenue</div>
            <div class="stat-number">$<?php echo number_format($stats->totalRevenue, 0); ?></div>
            <div style="font-size: 0.65rem; color: #10b981; margin-top: 4px; font-weight: 600;">Completed</div>
        </div>
    </div>
</div>

<!-- Charts -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 24px;">
    <div class="table-card">
        <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="line-chart" style="width: 16px; height: 16px; color: #EE4D2D;"></i> Revenue Overview — <?php echo date('Y'); ?>
        </div>
        <div style="height: 280px;"><canvas id="salesChart"></canvas></div>
    </div>
    <div class="table-card">
        <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="pie-chart" style="width: 16px; height: 16px; color: #2563eb;"></i> Order Status
        </div>
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
        <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="award" style="width: 16px; height: 16px; color: #EAB308;"></i> Top Selling Products
        </div>
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
    <div style="display: flex; justify-content: space-between; margin-bottom: 20px; align-items: center;">
        <div style="font-size: 0.85rem; font-weight: 700; color: #0f172a; margin-bottom: 0; display: flex; align-items: center; gap: 8px;">
            <i data-lucide="shopping-bag" style="width: 16px; height: 16px; color: #059669;"></i> Recent Orders
        </div>
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
// Revenue chart data from controller
const monthlyData = <?php echo json_encode($monthlyRevenue); ?>;
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
        datasets: [{
            label: 'Revenue', data: monthlyData,
            borderColor: '#FF6B35', backgroundColor: 'rgba(255,107,53,0.08)',
            tension: 0.4, fill: true, borderWidth: 2,
            pointBackgroundColor: '#FF6B35', pointRadius: 3, pointHoverRadius: 6
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

// Order status chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Completed','Pending','Processing','Cancelled'],
        datasets: [{
            data: [<?php echo "{$stats->completedOrders}, {$stats->pendingOrders}, {$stats->processingOrders}, {$stats->cancelledOrders}"; ?>],
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
