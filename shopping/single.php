<?php require_once '../includes/header.php'; ?>
<?php require_once '../config/config.php'; ?>
<?php require_once '../includes/product-image.php'; ?>

<?php
    if(isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                               LEFT JOIN categories c ON p.category_id = c.id 
                               WHERE p.id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_OBJ);
        
        if(!$product) {
            header("Location: " . APPURL);
            exit();
        }

        // Related products
        $stmtRel = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 1 LIMIT 4");
        $stmtRel->execute([$product->category_id, $id]);
        $related = $stmtRel->fetchAll(PDO::FETCH_OBJ);

        // Real reviews
        $stmtRev = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = ? AND status = 1");
        $stmtRev->execute([$id]);
        $reviewData = $stmtRev->fetch(PDO::FETCH_OBJ);
        $avgRating = $reviewData->avg_rating ? round($reviewData->avg_rating, 1) : 0;
        $reviewCount = $reviewData->review_count ?? 0;
    } else {
        header("Location: " . APPURL);
        exit();
    }

    $hasDiscount = isset($product->discount_price) && $product->discount_price > 0;
    $displayPrice = $hasDiscount ? $product->discount_price : $product->price;
    $originalPrice = $product->price;
    $savePct = $hasDiscount ? round((($originalPrice - $displayPrice) / $originalPrice) * 100) : 0;
?>

