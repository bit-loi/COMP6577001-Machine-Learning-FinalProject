<!-- Top Navigation Bar -->
<nav class="topnav">
    <div class="topnav-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" placeholder="Search products, orders, customers...">
        </div>
    </div>
    
    <div class="topnav-right">
        <!-- Notifications -->
        <div class="topnav-item dropdown">
            <a href="#" class="topnav-link" data-bs-toggle="dropdown">
                <i class="fas fa-bell"></i>
                <span class="badge badge-danger">5</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end notification-dropdown">
                <div class="dropdown-header">
                    <h6>Notifications</h6>
                    <a href="#">Mark all as read</a>
                </div>
                <div class="notification-list">
                    <a href="#" class="notification-item unread">
                        <div class="notification-icon bg-primary">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">New order received</p>
                            <span class="notification-time">5 minutes ago</span>
                        </div>
                    </a>
                    <a href="#" class="notification-item">
                        <div class="notification-icon bg-success">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">New customer registered</p>
                            <span class="notification-time">1 hour ago</span>
                        </div>
                    </a>
                    <a href="#" class="notification-item">
                        <div class="notification-icon bg-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="notification-content">
                            <p class="notification-text">Low stock alert</p>
                            <span class="notification-time">2 hours ago</span>
                        </div>
                    </a>
                </div>
                <div class="dropdown-footer">
                    <a href="#">View all notifications</a>
                </div>
            </div>
        </div>
        
        <!-- Messages -->
        <div class="topnav-item">
            <a href="#" class="topnav-link">
                <i class="fas fa-envelope"></i>
                <span class="badge badge-success">3</span>
            </a>
        </div>
        
        <!-- User Profile -->
        <div class="topnav-item dropdown">
            <a href="#" class="topnav-link user-link" data-bs-toggle="dropdown">
                <div class="user-avatar">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username']); ?>&background=667eea&color=fff" alt="Avatar">
                </div>
                <span class="user-name"><?php echo $_SESSION['username']; ?></span>
                <i class="fas fa-chevron-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
                <div class="dropdown-header">
                    <h6><?php echo $_SESSION['username']; ?></h6>
                    <p>Administrator</p>
                </div>
                <a class="dropdown-item" href="<?php echo APPURL; ?>admin/profile/">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a class="dropdown-item" href="<?php echo APPURL; ?>admin/settings/">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="<?php echo APPURL; ?>auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<script>
    // Sidebar toggle functionality
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('collapsed');
        document.querySelector('.main-content').classList.toggle('expanded');
    });
</script>
