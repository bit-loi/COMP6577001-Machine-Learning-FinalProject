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
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - Bookstore Admin</title>
    
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
                            <h2 class="mb-1">Products Management</h2>
                            <p class="text-muted">Manage your book inventory</p>
                        </div>
                        <a href="add.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Product
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="row">
                <div class="col-12">
                    <div class="table-card">
                        <div class="table-header">
                            <h5 class="table-title">All Products</h5>
                            <div class="d-flex gap-2">
                                <input type="text" class="form-control form-control-sm" placeholder="Search products..." style="width: 250px;">
                                <select class="form-select form-select-sm" style="width: 150px;">
                                    <option>All Categories</option>
                                    <option>Fiction</option>
                                    <option>Non-Fiction</option>
                                    <option>Science</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($products): ?>
                                        <?php foreach($products as $product): ?>
                                            <tr>
                                                <td><strong>#<?php echo str_pad($product->id, 4, '0', STR_PAD_LEFT); ?></strong></td>
                                                <td>
                                                    <img src="../../images/<?php echo $product->image; ?>" 
                                                         alt="<?php echo $product->name; ?>" 
                                                         style="width: 50px; height: 70px; object-fit: cover; border-radius: 8px;">
                                                </td>
                                                <td>
                                                    <strong><?php echo $product->name; ?></strong>
                                                    <br>
                                                    <small class="text-muted"><?php echo substr($product->description, 0, 50); ?>...</small>
                                                </td>
                                                <td><?php echo $product->category_name ?? 'Uncategorized'; ?></td>
                                                <td><strong>$<?php echo number_format($product->price, 2); ?></strong></td>
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
                                                    <?php if($product->status == 1): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="edit.php?id=<?php echo $product->id; ?>" class="btn btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="delete.php?id=<?php echo $product->id; ?>" class="btn btn-outline-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this product?')" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-5">
                                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                                <p>No products found. Add your first product!</p>
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
