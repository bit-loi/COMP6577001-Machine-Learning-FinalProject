<?php
session_start();
require '../config/config.php';
require '../middleware/auth.php';
require '../includes/product-image.php';

$sellerId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT is_seller, username, first_name, last_name, wallet_balance FROM users WHERE id = ?");
$stmt->execute([$sellerId]);
$user = $stmt->fetch(PDO::FETCH_OBJ);

if (!$user || !$user->is_seller) {
    $conn->prepare("UPDATE users SET is_seller = 1 WHERE id = ?")->execute([$sellerId]);
    $user = $user ?: (object)['is_seller' => 1, 'username' => $_SESSION['username'], 'first_name' => '', 'last_name' => '', 'wallet_balance' => 0];
    $user->is_seller = 1;
}

$sellerName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
if ($sellerName === '') {
    $sellerName = ucwords($user->username ?? 'My Shop');
}
$walletBalance = (float)($user->wallet_balance ?? 0);
$justRegistered = isset($_GET['registered']);

// Stats
$totalProducts = 0;
$totalRevenue = 0;
$unitsSold = 0;
$lowStock = 0;

try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ?");
    $stmt->execute([$sellerId]);
    $totalProducts = (int)$stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COALESCE(SUM(oi.subtotal), 0), COALESCE(SUM(oi.quantity), 0)
        FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE p.seller_id = ?");
    $stmt->execute([$sellerId]);
    $row = $stmt->fetch(PDO::FETCH_NUM);
    $totalRevenue = (float)($row[0] ?? 0);
    $unitsSold = (int)($row[1] ?? 0);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE seller_id = ? AND stock <= 5");
    $stmt->execute([$sellerId]);
    $lowStock = (int)$stmt->fetchColumn();
} catch (PDOException $e) {}

