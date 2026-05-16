<!-- Admin Sidebar — Shopmart -->
<div class="sidebar">
    <div class="sidebar-logo">
        <div style="display: flex; align-items: center; margin-bottom: 4px; margin-left: 8px;">
            <img src="<?php echo APPURL; ?>assets/header_logo.png" alt="Shopmart" style="height: 56px; width: auto; object-fit: contain;">
        </div>
        <div style="font-size: 0.7rem; color: #aaa; font-weight: 500;">Admin Dashboard</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Overview</div>
        <a href="<?php echo APPURL; ?>admin/" class="nav-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
        <a href="<?php echo APPURL; ?>admin/orders/" class="nav-item"><i data-lucide="shopping-bag"></i> Orders</a>
        <div class="nav-section">Catalogue</div>
        <a href="<?php echo APPURL; ?>admin/products/" class="nav-item"><i data-lucide="package"></i> Products</a>
        <a href="<?php echo APPURL; ?>admin/categories/" class="nav-item"><i data-lucide="grid-3x3"></i> Categories</a>
        <div class="nav-section">People</div>
        <a href="<?php echo APPURL; ?>admin/customers/" class="nav-item"><i data-lucide="users"></i> Customers</a>
        <div class="nav-section">Intelligence</div>
        <a href="<?php echo APPURL; ?>admin/simulation.php" class="nav-item"><i data-lucide="brain-circuit"></i> ML Simulation</a>
        <div style="padding-top: 24px; border-top: 1px solid #eee; margin-top: 24px;">
            <a href="<?php echo APPURL; ?>" target="_blank" class="nav-item"><i data-lucide="external-link"></i> View Store</a>
            <a href="<?php echo APPURL; ?>auth/logout.php" class="nav-item" style="color: #ef4444;"><i data-lucide="log-out"></i> Logout</a>
        </div>
    </nav>
</div>
