<?php require_once '../includes/header.php'; ?>
<?php require_once '../config/config.php'; ?>

<?php
    $categories = $conn->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.status = 1 GROUP BY c.id ORDER BY c.name ASC")->fetchAll(PDO::FETCH_OBJ);

    $catIcons = [
      'electronics'      => ['svg'=>'<path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3m-1 8a2 2 0 1 0 4 0 2 2 0 0 0-4 0M9 21h6m-3-4v4"/>','bg'=>'#EFF6FF','color'=>'#1D4ED8'],
      'fashion'          => ['svg'=>'<path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.57a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.57a2 2 0 0 0-1.34-2.23z"/>','bg'=>'#FDF2F8','color'=>'#9D174D'],
      'home-living'      => ['svg'=>'<path d="M20 9v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V9m18 0H2m18 0-1.995-7.183A2 2 0 0 0 16.054 0H7.946a2 2 0 0 0-1.951 1.817L4 9"/>','bg'=>'#F0FDF4','color'=>'#166534'],
      'beauty'           => ['svg'=>'<path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3z"/><path d="M5 3 4 6l-3 1 3 1 1 3 1-3 3-1-3-1L5 3z"/><path d="M19 12l-0.5 1.5L17 14l1.5.5.5 1.5.5-1.5L21 14l-1.5-.5L19 12z"/>','bg'=>'#FFF1F2','color'=>'#BE123C'],
      'sports'           => ['svg'=>'<path d="M6.5 6.5 17.5 17.5M6.5 17.5l5.774-5.774M17.5 6.5l-5.774 5.774M22 12c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10z"/>','bg'=>'#FFF7ED','color'=>'#C2410C'],
      'books-stationery' => ['svg'=>'<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2zm20 0h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>','bg'=>'#FEFCE8','color'=>'#92400E'],
      'groceries'        => ['svg'=>'<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4zm1 4h10M12 11v6m-3-3h6"/>','bg'=>'#F0FDF4','color'=>'#15803D'],
      'toys-games'       => ['svg'=>'<line x1="6" y1="11" x2="10" y2="11"/><line x1="8" y1="9" x2="8" y2="13"/><line x1="15" y1="12" x2="15.01" y2="12"/><line x1="18" y1="10" x2="18.01" y2="10"/><path d="M17.32 5H6.68a4 4 0 0 0-3.978 3.59c-.006.052-.01.101-.017.152C2.604 9.416 2 14.456 2 16a3 3 0 0 0 3 3c1 0 1.5-.5 2-1l1.414-1.414A2 2 0 0 1 9.828 16h4.344a2 2 0 0 1 1.414.586L17 18c.5.5 1 1 2 1a3 3 0 0 0 3-3c0-1.545-.604-6.584-.685-7.258-.007-.05-.011-.1-.017-.151A4 4 0 0 0 17.32 5z"/>','bg'=>'#F5F3FF','color'=>'#6D28D9'],
    ];

    // If a specific category is selected
    $selectedCat = null;
    $products = [];
    if (isset($_GET['id'])) {
        $stmtCat = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmtCat->execute([$_GET['id']]);
        $selectedCat = $stmtCat->fetch(PDO::FETCH_OBJ);
        if ($selectedCat) {
            require_once '../includes/product-image.php';
            $stmtProd = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND status = 1 ORDER BY created_at DESC");
            $stmtProd->execute([$_GET['id']]);
            $products = $stmtProd->fetchAll(PDO::FETCH_OBJ);
        }
    } elseif (isset($_GET['search']) && !empty($_GET['search'])) {
        require_once '../includes/product-image.php';
        $search = '%' . $_GET['search'] . '%';
        $stmtProd = $conn->prepare("SELECT * FROM products WHERE status = 1 AND (name LIKE ? OR description LIKE ? OR brand LIKE ?) ORDER BY created_at DESC");
        $stmtProd->execute([$search, $search, $search]);
        $products = $stmtProd->fetchAll(PDO::FETCH_OBJ);
    }
?>