// Recent sales
$recentSales = [];
try {
    $stmt = $conn->prepare("
        SELECT oi.product_name, oi.quantity, oi.subtotal, o.order_number, o.status, o.created_at, p.image
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ?
        ORDER BY o.created_at DESC
        LIMIT 6
    ");
    $stmt->execute([$sellerId]);
    $recentSales = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {}

// Products
$products = [];
try {
    $stmt = $conn->prepare("SELECT * FROM products WHERE seller_id = ? ORDER BY created_at DESC");
    $stmt->execute([$sellerId]);
    $products = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {}

$currentSellerPage = 'dashboard';
include '../includes/header.php';
include 'includes/styles.php';
?>

<div class="seller-page">
    <div class="seller-wrap">
        <nav class="seller-breadcrumb">
            <a href="<?php echo APPURL; ?>">Home</a>
            <span>›</span>
            <strong>Seller Dashboard</strong>
        </nav>

        <?php if ($justRegistered): ?>
        <div style="padding:14px 18px;background:#ecfdf5;color:#059669;border-radius:12px;font-size:0.88rem;font-weight:600;border:1px solid #bbf7d0;margin-bottom:20px;display:flex;align-items:center;gap:10px;box-shadow:0 2px 8px rgba(5,150,105,0.08);">
            <i data-lucide="party-popper" style="width:18px;height:18px;"></i>
            Selamat! Anda sekarang terdaftar sebagai seller di Shopmart.
        </div>
        <?php endif; ?>

        <div class="seller-layout">
            <?php include 'includes/sidebar.php'; ?>

            <div>
                <!-- Welcome banner -->
                <div class="welcome-banner">
                    <div class="welcome-banner-inner">
                        <h1>Selamat datang, <?php echo htmlspecialchars(explode(' ', $sellerName)[0]); ?>! 👋</h1>
                        <p>Kelola produk, pantau penjualan, dan terima pembayaran langsung ke Shopmart Wallet Anda.</p>
                    </div>
                    <a href="create.php" class="btn-seller">
                        <i data-lucide="plus" style="width:18px;height:18px;"></i>
                        Tambah Produk
                    </a>
                </div>

                <!-- Stats -->
                <div class="stat-grid">
                    <div class="stat-box">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#FFF4ED,#FFE6D5);color:#EE4D2D;">
                            <i data-lucide="package" style="width:22px;height:22px;"></i>
                        </div>
                        <div class="stat-label">Total Produk</div>
                        <div class="stat-value"><?php echo $totalProducts; ?></div>
                        <div class="stat-sub"><?php echo $lowStock; ?> stok menipis</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#2563eb;">
                            <i data-lucide="trending-up" style="width:22px;height:22px;"></i>
                        </div>
                        <div class="stat-label">Total Penjualan</div>
                        <div class="stat-value">$<?php echo number_format($totalRevenue, 2); ?></div>
                        <div class="stat-sub">Pendapatan kotor</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0);color:#059669;">
                            <i data-lucide="shopping-bag" style="width:22px;height:22px;"></i>
                        </div>
                        <div class="stat-label">Unit Terjual</div>
                        <div class="stat-value"><?php echo number_format($unitsSold); ?></div>
                        <div class="stat-sub">Item terjual</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon" style="background:linear-gradient(135deg,#ecfdf5,#bbf7d0);color:#16a34a;">
                            <i data-lucide="wallet" style="width:22px;height:22px;"></i>
                        </div>
                        <div class="stat-label">Saldo Wallet</div>
                        <div class="stat-value">
                            <?php if ($walletBalance < 0): ?>∞<?php else: ?>$<?php echo number_format($walletBalance, 2); ?><?php endif; ?>
                        </div>
                        <a href="<?php echo APPURL; ?>account/wallet.php" style="font-size:0.72rem;color:#EE4D2D;font-weight:600;text-decoration:none;margin-top:4px;display:inline-block;">Lihat wallet →</a>
                    </div>
                </div>

                <div class="seller-two-col">
                    <!-- Main column -->
                    <div>
                        <!-- Products -->
                        <div class="seller-card seller-card-pad" id="products" style="margin-bottom:20px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
                                <h2 style="font-size:1.05rem;font-weight:700;color:#111;margin:0;display:flex;align-items:center;gap:10px;">
                                    <span style="width:36px;height:36px;border-radius:10px;background:#FFF4ED;color:#EE4D2D;display:flex;align-items:center;justify-content:center;">
                                        <i data-lucide="layout-grid" style="width:18px;height:18px;"></i>
                                    </span>
                                    Produk Saya (<?php echo count($products); ?>)
                                </h2>
                                <a href="create.php" class="btn-seller-outline">
                                    <i data-lucide="plus" style="width:15px;height:15px;"></i> Tambah
                                </a>
                            </div>

                            <?php if ($products): ?>
                            <div class="product-grid">
                                <?php foreach ($products as $p):
                                    $inStock = ($p->stock ?? 0) > 0;
                                ?>
                                <div class="product-card-seller">
                                    <div style="height:160px;background:#f8fafc;position:relative;overflow:hidden;">
                                        <?php echo getProductImage($p, '320x320', '', ['style' => 'width:100%;height:100%;object-fit:cover;']); ?>
                                        <span style="position:absolute;top:10px;right:10px;padding:4px 10px;border-radius:20px;font-size:0.7rem;font-weight:700;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.12);color:<?php echo $inStock ? '#059669' : '#dc2626'; ?>;">
                                            Stok: <?php echo (int)($p->stock ?? 0); ?>
                                        </span>
                                    </div>
                                    <div style="padding:16px;">
                                        <h3 style="font-size:0.9rem;font-weight:700;color:#222;margin:0 0 6px;line-height:1.35;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?php echo htmlspecialchars($p->name); ?></h3>
                                        <div style="font-size:1.05rem;font-weight:800;color:#EE4D2D;">$<?php echo number_format($p->price, 2); ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <div style="text-align:center;padding:48px 24px;background:#fafafa;border-radius:14px;border:2px dashed #e5e5e5;">
                                <div style="width:72px;height:72px;margin:0 auto 16px;background:linear-gradient(135deg,#FFF4ED,#FFE6D5);border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 6px 20px rgba(238,77,45,0.15);">
                                    <i data-lucide="package-open" style="width:32px;height:32px;color:#EE4D2D;"></i>
                                </div>
                                <h3 style="font-size:1rem;font-weight:700;color:#333;margin:0 0 8px;">Belum ada produk</h3>
                                <p style="font-size:0.85rem;color:#999;margin:0 0 20px;">Mulai jualan dengan menambahkan produk pertama Anda.</p>
                                <a href="create.php" class="btn-seller" style="background:linear-gradient(180deg,#FF6B35,#EE4D2D);color:#fff;">
                                    <i data-lucide="plus" style="width:16px;height:16px;"></i> Tambah Produk Pertama
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- How it works (compact) -->
                        <?php if (!$products): ?>
                        <div class="seller-card seller-card-pad">
                            <h2 style="font-size:1rem;font-weight:700;color:#111;margin:0 0 16px;">Cara Kerja Seller</h2>
                            <div class="how-step">
                                <span class="how-num">1</span>
                                <div><strong style="color:#111;">List Product</strong><br><span style="font-size:0.85rem;color:#666;">Tambahkan detail produk, harga, dan stok.</span></div>
                            </div>
                            <div class="how-step">
                                <span class="how-num">2</span>
                                <div><strong style="color:#111;">Sell</strong><br><span style="font-size:0.85rem;color:#666;">Pelanggan membeli produk Anda di Shopmart.</span></div>
                            </div>
                            <div class="how-step">
                                <span class="how-num">3</span>
                                <div><strong style="color:#111;">Earn</strong><br><span style="font-size:0.85rem;color:#666;">Pembayaran otomatis masuk ke Wallet Anda.</span></div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sidebar column -->
                    <div style="display:flex;flex-direction:column;gap:20px;">
                        <!-- Recent sales -->
                        <div class="seller-card seller-card-pad">
                            <h2 style="font-size:0.95rem;font-weight:700;color:#111;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                                <i data-lucide="activity" style="width:17px;height:17px;color:#EE4D2D;"></i>
                                Penjualan Terbaru
                            </h2>
                            <?php if ($recentSales): ?>
                                <?php foreach ($recentSales as $sale):
                                    $imgObj = (object)['image' => $sale->image ?? '', 'name' => $sale->product_name];
                                    $status = strtolower($sale->status ?? 'pending');
                                ?>
                                <div class="sale-row">
                                    <div style="width:44px;height:44px;border-radius:10px;overflow:hidden;flex-shrink:0;border:1px solid #eee;">
                                        <?php echo getProductImage($imgObj, '88x88', '', ['style'=>'width:100%;height:100%;object-fit:cover;']); ?>
                                    </div>
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:0.82rem;font-weight:700;color:#222;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($sale->product_name); ?></div>
                                        <div style="font-size:0.72rem;color:#999;margin-top:2px;">
                                            <?php echo date('d M Y', strtotime($sale->created_at)); ?> · Qty <?php echo (int)$sale->quantity; ?>
                                        </div>
                                    </div>
                                    <div style="text-align:right;flex-shrink:0;">
                                        <div style="font-size:0.88rem;font-weight:800;color:#EE4D2D;">$<?php echo number_format($sale->subtotal, 2); ?></div>
                                        <div style="font-size:0.68rem;color:#aaa;text-transform:uppercase;margin-top:2px;"><?php echo $status; ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                            <div style="text-align:center;padding:24px 12px;color:#999;">
                                <i data-lucide="inbox" style="width:32px;height:32px;color:#ddd;margin:0 auto 10px;display:block;"></i>
                                <p style="font-size:0.82rem;margin:0;">Belum ada penjualan.</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Quick actions -->
                        <div class="seller-card seller-card-pad">
                            <h2 style="font-size:0.95rem;font-weight:700;color:#111;margin:0 0 14px;">Aksi Cepat</h2>
                            <div style="display:flex;flex-direction:column;gap:8px;">
                                <a href="create.php" style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:10px;background:#FFF4ED;color:#EE4D2D;text-decoration:none;font-size:0.85rem;font-weight:600;border:1px solid #FECBA6;transition:all .15s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(238,77,45,0.15)'" onmouseout="this.style.boxShadow=''">
                                    <i data-lucide="plus-circle" style="width:18px;height:18px;"></i> Tambah Produk Baru
                                </a>
                                <a href="<?php echo APPURL; ?>account/wallet.php" style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:10px;background:#f0fdf4;color:#059669;text-decoration:none;font-size:0.85rem;font-weight:600;border:1px solid #bbf7d0;transition:all .15s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(5,150,105,0.12)'" onmouseout="this.style.boxShadow=''">
                                    <i data-lucide="wallet" style="width:18px;height:18px;"></i> Cek Saldo Wallet
                                </a>
                                <a href="<?php echo APPURL; ?>categories/index.php" style="display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:10px;background:#f8fafc;color:#555;text-decoration:none;font-size:0.85rem;font-weight:600;border:1px solid #e8e8e8;transition:all .15s;" onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.06)'" onmouseout="this.style.boxShadow=''">
                                    <i data-lucide="store" style="width:18px;height:18px;"></i> Lihat Toko
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
