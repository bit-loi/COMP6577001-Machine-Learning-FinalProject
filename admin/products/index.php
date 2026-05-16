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
<?php
$pageTitle = 'Products Management';
$pageDescription = 'Manage your product inventory and catalogue';
$topbarAction = '<a href="add.php" class="bg-[#EE4D2D] text-white text-xs font-semibold px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-[#D74226] transition-colors shadow-sm" style="text-decoration:none;">
    <i data-lucide="plus" style="width:16px; height:16px;"></i> Add Product
</a>';
require_once '../includes/header.php';
?>

        <!-- Table Box -->
        <div class="table-card mt-2">
            <div class="flex justify-between items-center mb-6">
                <h5 class="text-sm font-semibold tracking-wide text-gray-800 uppercase">All Products</h5>
                <div class="flex gap-3">
                    <input type="text" class="bg-gray-50 border border-gray-200 text-gray-800 rounded-lg text-xs px-3 py-2 w-64 focus:outline-none focus:border-shopmart-500 transition-colors" placeholder="Search product name...">
                    <select class="bg-gray-50 border border-gray-200 text-gray-800 rounded-lg text-xs px-3 py-2 outline-none focus:border-shopmart-500">
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
                                <td><span class="mono" style="color: #888;">#<?php echo str_pad($product->id, 4, '0', STR_PAD_LEFT); ?></span></td>
                                <td>
                                    <div style="width: 44px; height: 62px; overflow: hidden; border-radius: 8px; border: 1px solid #eee; background: #f9f9f9;">
                                        <img src="https://placehold.co/88x124/FF6B35/FFFFFF?text=<?php echo urlencode(substr($product->name,0,6)); ?>" alt="Product" style="width: 100%; height: 100%; object-fit: cover;">
                                    </div>
                                </td>
                                <td>
                                    <div style="color: #333; font-weight: 600; font-size: 0.85rem; margin-bottom: 2px;"><?php echo htmlspecialchars($product->name); ?></div>
                                    <div style="color: #64748b; font-size: 0.7rem; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($product->description); ?>
                                    </div>
                                </td>
                                <td><span style="color: #64748b; font-size: 0.8rem;"><?php echo htmlspecialchars($product->category_name ?? 'Uncategorized'); ?></span></td>
                                <td><span class="mono" style="color: #EE4D2D; font-weight: 600;">$<?php echo number_format($product->price, 2); ?></span></td>
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
                            <td colspan="8" style="text-align: center; color: #aaa; padding: 60px 0;">
                                <div style="font-size: 0.85rem;">No products found</div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
<?php require_once '../includes/footer.php'; ?>
