<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <h3 class="sidebar-brand">
            <i class="fas fa-book-reader"></i>
            Bookstore Admin
        </h3>
    </div>
    
    <div class="sidebar-menu">
        <ul class="menu-list">
            <li class="menu-item">
                <a href="<?php echo APPURL; ?>admin/" class="menu-link active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="<?php echo APPURL; ?>admin/products/" class="menu-link">
                    <i class="fas fa-book"></i>
                    <span>Products</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="<?php echo APPURL; ?>admin/categories/" class="menu-link">
                    <i class="fas fa-tags"></i>
                    <span>Categories</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="<?php echo APPURL; ?>admin/orders/" class="menu-link">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Orders</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="<?php echo APPURL; ?>admin/users/" class="menu-link">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="<?php echo APPURL; ?>admin/analytics/" class="menu-link">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="<?php echo APPURL; ?>admin/settings/" class="menu-link">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            
            <li class="menu-divider"></li>
            
            <li class="menu-item">
                <a href="<?php echo APPURL; ?>" class="menu-link" target="_blank">
                    <i class="fas fa-external-link-alt"></i>
                    <span>View Store</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="<?php echo APPURL; ?>auth/logout.php" class="menu-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
</div>
