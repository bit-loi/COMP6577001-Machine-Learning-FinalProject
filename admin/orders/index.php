<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/config.php';

// Fetch filtered orders
$statusFilter = $_GET['status'] ?? 'all';
try {
    $sql = "SELECT o.*, u.username, u.email FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id";
    
    if ($statusFilter !== 'all') {
        $sql .= " WHERE o.status = :status";
    }
    
    $sql .= " ORDER BY o.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    if ($statusFilter !== 'all') {
        $stmt->execute(['status' => $statusFilter]);
    } else {
        $stmt->execute();
    }
    $orders = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $orders = [];
}
?>
<?php
$pageTitle = 'Orders Management';
$pageDescription = 'Track and manage customer orders';
$topbarAction = '<button class="bg-white border border-gray-200 text-gray-700 text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-gray-50 transition-colors shadow-sm">
    Export <i data-lucide="download" style="width:14px; height:14px;"></i>
</button>';
require_once '../includes/header.php';
?>
        
        <!-- Tab Navigation -->
        <div class="flex gap-6 border-b border-gray-200 pb-2">
            <?php 
            $tabs = [
                'all' => 'All Orders',
                'pending' => 'Pending',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled'
            ];
            foreach ($tabs as $key => $label):
                $active = ($statusFilter === $key);
            ?>
            <a href="?status=<?php echo $key; ?>" class="text-xs uppercase tracking-widest <?php echo $active ? 'font-bold text-shopmart-600 border-b-2 border-shopmart-600 pb-2 -mb-[10px]' : 'font-medium text-gray-400 hover:text-shopmart-600 transition-colors pb-2'; ?>">
                <?php echo $label; ?>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Table Box -->
        <div class="table-card mt-6">
            <div class="flex justify-between items-center mb-6">
                <h5 class="text-sm font-semibold tracking-wide text-gray-800 uppercase">Order History</h5>
                <input type="text" class="bg-gray-50 border border-gray-200 text-gray-800 rounded-lg text-xs px-3 py-2 w-64 focus:outline-none focus:border-shopmart-500 transition-colors" placeholder="Search order ID or customer...">
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
                                <td><span class="mono" style="color: #64748b;">#<?php echo $order->order_number ?? str_pad($order->id, 5, '0', STR_PAD_LEFT); ?></span></td>
                                <td>
                                    <div style="color: #0f172a; font-weight: 600; font-size: 0.85rem;"><?php echo htmlspecialchars($order->username ?? 'Guest'); ?></div>
                                    <div style="color: #64748b; font-size: 0.7rem;"><?php echo htmlspecialchars($order->email ?? ''); ?></div>
                                </td>
                                <td style="color: #64748b; font-size: 0.75rem;"><?php echo date('M d, Y', strtotime($order->created_at)); ?></td>
                                <td>
                                    <?php
                                    try {
                                        $items_stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?");
                                        $items_stmt->execute([$order->id]);
                                        $item_count = $items_stmt->fetch(PDO::FETCH_OBJ)->count;
                                        echo "<span style='color: #64748b; font-size: 0.75rem;'>" . $item_count . " item" . ($item_count > 1 ? 's' : '') . "</span>";
                                    } catch(Exception $e) { echo "—"; }
                                    ?>
                                </td>
                                <td><span class="mono" style="color: #EE4D2D; font-weight: 600;">$<?php echo number_format($order->total_amount, 2); ?></span></td>
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
                                    <select class="bg-gray-50 text-gray-700 border border-gray-200 rounded px-2 py-1 text-xs outline-none focus:border-shopmart-500">
                                        <option value="pending" <?php echo ($order->status == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="processing" <?php echo ($order->status == 'processing') ? 'selected' : ''; ?>>Processing</option>
                                        <option value="completed" <?php echo ($order->status == 'completed') ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo ($order->status == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td class="text-right">
                                    <a href="view.php?id=<?php echo $order->id; ?>" class="action-icon" title="View"><i data-lucide="eye" style="width:16px;height:16px;"></i></a>
                                    <a href="invoice.php?id=<?php echo $order->id; ?>" class="action-icon" title="Invoice"><i data-lucide="file-text" style="width:16px;height:16px;"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #94a3b8; padding: 60px 0;">
                                <i data-lucide="inbox" style="width:32px; height:32px; color: #cbd5e1; margin: 0 auto 12px; display: block;"></i>
                                <div class="text-xs uppercase tracking-widest font-semibold">No orders found</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

<?php require_once '../includes/footer.php'; ?>
