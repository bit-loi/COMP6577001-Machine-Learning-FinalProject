<?php
session_start();
require '../config/config.php';
require '../middleware/auth.php';

// Ensure user is a seller
$stmt = $conn->prepare("SELECT is_seller FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    header("Location: ../auth/login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 1);
    $category_id = intval($_POST['category_id'] ?? 0);
    $seller_id = $_SESSION['user_id'];
    
    // Simple slug generator
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    // Ensure unique slug
    $slug .= '-' . uniqid();

    if (empty($name) || $price <= 0 || $category_id <= 0) {
        $error = "Name, valid price, and category are required.";
    } else {
        // Image upload handling
        $imagePath = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../assets/images/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileExt = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($fileExt, $allowedExts)) {
                $newFileName = uniqid('prod_') . '.' . $fileExt;
                $dest = $uploadDir . $newFileName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $imagePath = $newFileName;
                } else {
                    $error = "Failed to upload image.";
                }
            } else {
                $error = "Invalid image format. Only JPG, PNG, WEBP allowed.";
            }
        } else {
            // Default placeholder if no image
            $imagePath = 'placeholder.jpg';
        }

        if (!$error) {
            try {
                $stmt = $conn->prepare("INSERT INTO products (name, slug, description, price, stock, category_id, seller_id, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
                $stmt->execute([$name, $slug, $description, $price, $stock, $category_id, $seller_id, $imagePath]);
                $success = "Product successfully listed!";
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch categories for dropdown
$stmtCat = $conn->query("SELECT id, name FROM categories WHERE status = 1");
$categories = $stmtCat->fetchAll(PDO::FETCH_OBJ);

$pageTitle = 'Add New Product';
include '../includes/header.php';
?>

<div style="background: #f5f5f5; min-height: 80vh; padding: 40px 0;">
    <div class="max-w-4xl mx-auto px-6 lg:px-12">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
            <h1 style="font-size: 1.5rem; font-weight: 800; color: #222;">Add New Product</h1>
            <a href="index.php" style="color: #64748b; font-size: 0.9rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Back to Dashboard
            </a>
        </div>

        <div style="background: #fff; border-radius: 16px; padding: 32px; border: 1px solid #eee;">
            <?php if($error): ?>
                <div style="background: #fef2f2; color: #ef4444; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-size: 0.9rem; font-weight: 600;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div style="background: #f0fdf4; color: #16a34a; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; font-size: 0.9rem; font-weight: 600;">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Product Name *</label>
                    <input type="text" name="name" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.95rem;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Price ($) *</label>
                        <input type="number" step="0.01" name="price" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.95rem;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Stock Quantity</label>
                        <input type="number" name="stock" value="1" min="1" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.95rem;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Category *</label>
                    <select name="category_id" required style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.95rem; background: #fff;">
                        <option value="">Select a category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat->id; ?>"><?php echo htmlspecialchars($cat->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Product Description</label>
                    <textarea name="description" rows="4" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.95rem;"></textarea>
                </div>

                <div style="margin-bottom: 32px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">Product Image</label>
                    <input type="file" name="image" accept="image/jpeg, image/png, image/webp" style="width: 100%; padding: 10px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; font-size: 0.9rem;">
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 6px;">Recommended: Square image (1:1 aspect ratio), Max 2MB.</div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" style="background: #EE4D2D; color: #fff; border: none; padding: 14px 32px; border-radius: 10px; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#D74226'" onmouseout="this.style.background='#EE4D2D'">
                        List Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