<style>
    .product-page { background: #f5f5f5; min-height: 60vh; }
    .product-grid { max-width: 1200px; margin: 0 auto; padding: 32px 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 48px; align-items: start; }
    .product-image-wrap { background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #f1f5f9; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; padding: 16px; }
    .product-image-wrap img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; }
    .product-info { padding: 8px 0; }
    .product-brand { font-size: 0.8rem; color: #FF6B35; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
    .product-title { font-size: 1.6rem; font-weight: 800; color: #222; margin: 0 0 12px; line-height: 1.3; }
    .product-rating { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
    .product-price { font-size: 2rem; font-weight: 800; color: #EE4D2D; margin-bottom: 4px; }
    .product-original-price { font-size: 1rem; color: #bbb; text-decoration: line-through; }
    .product-save-badge { font-size: 0.75rem; font-weight: 700; background: #FEE2E2; color: #EE4D2D; padding: 4px 10px; border-radius: 6px; }
    .btn-add-cart { display: block; width: 100%; padding: 16px; background: #FF6B35; color: white; font-size: 0.9rem; font-weight: 700; border: none; border-radius: 12px; cursor: pointer; transition: background 0.2s; text-align: center; }
    .btn-add-cart:hover { background: #EE4D2D; }
    .product-meta { border-top: 1px solid #eee; padding-top: 20px; margin-top: 20px; }
    .meta-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f5f5f5; font-size: 0.85rem; }
    .meta-label { color: #999; }
    .meta-value { color: #333; font-weight: 500; }
    .related-section { max-width: 1200px; margin: 0 auto; padding: 0 24px 64px; }
    .related-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
    @media (min-width: 640px) { .related-grid { grid-template-columns: repeat(3, 1fr); gap: 12px; } }
    @media (min-width: 1024px) { .related-grid { grid-template-columns: repeat(5, 1fr); gap: 16px; } }
    .related-card { background: #fff; border-radius: 4px; overflow: hidden; text-decoration: none; transition: transform 0.2s ease, box-shadow 0.2s ease; display: flex; flex-direction: column; height: 100%; border: 1px solid #f0f0f0; }
    .related-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-color: #EE4D2D; z-index: 2; }
    .related-img-wrap { position: relative; aspect-ratio: 1; background: #f8fafc; display: flex; align-items: center; justify-content: center; padding: 4px; }
    .related-img-wrap img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; }
    .related-info { padding: 10px; display: flex; flex-direction: column; flex-grow: 1; }
    .related-title { font-size: 0.85rem; color: #222; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 8px; min-height: 38px; }
    .related-price { font-size: 1.1rem; font-weight: 700; color: #EE4D2D; }
    @media (max-width: 768px) { .product-grid { grid-template-columns: 1fr; gap: 24px; } }
</style>

<div class="product-page">
    <!-- Breadcrumb -->
    <div style="max-width: 1200px; margin: 0 auto; padding: 20px 24px 0;">
        <nav style="font-size: 0.8rem; color: #999;">
            <a href="<?php echo APPURL; ?>" style="color: #999; text-decoration: none;">Home</a>
            <span style="margin: 0 8px;">/</span>
            <a href="<?php echo APPURL; ?>categories/index.php" style="color: #999; text-decoration: none;">Categories</a>
            <?php if($product->category_name): ?>
                <span style="margin: 0 8px;">/</span>
                <span style="color: #555;"><?php echo htmlspecialchars($product->category_name); ?></span>
            <?php endif; ?>
        </nav>
    </div>

    <div class="product-grid">
        <!-- Image -->
        <div class="product-image-wrap">
            <?php echo getProductImage($product, '600x600', '', ['style' => 'width:100%; height:100%; object-fit:cover;']); ?>
        </div>

        <!-- Info -->
        <div class="product-info">
            <?php if($product->brand): ?>
                <div class="product-brand"><?php echo htmlspecialchars($product->brand); ?></div>
            <?php endif; ?>
            
            <h1 class="product-title"><?php echo htmlspecialchars($product->name); ?></h1>

            <!-- Rating -->
            <div class="product-rating">
                <?php if($reviewCount > 0): ?>
                    <span style="color: #EAB308;">★</span>
                    <span style="font-size: 0.9rem; font-weight: 600; color: #333;"><?php echo $avgRating; ?></span>
                    <span style="font-size: 0.8rem; color: #999;">(<?php echo $reviewCount; ?> reviews)</span>
                <?php else: ?>
                    <span style="font-size: 0.8rem; color: #aaa;">No reviews yet</span>
                <?php endif; ?>
                <span style="color: #ddd;">•</span>
                <span style="font-size: 0.8rem; color: <?php echo $product->stock > 0 ? '#059669' : '#DC2626'; ?>; font-weight: 500;">
                    <?php echo $product->stock > 0 ? $product->stock . ' in stock' : 'Out of stock'; ?>
                </span>
            </div>

            <!-- Price -->
            <div class="product-price">$<?php echo number_format($displayPrice, 2); ?></div>
            <?php if($hasDiscount): ?>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
                    <span class="product-original-price">$<?php echo number_format($originalPrice, 2); ?></span>
                    <span class="product-save-badge">SAVE <?php echo $savePct; ?>%</span>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 20px;"></div>
            <?php endif; ?>

            <!-- Description -->
            <?php if($product->description): ?>
                <p style="font-size: 0.9rem; color: #666; line-height: 1.8; margin-bottom: 24px;"><?php echo nl2br(htmlspecialchars($product->description)); ?></p>
            <?php endif; ?>

            <?php if($product->stock > 0): ?>
                <!-- Quantity -->
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: #555;">Quantity:</span>
                    <div style="display: flex; align-items: center; border: 1px solid #ddd; border-radius: 10px; overflow: hidden;">
                        <button onclick="adjustQty(-1)" style="width: 40px; height: 40px; background: #f5f5f5; border: none; cursor: pointer; font-size: 16px; color: #555;">−</button>
                        <input id="sp-qty-val" type="number" value="1" min="1" max="<?php echo $product->stock; ?>" readonly style="width: 50px; text-align: center; border: none; font-size: 0.9rem; font-weight: 600; background: transparent; outline: none;">
                        <button onclick="adjustQty(1)" style="width: 40px; height: 40px; background: #f5f5f5; border: none; cursor: pointer; font-size: 16px; color: #555;">+</button>
                    </div>
                </div>

                <form method="POST" action="<?php echo APPURL; ?>shopping/cart.php">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
                    <input type="hidden" name="quantity" id="form-qty" value="1">
                    <button type="submit" name="add_to_cart" class="btn-add-cart">
                        <i data-lucide="shopping-cart" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:8px;"></i>Add to Cart
                    </button>
                </form>
            <?php else: ?>
                <button class="btn-add-cart" disabled style="opacity: 0.4; cursor: not-allowed;">Out of Stock</button>
            <?php endif; ?>

            <!-- Product Details -->
            <div class="product-meta">
                <h3 style="font-size: 0.9rem; font-weight: 700; color: #333; margin: 0 0 12px;">Product Details</h3>
                <?php if($product->sku): ?>
                    <div class="meta-row"><span class="meta-label">SKU</span><span class="meta-value"><?php echo htmlspecialchars($product->sku); ?></span></div>
                <?php endif; ?>
                <?php if($product->brand): ?>
                    <div class="meta-row"><span class="meta-label">Brand</span><span class="meta-value"><?php echo htmlspecialchars($product->brand); ?></span></div>
                <?php endif; ?>
                <?php if($product->supplier): ?>
                    <div class="meta-row"><span class="meta-label">Supplier</span><span class="meta-value"><?php echo htmlspecialchars($product->supplier); ?></span></div>
                <?php endif; ?>
                <?php if($product->category_name): ?>
                    <div class="meta-row"><span class="meta-label">Category</span><span class="meta-value"><?php echo htmlspecialchars($product->category_name); ?></span></div>
                <?php endif; ?>
                <div class="meta-row"><span class="meta-label">Availability</span><span class="meta-value"><?php echo $product->stock; ?> units</span></div>
            </div>

            <!-- Guarantees -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 24px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #888;"><i data-lucide="truck" style="width:14px;height:14px;color:#FF6B35;"></i> Free shipping $50+</div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #888;"><i data-lucide="rotate-ccw" style="width:14px;height:14px;color:#FF6B35;"></i> 30-day returns</div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #888;"><i data-lucide="shield-check" style="width:14px;height:14px;color:#FF6B35;"></i> Buyer protection</div>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 0.8rem; color: #888;"><i data-lucide="headphones" style="width:14px;height:14px;color:#FF6B35;"></i> 24/7 support</div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if($related): ?>
    <div class="related-section">
        <h2 style="font-size: 1.3rem; font-weight: 800; color: #222; margin-bottom: 20px;">You May Also Like</h2>
        <div class="related-grid">
            <?php foreach($related as $rel): 
                $hasDiscount = !empty($rel->discount_price) && $rel->discount_price > 0;
                $displayPrice = $hasDiscount ? $rel->discount_price : $rel->price;
                $savePct = $hasDiscount ? round((($rel->price - $displayPrice) / $rel->price) * 100) : 0;
            ?>
            <a href="single.php?id=<?php echo $rel->id; ?>" class="related-card">
                <div class="related-img-wrap">
                    <?php echo getProductImage($rel, '300x300'); ?>
                    <?php if($hasDiscount): ?>
                        <span style="position: absolute; top: 0; right: 0; background: #EE4D2D; color: white; font-size: 0.7rem; font-weight: 700; padding: 3px 6px; border-bottom-left-radius: 4px;">-<?php echo $savePct; ?>%</span>
                    <?php endif; ?>
                </div>
                <div class="related-info">
                    <?php if($rel->brand): ?>
                        <div style="font-size: 10px; color: #aaa; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;"><?php echo htmlspecialchars($rel->brand); ?></div>
                    <?php endif; ?>
                    <div class="related-title"><?php echo htmlspecialchars($rel->name); ?></div>
                    <div style="margin-top: auto;">
                        <span class="related-price">$<?php echo number_format($displayPrice, 2); ?></span>
                        <?php if($hasDiscount): ?>
                            <span style="font-size: 0.75rem; color: #999; text-decoration: line-through; margin-left: 4px;">$<?php echo number_format($rel->price, 2); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function adjustQty(delta) {
    const input = document.getElementById('sp-qty-val');
    const formQty = document.getElementById('form-qty');
    if (!input) return;
    let val = parseInt(input.value) + delta;
    const max = parseInt(input.max) || 99;
    val = Math.max(1, Math.min(max, val));
    input.value = val;
    if (formQty) formQty.value = val;
}
</script>

<?php require '../includes/footer.php'; ?>
