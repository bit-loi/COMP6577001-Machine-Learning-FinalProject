<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-logo">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            <span style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 0.85rem; font-weight: 700; color: white;">Premeditatio Malorum</span>
        </div>
        <div style="font-size: 0.65rem; color: rgba(255,255,255,0.2); letter-spacing: 0.1em; text-transform: uppercase; margin-top: 4px;">Admin Console</div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Overview</div>
        <a href="<?php echo APPURL; ?>admin/" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && strpos($_SERVER['REQUEST_URI'], '/admin/') !== false ? 'active' : ''; ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="<?php echo APPURL; ?>admin/orders/" class="nav-item">
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


        <div style="padding-top: 32px; border-top: 1px solid rgba(255,255,255,0.06); margin-top: 32px;">
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
</div>
