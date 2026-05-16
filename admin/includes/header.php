<?php
if (!defined('APPURL')) {
    define('APPURL', 'http://localhost/shopmart/');
}
require_once __DIR__ . '/auth.php';

$pageTitle = $pageTitle ?? 'Admin Dashboard';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$activeMenu = 'dashboard';

if (strpos($currentPath, '/orders') !== false) $activeMenu = 'orders';
elseif (strpos($currentPath, '/products') !== false) $activeMenu = 'products';
elseif (strpos($currentPath, '/categories') !== false) $activeMenu = 'categories';
elseif (strpos($currentPath, '/customers') !== false) $activeMenu = 'customers';
elseif (strpos($currentPath, '/churn') !== false) $activeMenu = 'churn';
elseif (strpos($currentPath, '/simulation.php') !== false) $activeMenu = 'simulation';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> — Shopmart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.344.0/umd/lucide.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #333; margin: 0; display: flex; min-height: 100vh; }
        .mono { font-family: monospace; }

        /* Sidebar */
        .sidebar { width: 260px; min-height: 100vh; background: #fff; border-right: 1px solid #f1f5f9; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; z-index: 50; }
        .sidebar-logo { padding: 24px; border-bottom: 1px solid #f1f5f9; }
        .sidebar-nav { flex: 1; padding: 16px 12px; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 11px 14px; border-radius: 10px; color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s ease; margin-bottom: 4px; }
        .nav-item:hover { background: #FFF4ED; color: #EE4D2D; }
        .nav-item.active { background: #FFF4ED; color: #EE4D2D; font-weight: 700; }
        .nav-item i, .nav-item svg { width: 18px; height: 18px; flex-shrink: 0; }
        .nav-section { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #cbd5e1; padding: 20px 14px 8px; }

        /* Main content */
        .main { margin-left: 260px; flex: 1; min-height: 100vh; display: flex; flex-direction: column; }
        .topbar { height: 72px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; padding: 0 32px; background: #fff; position: sticky; top: 0; z-index: 40; }
        .content { padding: 32px; flex: 1; display: flex; flex-direction: column; gap: 24px; }
        
        /* Table Box */
        .table-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 16px; padding: 24px; width: 100%; overflow-x: auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); }
        .data-table { width: 100%; border-collapse: collapse; min-width: 800px; }
        .data-table th { font-size: 0.75rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; color: #94a3b8; padding: 16px; text-align: left; border-bottom: 1px solid #f1f5f9; }
        .data-table td { padding: 16px; font-size: 0.875rem; color: #475569; border-bottom: 1px solid #f8fafc; vertical-align: middle; }
        .data-table tr:hover td { background: #f8fafc; }
        .data-table tr:last-child td { border-bottom: none; }

        /* Badges */
        .badge { display: inline-flex; align-items: center; justify-content: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.02em; text-transform: uppercase; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #e0f2fe; color: #075985; }

        /* Action Icons */
        .action-icon { color: #94a3b8; transition: color 0.2s; display: inline-flex; padding: 6px; border-radius: 6px; }
        .action-icon:hover { color: #EE4D2D; background: #FFF4ED; }

        /* Grid Layout */
        .grid-layout { display: grid; grid-template-columns: 1fr 340px; gap: 32px; align-items: stretch; }
        @media (max-width: 1200px) {
            .grid-layout { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
            <i data-lucide="shopping-bag" style="width:24px; height:24px; color: #EE4D2D;"></i>
            <span style="font-size: 1.25rem; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Shopmart</span>
        </div>
        <div style="font-size: 0.75rem; color: #64748b; font-weight: 500;">Admin Dashboard</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Overview</div>
        <a href="<?php echo APPURL; ?>admin/" class="nav-item <?php echo $activeMenu == 'dashboard' ? 'active' : ''; ?>"><i data-lucide="layout-dashboard"></i> Dashboard</a>
        <a href="<?php echo APPURL; ?>admin/orders/" class="nav-item <?php echo $activeMenu == 'orders' ? 'active' : ''; ?>"><i data-lucide="shopping-cart"></i> Orders</a>
        
        <div class="nav-section">Catalogue</div>
        <a href="<?php echo APPURL; ?>admin/products/" class="nav-item <?php echo $activeMenu == 'products' ? 'active' : ''; ?>"><i data-lucide="package"></i> Products</a>
        <a href="<?php echo APPURL; ?>admin/categories/" class="nav-item <?php echo $activeMenu == 'categories' ? 'active' : ''; ?>"><i data-lucide="grid-3x3"></i> Categories</a>
        
        <div class="nav-section">People</div>
        <a href="<?php echo APPURL; ?>admin/customers/" class="nav-item <?php echo $activeMenu == 'customers' ? 'active' : ''; ?>"><i data-lucide="users"></i> Customers</a>
        
        <div class="nav-section">Intelligence</div>
        <a href="<?php echo APPURL; ?>admin/churn/" class="nav-item <?php echo $activeMenu == 'churn' ? 'active' : ''; ?>">
            <i data-lucide="user-minus"></i>
            Churn Prediction
        </a>
        <a href="<?php echo APPURL; ?>admin/simulation.php" class="nav-item <?php echo $activeMenu == 'simulation' ? 'active' : ''; ?>"><i data-lucide="brain-circuit"></i> ML Simulation</a>

        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #f1f5f9;">
            <a href="<?php echo APPURL; ?>" target="_blank" class="nav-item"><i data-lucide="external-link"></i> View Store</a>
            <a href="<?php echo APPURL; ?>auth/logout.php" class="nav-item" style="color: #ef4444;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''"><i data-lucide="log-out"></i> Logout</a>
        </div>
    </nav>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div>
            <div style="font-size: 1.125rem; font-weight: 700; color: #0f172a;"><?php echo htmlspecialchars($pageTitle); ?></div>
            <?php if (isset($pageDescription)): ?>
                <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;"><?php echo htmlspecialchars($pageDescription); ?></div>
            <?php endif; ?>
        </div>
        <div style="display: flex; align-items: center; gap: 16px;">
            <?php if (isset($topbarAction)): ?>
                <?php echo $topbarAction; ?>
            <?php endif; ?>
            <?php if (isset($topbarAction)): ?>
            <div style="width: 1px; height: 24px; background: #e2e8f0; margin: 0 4px;"></div>
            <?php endif; ?>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #EE4D2D; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; font-weight: 700; color: white;">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #0f172a;"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                    <div style="font-size: 0.7rem; color: #64748b;">Administrator</div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="content">
