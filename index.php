<?php require_once 'includes/header.php'; ?>
<?php require_once 'config/config.php'; ?>
<?php require_once 'includes/product-image.php'; ?>

<?php
    // Fetch featured products
    $stmt = $conn->prepare("SELECT * FROM products WHERE featured = 1 AND status = 1 LIMIT 10");
    $stmt->execute();
    $featuredProducts = $stmt->fetchAll(PDO::FETCH_OBJ);

    // Fetch all active products
    $stmt = $conn->prepare("SELECT * FROM products WHERE status = 1 ORDER BY created_at DESC LIMIT 15");
    $stmt->execute();
    $allProducts = $stmt->fetchAll(PDO::FETCH_OBJ);

    // Fetch categories
    $stmt = $conn->prepare("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.status = 1 GROUP BY c.id ORDER BY c.name ASC LIMIT 12");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_OBJ);

    // Category icons
    $categoryIcons = ['electronics' => 'smartphone', 'fashion' => 'shirt', 'home-living' => 'sofa', 'beauty' => 'sparkles', 'sports' => 'dumbbell', 'books-stationery' => 'book-open', 'groceries' => 'apple', 'toys-games' => 'gamepad-2'];
?>

<style>
/* Marketplace specific styles */
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

.product-img-wrap { position: relative; aspect-ratio: 1; background: #fff; display: flex; align-items: center; justify-content: center; padding: 12px; border-bottom: 1px solid #f8fafc; }
.product-img-wrap img { width: 100%; height: 100%; object-fit: contain; mix-blend-mode: multiply; transition: transform 0.3s ease; }
.product-card:hover .product-img-wrap img { transform: scale(1.05); }

.discount-badge { position: absolute; top: 0; right: 0; background: #EE4D2D; color: white; font-size: 0.65rem; font-weight: 700; padding: 4px 8px; border-bottom-left-radius: 8px; z-index: 2; }
.product-info { padding: 10px; display: flex; flex-direction: column; flex-grow: 1; }
@media (min-width: 768px) { .product-info { padding: 12px 18px 18px 18px; } }

.product-title { font-size: 0.8rem; color: #334155; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 6px; min-height: 36px; font-weight: 500; }
@media (min-width: 768px) { .product-title { font-size: 0.95rem; margin-bottom: 8px; min-height: 42px; } }

.product-price { font-size: 1rem; font-weight: 800; color: #EE4D2D; }
@media (min-width: 768px) { .product-price { font-size: 1.15rem; } }

.product-price-orig { font-size: 0.7rem; color: #94a3b8; text-decoration: line-through; margin-left: 4px; }
.product-meta { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; font-size: 0.65rem; color: #64748b; }
@media (min-width: 768px) { .product-meta { font-size: 0.75rem; margin-top: 12px; } }
.cat-card { background: #fff; border: 1px solid #f1f5f9; transition: all 0.2s; border-radius: 8px; margin: 4px; }
.cat-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transform: translateY(-2px); }
</style>

<!-- ===== HERO ===== -->
<section style="background:#fff;border-bottom:1px solid #f0f0f0;">
  <div style="max-width:1280px;margin:0 auto;padding:20px 16px;">
    <div style="display:flex;flex-direction:column;gap:12px;">

      <!-- Main hero banner -->
      <div style="position:relative;border-radius:16px;overflow:hidden;background:linear-gradient(135deg,#FFF1EC 0%,#FFE4D5 40%,#FECBA6 100%);padding:32px 24px 32px 32px;display:flex;align-items:center;justify-content:space-between;min-height:180px;">
        <div style="position:absolute;right:-20px;bottom:-20px;opacity:0.06;">
          <svg width="260" height="260" viewBox="0 0 24 24" fill="#EE4D2D"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <div style="position:relative;z-index:1;">
          <div style="display:inline-flex;align-items:center;gap:5px;background:#EE4D2D;color:#fff;font-size:0.68rem;font-weight:700;padding:4px 12px;border-radius:20px;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:12px;"><i data-lucide="flame" style="width:12px;height:12px;stroke-width:3;"></i> Super Deals</div>
          <h1 style="font-size:1.75rem;font-weight:900;color:#1a1a1a;line-height:1.2;margin:0 0 10px 0;">Discover Amazing<br><span style="color:#EE4D2D;">Deals</span> Every Day</h1>
          <p style="color:#666;font-size:0.85rem;margin:0 0 18px 0;max-width:360px;line-height:1.5;">Thousands of products from trusted sellers. Fast delivery & great prices.</p>
          <a href="<?php echo APPURL; ?>categories/index.php" style="display:inline-flex;align-items:center;gap:6px;background:#EE4D2D;color:#fff;font-size:0.85rem;font-weight:700;padding:10px 22px;border-radius:8px;text-decoration:none;transition:background .2s;" onmouseover="this.style.background='#C53D20'" onmouseout="this.style.background='#EE4D2D'">
            Shop Now <i data-lucide="arrow-right" style="width:14px;height:14px;stroke-width:3;"></i>
          </a>
        </div>
      </div>

      <!-- Mini promo cards -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div style="background:linear-gradient(135deg,#FEF3C7,#FDE68A);border-radius:12px;padding:16px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:transform .15s,box-shadow .15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 16px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
          <div style="width:40px;height:40px;background:#FFFBEB;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          </div>
          <div>
            <div style="font-size:0.88rem;font-weight:700;color:#92400E;">Flash Sale</div>
            <div style="font-size:0.72rem;color:#B45309;">Up to 70% off</div>
          </div>
        </div>
        <div style="background:linear-gradient(135deg,#D1FAE5,#A7F3D0);border-radius:12px;padding:16px;display:flex;align-items:center;gap:12px;cursor:pointer;transition:transform .15s,box-shadow .15s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 16px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
          <div style="width:40px;height:40px;background:#ECFDF5;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="2" ry="2"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          </div>
          <div>
            <div style="font-size:0.88rem;font-weight:700;color:#065F46;">Free Shipping</div>
            <div style="font-size:0.72rem;color:#047857;">Orders over $50</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== CATEGORIES ===== -->
<?php
$catIcons = [
  'electronics'      => ['icon'=>'smartphone', 'bg'=>'#EFF6FF','color'=>'#1D4ED8'],
  'fashion'          => ['icon'=>'shirt',      'bg'=>'#FDF2F8','color'=>'#9D174D'],
  'home-living'      => ['icon'=>'sofa',       'bg'=>'#F0FDF4','color'=>'#166534'],
  'beauty'           => ['icon'=>'sparkles',   'bg'=>'#FFF1F2','color'=>'#BE123C'],
  'sports'           => ['icon'=>'dumbbell',   'bg'=>'#FFF7ED','color'=>'#C2410C'],
  'books-stationery' => ['icon'=>'book-open',  'bg'=>'#FEFCE8','color'=>'#92400E'],
  'groceries'        => ['icon'=>'apple',      'bg'=>'#F0FDF4','color'=>'#15803D'],
  'toys-games'       => ['icon'=>'gamepad-2',  'bg'=>'#F5F3FF','color'=>'#6D28D9'],
];
?>
<?php if($categories): ?>
<section style="max-width:1280px;margin:16px auto 0;padding:0 16px;">
  <div style="background:#fff;border-radius:12px;border:1px solid #f0f0f0;padding:16px 20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
      <h2 style="font-size:0.95rem;font-weight:800;color:#111;text-transform:uppercase;letter-spacing:0.06em;margin:0;">Shop by Category</h2>
      <a href="<?php echo APPURL; ?>categories/index.php" style="font-size:0.82rem;font-weight:600;color:#EE4D2D;text-decoration:none;">See All &rarr;</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;">
      <?php foreach($categories as $cat):
        $catStyle = $catIcons[$cat->slug] ?? ['icon'=>'package', 'bg'=>'#F5F5F5', 'color'=>'#555'];
      ?>
      <a href="<?php echo APPURL; ?>categories/index.php?id=<?php echo $cat->id; ?>" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:16px 8px 12px;border-radius:10px;background:<?php echo $catStyle['bg']; ?>;text-decoration:none;transition:transform .15s,box-shadow .15s;" onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 6px 20px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
        <div style="width:40px;height:40px;display:flex;align-items:center;justify-content:center;margin-bottom:8px;">
          <i data-lucide="<?php echo $catStyle['icon'] ?? 'package'; ?>" style="width:26px;height:26px;color:<?php echo $catStyle['color']; ?>;"></i>
        </div>
        <div style="font-size:0.72rem;font-weight:600;color:<?php echo $catStyle['color']; ?>;text-align:center;line-height:1.3;"><?php echo htmlspecialchars($cat->name); ?></div>
        <div style="font-size:0.62rem;color:#aaa;margin-top:2px;"><?php echo $cat->product_count; ?> items</div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===== FEATURED PRODUCTS ===== -->
<?php if($featuredProducts): ?>
<section class="mt-8 max-w-7xl mx-auto px-4 lg:px-12">
  <div class="flex items-center justify-between mb-4 px-2">
    <h2 class="text-lg font-bold text-shopmart-600 uppercase tracking-wide flex items-center gap-2">
      <i data-lucide="flame" style="width:20px;height:20px;"></i> Featured Deals
    </h2>
  </div>
  <div class="marketplace-grid">
    <?php foreach($featuredProducts as $product):
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
          <div class="product-title"><?php echo htmlspecialchars($product->name); ?></div>
          <div>
            <span class="product-price">$<?php echo number_format($displayPrice, 2); ?></span>
            <?php if($hasDiscount): ?>
              <span class="product-price-orig">$<?php echo number_format($product->price, 2); ?></span>
            <?php endif; ?>
          </div>
          <div class="product-meta mt-3 pt-3 border-t border-gray-100">
            <div class="flex items-center text-yellow-400">
              ★<span class="text-gray-600 ml-1 text-[10px] font-bold"><?php echo number_format(rand(45, 50) / 10, 1); ?></span>
            </div>
            <span class="text-[10px] text-gray-400 font-medium"><?php echo rand(50, 500); ?> sold</span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- ===== ALL PRODUCTS ===== -->
<section class="mt-8 mb-16 max-w-7xl mx-auto px-4 lg:px-12">
  <div class="bg-white p-4 sm:p-6 rounded-lg shadow-sm border border-gray-100">
    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100">
      <h2 class="text-lg font-bold text-gray-800 uppercase tracking-wide">Daily Discoveries</h2>
    </div>
    
    <?php if($allProducts): ?>
    <div class="marketplace-grid">
      <?php foreach($allProducts as $product):
        $hasDiscount = !empty($product->discount_price) && $product->discount_price > 0;
        $displayPrice = $hasDiscount ? $product->discount_price : $product->price;
        $savePct = $hasDiscount ? round((($product->price - $displayPrice) / $product->price) * 100) : 0;
      ?>
        <a href="<?php echo APPURL; ?>shopping/single.php?id=<?php echo $product->id; ?>" class="product-card !border-gray-100 hover:!border-shopmart-600">
          <div class="product-img-wrap">
            <?php echo getProductImage($product, '400x400'); ?>
            <?php if($hasDiscount): ?>
              <span class="discount-badge">-<?php echo $savePct; ?>%</span>
            <?php endif; ?>
          </div>
          <div class="product-info">
            <div class="product-title"><?php echo htmlspecialchars($product->name); ?></div>
            <div class="mt-auto">
              <span class="product-price">$<?php echo number_format($displayPrice, 2); ?></span>
              <?php if($hasDiscount): ?>
                <span class="product-price-orig">$<?php echo number_format($product->price, 2); ?></span>
              <?php endif; ?>
            </div>
            <div class="product-meta mt-3 pt-3 border-t border-gray-100">
              <div class="flex items-center text-yellow-400">
                ★<span class="text-gray-600 ml-1 text-[10px] font-bold"><?php echo number_format(rand(42, 50) / 10, 1); ?></span>
              </div>
              <span class="text-[10px] text-gray-400 font-medium"><?php echo rand(10, 300); ?> sold</span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="mt-8 text-center">
       <a href="<?php echo APPURL; ?>categories/index.php" class="inline-block px-12 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-sm hover:bg-gray-50 transition-colors text-sm">See More</a>
    </div>
    <?php else: ?>
    <div class="text-center py-16">
      <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
         <i data-lucide="package" style="width:32px; height:32px; color: #9ca3af;"></i>
      </div>
      <h3 class="text-base font-semibold text-gray-700">No products available yet</h3>
      <p class="text-sm text-gray-500 mt-1">Check back soon for new arrivals!</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once 'includes/footer.php'; ?>
