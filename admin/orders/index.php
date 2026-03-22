<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/config.php';

// Fetch all orders
try {
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o 
                           LEFT JOIN users u ON o.user_id = u.id 
                           ORDER BY o.created_at DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $orders = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders — Premeditatio Malorum</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
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
        
        /* Table Box */
        .table-card { background: #0a0a0a; border: 1px solid rgba(255,255,255,0.06); border-radius: 12px; padding: 24px; width: 100%; overflow-x: auto; }
        .data-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .data-table th { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(255,255,255,0.3); padding: 12px 16px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.06); }
        .data-table td { padding: 14px 16px; font-size: 0.8rem; color: rgba(255,255,255,0.7); border-bottom: 1px solid rgba(255,255,255,0.04); vertical-align: middle; }
        .data-table tr:hover td { background: rgba(255,255,255,0.02); }
        .data-table tr:last-child td { border-bottom: none; }

        /* Badge */
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; }
        
        .badge-pending   { background: rgba(251,191,36,0.1); color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
        .badge-completed { background: rgba(74,222,128,0.1); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
        .badge-processing{ background: rgba(96,165,250,0.1); color: #60a5fa; border: 1px solid rgba(96,165,250,0.2); }
        .badge-cancelled { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
        .badge-danger    { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
        .badge-success   { background: rgba(74,222,128,0.1); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
        .badge-warning   { background: rgba(251,191,36,0.1); color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
        
        .action-select { background: rgba(255,255,255,0.03); color: white; border: 1px solid rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; outline: none; }
        .action-select:focus { border-color: rgba(255,255,255,0.3); }

        ::-webkit-scrollbar { width: 4px; height: 4px;}
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
        <a href="<?php echo APPURL; ?>admin/" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="<?php echo APPURL; ?>admin/orders/" class="nav-item active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Orders
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

        <div class="nav-section">Directory</div>
        <a href="<?php echo APPURL; ?>admin/customers/" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Customers
        </a>

        <div class="nav-section">Intelligence</div>
        <a href="<?php echo APPURL; ?>admin/simulation.php" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
            ML Simulation
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
            <div style="font-size: 1rem; font-weight: 600; color: white;">Orders Management</div>
            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.3); margin-top: 1px;">Track and manage customer orders</div>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <button class="bg-white text-black text-xs font-semibold uppercase tracking-widest px-4 py-2 flex items-center gap-2 hover:bg-gray-200 transition-colors">
                Export <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </button>
            <div style="display: flex; align-items: center; gap: 10px; margin-left: 8px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: white;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content space-y-6">
        
        <!-- Tab Navigation -->
        <div class="flex gap-6 border-b border-white/10 pb-2">
            <a href="#" class="text-xs uppercase tracking-widest font-semibold text-white border-b-2 border-white pb-2 -mb-[9px]">All Orders</a>
            <a href="#" class="text-xs uppercase tracking-widest font-medium text-white/40 hover:text-white transition-colors pb-2">Pending</a>
            <a href="#" class="text-xs uppercase tracking-widest font-medium text-white/40 hover:text-white transition-colors pb-2">Processing</a>
            <a href="#" class="text-xs uppercase tracking-widest font-medium text-white/40 hover:text-white transition-colors pb-2">Completed</a>
        </div>

        <!-- Table Box -->
        <div class="table-card mt-6">
            <div class="flex justify-between items-center mb-6">
                <h5 class="text-sm font-semibold tracking-wide text-white/80 uppercase">Order History</h5>
                <input type="text" class="bg-white/5 border border-white/10 text-white text-xs px-3 py-2 w-64 focus:outline-none focus:border-white/30 transition-colors" placeholder="Search order ID or customer...">
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($orders): ?>
                        <?php foreach($orders as $order): ?>
                            <tr>
                                <td><span class="mono" style="color: rgba(255,255,255,0.5);">#<?php echo $order->order_number ?? str_pad($order->id, 5, '0', STR_PAD_LEFT); ?></span></td>
                                <td>
                                    <div style="color: white; font-weight: 500; font-size: 0.85rem;"><?php echo htmlspecialchars($order->username ?? 'Guest'); ?></div>
                                    <div style="color: rgba(255,255,255,0.4); font-size: 0.7rem;"><?php echo htmlspecialchars($order->email ?? ''); ?></div>
                                </td>
                                <td style="color: rgba(255,255,255,0.5); font-size: 0.75rem;"><?php echo date('M d, Y', strtotime($order->created_at)); ?></td>
                                <td>
                                    <?php
                                    try {
                                        $items_stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?");
                                        $items_stmt->execute([$order->id]);
                                        $item_count = $items_stmt->fetch(PDO::FETCH_OBJ)->count;
                                        echo "<span style='color: rgba(255,255,255,0.5); font-size: 0.75rem;'>" . $item_count . " item" . ($item_count > 1 ? 's' : '') . "</span>";
                                    } catch(Exception $e) { echo "—"; }
                                    ?>
                                </td>
                                <td><span class="mono" style="color: white; font-weight: 600;">$<?php echo number_format($order->total_amount, 2); ?></span></td>
                                <td>
                                    <?php 
                                    $p_status = strtolower($order->payment_status ?? 'pending');
                                    $p_badge = match($p_status) {
                                        'paid', 'success' => 'badge-success',
                                        'pending' => 'badge-warning',
                                        default => 'badge-danger'
                                    };
                                    ?>
                                    <span class="badge <?php echo $p_badge; ?>"><?php echo ucfirst($p_status); ?></span>
                                </td>
                                <td>
                                    <select class="action-select">
                                        <option value="pending" <?php echo ($order->status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo ($order->status == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo ($order->status == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo ($order->status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td class="text-right">
                                    <a href="view.php?id=<?php echo $order->id; ?>" class="text-white/40 hover:text-white transition-colors text-xs mr-3"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 inline"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                    <a href="invoice.php?id=<?php echo $order->id; ?>" class="text-white/40 hover:text-white transition-colors text-xs"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4 inline"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: rgba(255,255,255,0.2); padding: 60px 0;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 12px; display: block;"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                <div class="text-xs uppercase tracking-widest">No orders found</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
