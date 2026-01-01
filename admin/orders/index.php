<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/config.php';

// Fetch all orders
try {
    $stmt = $conn->prepare("SELECT o.*, u.username, u.email FROM orders o 
                           LEFT JOIN users u ON o.user_id = u.id 
                           ORDER BY o.created_at DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_OBJ);
} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Bookstore Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/5c5946fe44.js" crossorigin="anonymous"></script>
    <link href="../../assets/css/admin.css" rel="stylesheet">
</head>
<body>
    
    <?php include '../includes/sidebar.php'; ?>
    
    <div class="main-content">
        
        <?php include '../includes/topnav.php'; ?>
        
        <div class="container-fluid px-4 py-4">
            
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-1">Orders Management</h2>
                            <p class="text-muted">Track and manage customer orders</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary">
                                <i class="fas fa-download"></i> Export
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Tabs -->
            <div class="row mb-4">
                <div class="col-12">
                    <ul class="nav nav-pills">
                        <li class="nav-item">
                            <a class="nav-link active" href="#">All Orders</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Pending</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Processing</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Completed</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">Cancelled</a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Orders Table -->
            <div class="row">
                <div class="col-12">
                    <div class="table-card">
                        <div class="table-header">
                            <h5 class="table-title">All Orders</h5>
                            <input type="text" class="form-control form-control-sm" placeholder="Search orders..." style="width: 250px;">
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Date</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($orders): ?>
                                        <?php foreach($orders as $order): ?>
                                            <tr>
                                                <td><strong>#<?php echo $order->order_number ?? str_pad($order->id, 5, '0', STR_PAD_LEFT); ?></strong></td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo $order->username ?? 'Guest'; ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo $order->email ?? ''; ?></small>
                                                    </div>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($order->created_at)); ?></td>
                                                <td>
                                                    <?php
                                                    $items_stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id = ?");
                                                    $items_stmt->execute([$order->id]);
                                                    $item_count = $items_stmt->fetch(PDO::FETCH_OBJ)->count;
                                                    echo $item_count . " item" . ($item_count > 1 ? 's' : '');
                                                    ?>
                                                </td>
                                                <td><strong>$<?php echo number_format($order->total_amount, 2); ?></strong></td>
                                                <td>
                                                    <span class="badge badge-<?php 
                                                        echo $order->payment_status == 'paid' ? 'success' : 
                                                            ($order->payment_status == 'pending' ? 'warning' : 'danger'); 
                                                    ?>">
                                                        <?php echo ucfirst($order->payment_status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <select class="form-select form-select-sm" style="width: 130px;">
                                                        <option <?php echo $order->status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option <?php echo $order->status == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                                        <option <?php echo $order->status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                        <option <?php echo $order->status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="view.php?id=<?php echo $order->id; ?>" class="btn btn-outline-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="invoice.php?id=<?php echo $order->id; ?>" class="btn btn-outline-success" title="Invoice">
                                                            <i class="fas fa-file-invoice"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-5">
                                                <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                                <p>No orders found</p>
                                            </td>
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
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    
</body>
</html>
