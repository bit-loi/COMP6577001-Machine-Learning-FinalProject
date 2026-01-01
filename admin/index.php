<?php
session_start();

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/config.php';

// Fetch statistics
try {
    // Total products
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
    $stmt->execute();
    $totalProducts = $stmt->fetch(PDO::FETCH_OBJ)->total;
    
    // Total users
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE is_admin = 0");
    $stmt->execute();
    $totalUsers = $stmt->fetch(PDO::FETCH_OBJ)->total;
    
    // Total orders
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders");
    $stmt->execute();
    $totalOrders = $stmt->fetch(PDO::FETCH_OBJ)->total;
    
    // Total revenue
    $stmt = $conn->prepare("SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'completed'");
    $stmt->execute();
    $totalRevenue = $stmt->fetch(PDO::FETCH_OBJ)->revenue ?? 0;
    
    // Recent orders
    $stmt = $conn->prepare("SELECT o.*, u.username FROM orders o 
                           LEFT JOIN users u ON o.user_id = u.id 
                           ORDER BY o.created_at DESC LIMIT 5");
    $stmt->execute();
    $recentOrders = $stmt->fetchAll(PDO::FETCH_OBJ);
    
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Bookstore</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/5c5946fe44.js" crossorigin="anonymous"></script>
    <!-- Custom CSS -->
    <link href="../assets/css/admin.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    <!-- Sidebar -->
    <?php include 'includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Top Navigation -->
        <?php include 'includes/topnav.php'; ?>
        
        <!-- Dashboard Content -->
        <div class="container-fluid px-4 py-4">
            
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="welcome-card">
                        <h1 class="mb-2">Welcome back, <?php echo $_SESSION['username']; ?>! 👋</h1>
                        <p class="text-muted">Here's what's happening with your store today.</p>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                
                <!-- Total Products -->
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card stats-card-primary">
                        <div class="stats-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number"><?php echo $totalProducts; ?></h3>
                            <p class="stats-label">Total Products</p>
                        </div>
                        <div class="stats-trend">
                            <i class="fas fa-arrow-up"></i> 12% from last month
                        </div>
                    </div>
                </div>
                
                <!-- Total Users -->
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card stats-card-success">
                        <div class="stats-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number"><?php echo $totalUsers; ?></h3>
                            <p class="stats-label">Total Customers</p>
                        </div>
                        <div class="stats-trend">
                            <i class="fas fa-arrow-up"></i> 8% from last month
                        </div>
                    </div>
                </div>
                
                <!-- Total Orders -->
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card stats-card-warning">
                        <div class="stats-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number"><?php echo $totalOrders; ?></h3>
                            <p class="stats-label">Total Orders</p>
                        </div>
                        <div class="stats-trend">
                            <i class="fas fa-arrow-up"></i> 23% from last month
                        </div>
                    </div>
                </div>
                
                <!-- Total Revenue -->
                <div class="col-xl-3 col-md-6">
                    <div class="stats-card stats-card-danger">
                        <div class="stats-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stats-content">
                            <h3 class="stats-number">$<?php echo number_format($totalRevenue, 2); ?></h3>
                            <p class="stats-label">Total Revenue</p>
                        </div>
                        <div class="stats-trend">
                            <i class="fas fa-arrow-up"></i> 15% from last month
                        </div>
                    </div>
                </div>
                
            </div>
            
            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                
                <!-- Sales Chart -->
                <div class="col-xl-8">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h5 class="chart-title">Sales Overview</h5>
                            <div class="chart-actions">
                                <button class="btn btn-sm btn-outline-primary">This Month</button>
                            </div>
                        </div>
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                
                <!-- Top Products Chart -->
                <div class="col-xl-4">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h5 class="chart-title">Top Categories</h5>
                        </div>
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
                
            </div>
            
            <!-- Recent Orders Table -->
            <div class="row">
                <div class="col-12">
                    <div class="table-card">
                        <div class="table-header">
                            <h5 class="table-title">Recent Orders</h5>
                            <a href="orders/" class="btn btn-primary btn-sm">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($recentOrders): ?>
                                        <?php foreach($recentOrders as $order): ?>
                                            <tr>
                                                <td><strong>#<?php echo str_pad($order->id, 5, '0', STR_PAD_LEFT); ?></strong></td>
                                                <td><?php echo $order->username ?? 'Guest'; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($order->created_at)); ?></td>
                                                <td><strong>$<?php echo number_format($order->total_amount, 2); ?></strong></td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                        echo $order->status == 'completed' ? 'success' : 
                                                            ($order->status == 'pending' ? 'warning' : 'info'); 
                                                    ?>">
                                                        <?php echo ucfirst($order->status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="orders/view.php?id=<?php echo $order->id; ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No orders yet</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    
    <!-- Custom Charts Script -->
    <script>
        // Sales Chart
        const salesCtx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Sales',
                    data: [1200, 1900, 1500, 2100, 2400, 2800, 2600, 3200, 2900, 3400, 3800, 4200],
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
        
        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['Fiction', 'Non-Fiction', 'Science', 'History', 'Children'],
                datasets: [{
                    data: [30, 25, 20, 15, 10],
                    backgroundColor: [
                        '#667eea',
                        '#f5576c',
                        '#4facfe',
                        '#f7b731',
                        '#5f27cd'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
    
</body>
</html>
