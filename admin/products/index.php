<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/config.php';

// Fetch all products
try {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           ORDER BY p.created_at DESC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $products = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products — Premeditatio Malorum</title>
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
        .badge { display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; }
        
        .badge-success { background: rgba(74,222,128,0.1); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
        .badge-warning { background: rgba(251,191,36,0.1); color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
        .badge-danger { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
        
        .action-icon { color: rgba(255,255,255,0.4); transition: color 0.2s; display: inline-block; margin: 0 4px; padding: 6px; }
        .action-icon:hover { color: white; }

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
        <a href="<?php echo APPURL; ?>admin/orders/" class="nav-item">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            Orders
        </a>

        <div class="nav-section">Catalogue</div>
        <a href="<?php echo APPURL; ?>admin/products/" class="nav-item active">
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
            <div style="font-size: 1rem; font-weight: 600; color: white;">Products Management</div>
            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.3); margin-top: 1px;">Manage your book inventory and catalogue</div>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <a href="add.php" class="bg-white text-black text-xs font-semibold uppercase tracking-widest px-4 py-2 flex items-center gap-2 hover:bg-gray-200 transition-colors cursor-pointer" style="text-decoration:none;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-3 h-3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add Product
            </a>
            <div style="display: flex; align-items: center; gap: 10px; margin-left: 8px;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: white;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content space-y-6">

        <!-- Table Box -->
        <div class="table-card mt-2">
            <div class="flex justify-between items-center mb-6">
                <h5 class="text-sm font-semibold tracking-wide text-white/80 uppercase">All Products</h5>
                <div class="flex gap-3">
                    <input type="text" class="bg-white/5 border border-white/10 text-white text-xs px-3 py-2 w-64 focus:outline-none focus:border-white/30 transition-colors" placeholder="Search product name...">
                    <select class="bg-white/5 border border-white/10 text-white/70 text-xs px-3 py-2 outline-none">
                        <option>All Categories</option>
                        <option>Fiction</option>
                        <option>Non-Fiction</option>
                        <option>Science</option>
                    </select>
                </div>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 80px;">Cover</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($products): ?>
                        <?php foreach($products as $product): ?>
                            <tr>
                                <td><span class="mono" style="color: rgba(255,255,255,0.5);">#<?php echo str_pad($product->id, 4, '0', STR_PAD_LEFT); ?></span></td>
                                <td>
                                    <div style="width: 44px; height: 62px; overflow: hidden; border-radius: 4px; border: 1px solid rgba(255,255,255,0.1);">
                                        <img src="../../images/<?php echo $product->image; ?>" alt="Cover" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.9;">
                                    </div>
                                </td>
                                <td>
                                    <div style="color: white; font-weight: 600; font-size: 0.85rem; margin-bottom: 2px;" class="serif"><?php echo htmlspecialchars($product->name); ?></div>
                                    <div style="color: rgba(255,255,255,0.4); font-size: 0.7rem; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.4;">
                                        <?php echo htmlspecialchars($product->description); ?>
                                    </div>
                                </td>
                                <td><span style="color: rgba(255,255,255,0.6); font-size: 0.8rem;"><?php echo htmlspecialchars($product->category_name ?? 'Uncategorized'); ?></span></td>
                                <td><span class="mono" style="color: white; font-weight: 600;">$<?php echo number_format($product->price, 2); ?></span></td>
                                <td>
                                    <?php if($product->stock > 10): ?>
                                        <span class="badge badge-success"><?php echo $product->stock; ?> in stock</span>
                                    <?php elseif($product->stock > 0): ?>
                                        <span class="badge badge-warning"><?php echo $product->stock; ?> low stock</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Out of stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if(isset($product->status) && $product->status == 1): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right">
                                    <a href="edit.php?id=<?php echo $product->id; ?>" class="action-icon" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                    </a>
                                    <a href="delete.php?id=<?php echo $product->id; ?>" onclick="return confirm('Are you sure you want to delete this product?')" class="action-icon text-red-500 hover:text-red-400" title="Delete">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="w-4 h-4"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: rgba(255,255,255,0.2); padding: 60px 0;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin: 0 auto 12px; display: block;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                                <div class="text-xs uppercase tracking-widest">No products found</div>
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
