<?php
session_start();
require '../config/config.php';
require '../middleware/auth.php';
require '../includes/product-image.php';

$userId = $_SESSION['user_id'];
$statusFilter = $_GET['status'] ?? 'all';

$stmtUser = $conn->prepare("SELECT username, first_name, last_name FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch(PDO::FETCH_OBJ);
$accountName = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : '';
if ($accountName === '') {
    $accountName = ucwords($user->username ?? $_SESSION['username'] ?? 'User');
}

$orders = [];
try {
    $sql = "SELECT * FROM orders WHERE user_id = ?";
    $params = [$userId];
    if ($statusFilter !== 'all') {
        $sql .= " AND status = ?";
        $params[] = $statusFilter;
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $orders = [];
}

$statusLabels = [
    'all'        => 'Semua',
    'pending'    => 'Menunggu',
    'processing' => 'Diproses',
    'completed'  => 'Selesai',
    'cancelled'  => 'Dibatalkan',
];

$statusIcons = [
    'pending'    => 'clock',
    'processing' => 'loader',
    'completed'  => 'check-circle',
    'cancelled'  => 'x-circle',
];

$currentPage = 'purchases';
include '../includes/header.php';
include 'includes/styles.php';
?>

<div class="account-page">
    <div class="account-wrap">
        <nav class="account-breadcrumb">
            <a href="<?php echo APPURL; ?>">Home</a>
            <span>›</span>
            <strong>My Purchases</strong>
        </nav>

        <div class="account-layout">
            <?php include 'includes/sidebar.php'; ?>

            <div>
                <div class="account-card account-card-pad" style="margin-bottom:20px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:20px;">
                        <h2 class="account-card-title" style="margin:0;">
                            <span class="icon-wrap"><i data-lucide="package" style="width:18px;height:18px;"></i></span>
                            Pesanan Saya
                        </h2>
                        <div style="font-size:0.85rem;color:#888;">
                            <strong style="color:#EE4D2D;"><?php echo count($orders); ?></strong> pesanan
                        </div>
                    </div>

                    <div class="status-tabs">
                        <?php foreach ($statusLabels as $key => $label): ?>
                        <a href="?status=<?php echo $key; ?>" class="status-tab <?php echo $statusFilter === $key ? 'active' : ''; ?>">
                            <?php echo $label; ?>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($orders): ?>
                    <?php foreach ($orders as $order):
                        $status = strtolower($order->status ?? 'pending');
                        $badgeClass = 'badge-' . (in_array($status, ['pending','processing','completed','cancelled']) ? $status : 'pending');
                        $orderNum = $order->order_number ?? ('SM-' . str_pad($order->id, 5, '0', STR_PAD_LEFT));

                        $items = [];
                        try {
                            $stmtItems = $conn->prepare("SELECT oi.*, p.image, p.brand FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                            $stmtItems->execute([$order->id]);
                            $items = $stmtItems->fetchAll(PDO::FETCH_OBJ);
                        } catch (PDOException $e) {}
                    ?>
                    <div class="order-card">
                        <div class="order-card-head">
                            <div>
                                <div style="font-size:0.72rem;color:#999;text-transform:uppercase;letter-spacing:0.05em;font-weight:600;margin-bottom:4px;">No. Pesanan</div>
                                <div style="font-size:0.95rem;font-weight:800;color:#111;font-family:monospace;">#<?php echo htmlspecialchars($orderNum); ?></div>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:0.72rem;color:#999;margin-bottom:4px;"><?php echo date('d M Y, H:i', strtotime($order->created_at)); ?></div>
                                <span class="badge-status <?php echo $badgeClass; ?>">
                                    <i data-lucide="<?php echo $statusIcons[$status] ?? 'package'; ?>" style="width:11px;height:11px;"></i>
                                    <?php echo ucfirst($status); ?>
                                </span>
                            </div>
                        </div>

                        <div style="padding:16px 24px;">
                            <?php foreach ($items as $item):
                                $imgObj = (object)['image' => $item->image ?? '', 'name' => $item->product_name ?? 'Product'];
                            ?>
                            <div style="display:flex;align-items:center;gap:16px;padding:14px 0;border-bottom:1px solid #f5f5f5;">
                                <div style="width:64px;height:64px;border-radius:12px;overflow:hidden;background:#f8f8f8;flex-shrink:0;border:1px solid #eee;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                                    <?php echo getProductImage($imgObj, '128x128', '', ['style' => 'width:100%;height:100%;object-fit:cover;']); ?>
                                </div>
                                <div style="flex:1;min-width:0;">
                                    <div style="font-size:0.9rem;font-weight:700;color:#222;margin-bottom:4px;"><?php echo htmlspecialchars($item->product_name); ?></div>
                                    <?php if (!empty($item->brand)): ?>
                                    <div style="font-size:0.75rem;color:#999;"><?php echo htmlspecialchars($item->brand); ?></div>
                                    <?php endif; ?>
                                    <div style="font-size:0.78rem;color:#888;margin-top:4px;">Qty: <?php echo (int)$item->quantity; ?></div>
                                </div>
                                <div style="font-size:0.95rem;font-weight:800;color:#EE4D2D;flex-shrink:0;">$<?php echo number_format($item->subtotal, 2); ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 24px;background:#fafafa;border-top:1px solid #eee;flex-wrap:wrap;gap:12px;">
                            <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                                <div style="font-size:0.8rem;color:#666;display:flex;align-items:center;gap:6px;">
                                    <i data-lucide="credit-card" style="width:14px;height:14px;color:#888;"></i>
                                    <?php echo ucfirst($order->payment_method ?? 'wallet'); ?>
                                </div>
                                <?php if (!empty($order->shipping_city)): ?>
                                <div style="font-size:0.8rem;color:#666;display:flex;align-items:center;gap:6px;">
                                    <i data-lucide="map-pin" style="width:14px;height:14px;color:#888;"></i>
                                    <?php echo htmlspecialchars($order->shipping_city); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:0.72rem;color:#999;margin-bottom:2px;">Total</div>
                                <div style="font-size:1.2rem;font-weight:800;color:#EE4D2D;">$<?php echo number_format($order->total_amount, 2); ?></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>

                <?php else: ?>
                <div class="account-card">
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i data-lucide="shopping-bag" style="width:36px;height:36px;color:#EE4D2D;"></i>
                        </div>
                        <h3 style="font-size:1.1rem;font-weight:800;color:#222;margin:0 0 8px;">Belum ada pesanan</h3>
                        <p style="font-size:0.88rem;color:#999;margin:0 0 24px;max-width:360px;margin-left:auto;margin-right:auto;">
                            <?php echo $statusFilter !== 'all' ? 'Tidak ada pesanan dengan status ini.' : 'Mulai belanja dan pesanan Anda akan muncul di sini.'; ?>
                        </p>
                        <a href="<?php echo APPURL; ?>" class="btn-primary" style="display:inline-block;text-decoration:none;">
                            <i data-lucide="shopping-cart" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                            Mulai Belanja
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require '../includes/footer.php'; ?>