<style>
/* Marketplace specific styles shared */
body { background: #f5f5f5; }
.marketplace-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
@media (min-width: 640px) { .marketplace-grid { grid-template-columns: repeat(3, 1fr); gap: 12px; } }
@media (min-width: 1024px) { .marketplace-grid { grid-template-columns: repeat(5, 1fr); gap: 16px; } }

.product-card {
    height: 100%;
    min-height: 320px;
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    text-decoration: none;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}
@media (min-width: 768px) {
    .product-card { min-height: 430px; }
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}

.product-img-wrap { position: relative; aspect-ratio: 1; background: #f8fafc; display: flex; align-items: center; justify-content: center; padding: 4px; }
.product-img-wrap img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; }
.discount-badge { position: absolute; top: 0; right: 0; background: #EE4D2D; color: white; font-size: 0.7rem; font-weight: 700; padding: 3px 6px; border-bottom-left-radius: 8px; z-index: 2; }
.product-info { padding: 10px; display: flex; flex-direction: column; flex-grow: 1; }
@media (min-width: 768px) { .product-info { padding: 12px 18px 18px 18px; } }

.product-title { font-size: 0.8rem; color: #334155; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 6px; min-height: 36px; font-weight: 500; }
@media (min-width: 768px) { .product-title { font-size: 0.95rem; margin-bottom: 8px; min-height: 42px; } }

.product-price { font-size: 1rem; font-weight: 800; color: #EE4D2D; }
@media (min-width: 768px) { .product-price { font-size: 1.15rem; } }

.product-price-orig { font-size: 0.7rem; color: #94a3b8; text-decoration: line-through; margin-left: 4px; }
.product-meta { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; font-size: 0.65rem; color: #64748b; }
@media (min-width: 768px) { .product-meta { font-size: 0.75rem; margin-top: 12px; } }
</style>

<div class="min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 lg:px-12">
        <?php if($selectedCat || (isset($_GET['search']) && !empty($_GET['search']))): ?>
            <!-- Products View -->
            <div class="mb-4">
                <a href="<?php echo APPURL; ?>categories/index.php" class="text-sm text-shopmart-600 hover:text-shopmart-700 font-medium inline-flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg> All Categories
                </a>
            </div>
            
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-6">
                <?php if($selectedCat): ?>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($selectedCat->name); ?></h1>
                    <p class="text-gray-500 text-sm"><?php echo htmlspecialchars($selectedCat->description); ?></p>
                <?php else: ?>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Search Results for "<?php echo htmlspecialchars($_GET['search']); ?>"</h1>
                    <p class="text-gray-500 text-sm">Found <?php echo count($products); ?> products matching your search.</p>
                <?php endif; ?>
            </div>

            <?php if($products): ?>
            <div class="marketplace-grid mb-12">
                <?php foreach($products as $product):
                    $hasDiscount = !empty($product->discount_price) && $product->discount_price > 0;
                    $displayPrice = $hasDiscount ? $product->discount_price : $product->price;
                    $savePct = $hasDiscount ? round((($product->price - $displayPrice) / $product->price) * 100) : 0;
                ?>
                <a href="<?php echo APPURL; ?>shopping/single.php?id=<?php echo $product->id; ?>" class="product-card">
                    <div class="product-img-wrap">
                        <?php echo getProductImage($product, '400x400'); ?>
                        <?php if($hasDiscount): ?>
                            <span class="discount-badge">-<?php echo $savePct; ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <?php if($product->brand): ?>
                            <div class="text-[10px] text-gray-400 uppercase tracking-wider mb-1"><?php echo htmlspecialchars($product->brand); ?></div>
                        <?php endif; ?>
                        <div class="product-title"><?php echo htmlspecialchars($product->name); ?></div>
                        <div class="mt-auto">
                            <span class="product-price">$<?php echo number_format($displayPrice, 2); ?></span>
                            <?php if($hasDiscount): ?>
                                <span class="product-price-orig">$<?php echo number_format($product->price, 2); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="product-meta">
                            <span class="text-[10px] text-gray-500"><?php echo $product->stock > 0 ? $product->stock.' left' : 'Out of stock'; ?></span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="bg-white rounded-lg border border-gray-100 p-12 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 1 0 2.829 2.828z"/><path d="m15 5 4 4"/><path d="m14.5 8.5 5.5-5.5"/></svg>
                <h3 class="text-lg font-semibold text-gray-800">No products found</h3>
                <p class="text-gray-500 mt-2">Try adjusting your search or category filter.</p>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <!-- All Categories View -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-2">Shop by Category</h1>
                <p class="text-gray-500 text-sm">Explore our curated collections and find what you need.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach($categories as $cat):
                    $catStyle = $catIcons[$cat->slug] ?? ['bg'=>'#F5F5F5','color'=>'#555'];
                ?>
                <a href="?id=<?php echo $cat->id; ?>" class="bg-white border border-gray-200 rounded-lg p-6 flex flex-col items-center text-center transition-all group" style="background:<?php echo $catStyle['bg']; ?>;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 6px 20px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 bg-white shadow-sm transition-colors" style="color: <?php echo $catStyle['color']; ?>;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <?php echo $catStyle['svg'] ?? '<circle cx="12" cy="12" r="10"/>'; ?>
                        </svg>
                    </div>
                    <h3 class="font-bold mb-1" style="color:<?php echo $catStyle['color']; ?>;"><?php echo htmlspecialchars($cat->name); ?></h3>
                    <p class="text-xs text-gray-500 mb-3 line-clamp-2"><?php echo htmlspecialchars($cat->description ?? ''); ?></p>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-white shadow-sm" style="color:<?php echo $catStyle['color']; ?>;"><?php echo $cat->product_count; ?> products</span>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>