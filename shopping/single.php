<?php require '../includes/header.php'; ?>
<?php require '../config/config.php'; ?>
<?php require '../includes/book-cover.php'; ?>

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
    } else {
        header("Location: " . APPURL);
        exit();
    }

    $hasDiscount = isset($product->discount_price) && $product->discount_price > 0;
    $displayPrice = $hasDiscount ? $product->discount_price : $product->price;
    $originalPrice = $product->price;
    $savePct = $hasDiscount ? round((($originalPrice - $displayPrice) / $originalPrice) * 100) : 0;

    // ── Wikipedia synopsis (Redis-cached, rate-limited) ──
    require_once dirname(__DIR__) . '/includes/WikipediaService.php';

    $dbDesc  = trim($product->description ?? '');
    $useWiki = (strlen($dbDesc) < 80); // only hit Wikipedia when DB text is absent/short

    $wikiSvc = new WikipediaService();
    $synopsis = $wikiSvc->getSynopsis(
        $product->name,
        $product->author   ?? '',
        $dbDesc                    // fallback text
    );

    // If DB description is rich enough, prefer it over Wikipedia
    if (!$useWiki && $synopsis['source'] === 'wikipedia') {
        $synopsis = [
            'text'        => $dbDesc,
            'source'      => 'db',
            'pageUrl'     => '',
            'pageTitle'   => '',
            'cached'      => false,
            'rateLimited' => false,
        ];
    }
?>


<style>
/* ─── Reset & base ───────────────────────────────── */
#sp-page { background: #000; color: #fff; min-height: 100vh; }

/* ─── Dark hero banner (2-col: info left, poster right) ─ */
#sp-hero {
    background: #000;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    padding: 48px 0 0;
    overflow: hidden;
}
#sp-hero-inner {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 24px;
    display: grid;
    grid-template-columns: 1fr 260px;
    gap: 48px;
    align-items: end;
}

/* Hero left column */
#sp-hero-info {
    padding-bottom: 40px;
    min-width: 0;
}

/* Hero right column — poster */
#sp-hero-poster {
    display: flex;
    align-items: flex-end;
    justify-content: center;
    align-self: stretch;
}

/* ─── Breadcrumb ─────────────────────────────────── */
.sp-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: rgba(255,255,255,0.45);
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.sp-breadcrumb a {
    color: rgba(255,255,255,0.45);
    text-decoration: none;
    transition: color 0.2s;
}
.sp-breadcrumb a:hover { color: rgba(255,255,255,0.85); }
.sp-breadcrumb-sep { opacity: 0.3; }

/* ─── Labels row ─────────────────────────────────── */
.sp-labels {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.sp-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    padding: 3px 10px;
    border-radius: 4px;
}
.sp-label-cat  { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.1); }
.sp-label-feat { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.1); }
.sp-label-stock-ok  { background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.55); border: 1px solid rgba(255,255,255,0.1); }
.sp-label-stock-out { background: rgba(255,255,255,0.03); color: rgba(255,255,255,0.3);  border: 1px solid rgba(255,255,255,0.08); }

/* ─── Title ──────────────────────────────────────── */
#sp-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(1.6rem, 3vw, 2.4rem);
    font-weight: 700;
    line-height: 1.18;
    color: #fff;
    margin: 0 0 14px;
    letter-spacing: -0.01em;
}

/* ─── Author line ────────────────────────────────── */
.sp-author {
    font-size: 14px;
    color: rgba(255,255,255,0.5);
    margin-bottom: 20px;
}
.sp-author a { color: rgba(255,255,255,0.55); text-decoration: none; }
.sp-author a:hover { color: #fff; text-decoration: underline; }

/* ─── Star rating mock ───────────────────────────── */
.sp-rating {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 20px;
    font-size: 13px;
}
.sp-stars { color: rgba(255,255,255,0.7); letter-spacing: 2px; font-size: 14px; }
.sp-rating-count { color: rgba(255,255,255,0.4); }

/* ─── Meta pills row ─────────────────────────────── */
.sp-meta-row {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    padding: 18px 0;
    border-top: 1px solid rgba(255,255,255,0.07);
    border-bottom: 1px solid rgba(255,255,255,0.07);
    margin-bottom: 0;
}
.sp-meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: rgba(255,255,255,0.45);
}
.sp-meta-item i { width: 14px; height: 14px; }
.sp-meta-item strong { color: rgba(255,255,255,0.8); font-weight: 500; }

/* ─── Hero poster ───────────────────────────────── */
.sp-hero-poster-img {
    width: 100%;
    max-width: 240px;
    aspect-ratio: 2/3;
    object-fit: cover;
    display: block;
    border-radius: 6px 6px 0 0;
    box-shadow: -8px -8px 40px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.06);
    margin: 0 auto;
}

/* ─── Purchase card (sticky sidebar) ────────────── */
#sp-card {
    background: #0d0d0d;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 6px;
    overflow: hidden;
    position: sticky;
    top: 88px;
}
.sp-card-body { padding: 22px; }

.sp-price-main {
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
    line-height: 1;
    margin-bottom: 4px;
}
.sp-price-original {
    font-size: 14px;
    color: rgba(255,255,255,0.35);
    text-decoration: line-through;
    margin-right: 8px;
}
.sp-price-save {
    font-size: 12px;
    font-weight: 700;
    color: rgba(255,255,255,0.5);
}
.sp-price-row {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 16px;
}

/* CTA buttons */
.sp-btn-primary {
    display: block;
    width: 100%;
    padding: 14px;
    background: #fff;
    color: #1c1d1f;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-align: center;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background 0.2s, transform 0.15s;
    text-decoration: none;
    margin-bottom: 10px;
}
.sp-btn-primary:hover { background: #e5e5e5; transform: translateY(-1px); color: #1c1d1f; }

.sp-btn-secondary {
    display: block;
    width: 100%;
    padding: 13px;
    background: transparent;
    color: rgba(255,255,255,0.7);
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 4px;
    cursor: pointer;
    transition: border-color 0.2s, color 0.2s;
    text-decoration: none;
    margin-bottom: 10px;
}
.sp-btn-secondary:hover { border-color: rgba(255,255,255,0.5); color: #fff; }

/* Qty stepper */
.sp-qty {
    display: flex;
    align-items: center;
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 12px;
}
.sp-qty-btn {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.05);
    border: none;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.2s;
    flex-shrink: 0;
}
.sp-qty-btn:hover { background: rgba(255,255,255,0.1); }
.sp-qty-input {
    flex: 1;
    background: transparent;
    border: none;
    color: #fff;
    text-align: center;
    font-size: 15px;
    font-weight: 600;
    outline: none;
    padding: 0;
}

/* guarantees */
.sp-guarantees {
    margin-top: 14px;
    border-top: 1px solid rgba(255,255,255,0.07);
    padding-top: 14px;
    display: flex;
    flex-direction: column;
    gap: 9px;
}
.sp-guarantee-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 11px;
    color: rgba(255,255,255,0.4);
}
.sp-guarantee-item i { width: 14px; height: 14px; flex-shrink: 0; }

/* ─── Body content area ──────────────────────────── */
#sp-body {
    max-width: 1180px;
    margin: 0 auto;
    padding: 36px 24px 80px;
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 40px;
    background: #000;
    align-items: start;
}
#sp-content { min-width: 0; }

/* Tabs */
.sp-tabs {
    display: flex;
    gap: 0;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    margin-bottom: 32px;
}
.sp-tab {
    padding: 12px 20px;
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,0.4);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: color 0.2s, border-color 0.2s;
    background: none;
    border-top: none;
    border-left: none;
    border-right: none;
    white-space: nowrap;
}
.sp-tab.active, .sp-tab:hover { color: #fff; border-bottom-color: #fff; }

.sp-tab-panel { display: none; }
.sp-tab-panel.active { display: block; }

/* Section titles */
.sp-section-title {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    margin: 0 0 16px;
    letter-spacing: -0.01em;
}

/* Description */
.sp-description {
    font-size: 14px;
    line-height: 1.85;
    color: rgba(255,255,255,0.55);
}

/* Book details table */
.sp-details-table { width: 100%; border-collapse: collapse; }
.sp-details-table tr { border-bottom: 1px solid rgba(255,255,255,0.06); }
.sp-details-table tr:last-child { border-bottom: none; }
.sp-details-table td {
    padding: 12px 0;
    font-size: 13px;
}
.sp-details-table td:first-child {
    color: rgba(255,255,255,0.35);
    width: 140px;
    font-size: 12px;
    letter-spacing: 0.03em;
}
.sp-details-table td:last-child { color: rgba(255,255,255,0.8); font-weight: 500; }

/* What you learn checklist */
.sp-checklist { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.sp-checklist li {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 13px;
    color: rgba(255,255,255,0.6);
    line-height: 1.5;
}
.sp-checklist li i { color: rgba(255,255,255,0.4); flex-shrink: 0; margin-top: 2px; width: 14px; height: 14px; }

/* ─── Related books ──────────────────────────────── */
#sp-related {
    border-top: 1px solid rgba(255,255,255,0.07);
    padding: 40px 24px 80px;
    max-width: 1180px;
    margin: 0 auto;
}
.sp-related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; margin-top: 20px; }
.sp-related-card {
    text-decoration: none;
    display: block;
    transition: transform 0.3s;
}
.sp-related-card:hover { transform: translateY(-4px); }
.sp-related-cover {
    width: 100%;
    aspect-ratio: 3/4;
    object-fit: cover;
    border-radius: 4px;
    background: #2d2f31;
    display: block;
    margin-bottom: 10px;
}
.sp-related-title { font-size: 13px; font-weight: 600; color: #fff; margin-bottom: 4px; line-height: 1.35; }
.sp-related-author { font-size: 11px; color: rgba(255,255,255,0.35); margin-bottom: 6px; }
.sp-related-price { font-size: 14px; font-weight: 700; color: #fff; }

/* ─── Responsive ─────────────────────────────────── */
@media (max-width: 1024px) {
    #sp-hero-inner { grid-template-columns: 1fr 200px; gap: 32px; }
}
@media (max-width: 860px) {
    #sp-hero-inner { grid-template-columns: 1fr; }
    #sp-hero-poster { display: none; }
    #sp-body { grid-template-columns: 1fr; }
    #sp-card { position: static; }
}
@media (max-width: 600px) {
    .sp-checklist { grid-template-columns: 1fr; }
}
</style>

<div id="sp-page">

<!-- ══ HERO BANNER (2-col: info left │ poster right) ══════════ -->
<div id="sp-hero">
    <div id="sp-hero-inner">

        <!-- LEFT: info -->
        <div id="sp-hero-info">
            <!-- Breadcrumb -->
            <nav class="sp-breadcrumb">
                <a href="<?php echo APPURL; ?>">Home</a>
                <span class="sp-breadcrumb-sep">/</span>
                <a href="<?php echo APPURL; ?>categories/index.php">Categories</a>
                <?php if($product->category_name): ?>
                    <span class="sp-breadcrumb-sep">/</span>
                    <a href="<?php echo APPURL; ?>categories/category.php?id=<?php echo $product->category_id; ?>">
                        <?php echo htmlspecialchars($product->category_name); ?>
                    </a>
                <?php endif; ?>
                <span class="sp-breadcrumb-sep">/</span>
                <span style="color:rgba(255,255,255,0.6);"><?php echo htmlspecialchars(mb_strimwidth($product->name, 0, 40, '…')); ?></span>
            </nav>

            <!-- Labels -->
            <div class="sp-labels">
                <?php if($product->category_name): ?>
                    <span class="sp-label sp-label-cat"><?php echo htmlspecialchars($product->category_name); ?></span>
                <?php endif; ?>
                <?php if(isset($product->featured) && $product->featured): ?>
                    <span class="sp-label sp-label-feat">★ Featured</span>
                <?php endif; ?>
                <?php if($product->stock > 0): ?>
                    <span class="sp-label sp-label-stock-ok">● In Stock</span>
                <?php else: ?>
                    <span class="sp-label sp-label-stock-out">✕ Out of Stock</span>
                <?php endif; ?>
            </div>

            <!-- Title -->
            <h1 id="sp-title"><?php echo htmlspecialchars($product->name); ?></h1>

            <!-- Author -->
            <?php if(isset($product->author) && $product->author): ?>
                <p class="sp-author">
                    by <a href="#"><?php echo htmlspecialchars($product->author); ?></a>
                </p>
            <?php endif; ?>

            <!-- Mock rating -->
            <div class="sp-rating">
                <span class="sp-stars">★★★★★</span>
                <span style="font-size:13px;color:rgba(255,255,255,0.65);font-weight:600;">5.0</span>
                <span class="sp-rating-count">(<?php echo rand(120, 980); ?> ratings)</span>
                <span style="color:rgba(255,255,255,0.2);">·</span>
                <span style="font-size:12px;color:rgba(255,255,255,0.35);"><?php echo $product->stock; ?> in stock</span>
            </div>

            <!-- Meta row -->
            <div class="sp-meta-row">
                <?php if(isset($product->publisher) && $product->publisher): ?>
                    <div class="sp-meta-item">
                        <i data-lucide="building-2"></i>
                        <span>Published by <strong><?php echo htmlspecialchars($product->publisher); ?></strong></span>
                    </div>
                <?php endif; ?>
                <?php if(isset($product->pages) && $product->pages): ?>
                    <div class="sp-meta-item">
                        <i data-lucide="book-open"></i>
                        <span><strong><?php echo $product->pages; ?></strong> pages</span>
                    </div>
                <?php endif; ?>
                <?php if(isset($product->language) && $product->language): ?>
                    <div class="sp-meta-item">
                        <i data-lucide="globe"></i>
                        <span><strong><?php echo htmlspecialchars($product->language); ?></strong></span>
                    </div>
                <?php endif; ?>
                <?php if(isset($product->isbn) && $product->isbn): ?>
                    <div class="sp-meta-item">
                        <i data-lucide="barcode"></i>
                        <span>ISBN <strong><?php echo htmlspecialchars($product->isbn); ?></strong></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT: Book cover poster (flush to bottom of hero) -->
        <div id="sp-hero-poster">
            <?php echo getBookCoverImage(
                $product->isbn ?? '',
                $product->name,
                'L',
                'sp-hero-poster-img'
            ); ?>
        </div>

    </div>
</div>

<!-- ══ BODY ══════════════════════════════════════════════════ -->
<div id="sp-body">

    <!-- ── LEFT: Tabs & Content ── -->
    <div id="sp-content">

        <!-- What you'll get -->
        <div style="background:#0d0d0d;border:1px solid rgba(255,255,255,0.07);border-radius:6px;padding:24px;margin-bottom:28px;">
            <p class="sp-section-title" style="margin-bottom:12px;">What you'll get</p>
            <ul class="sp-checklist">
                <li><i data-lucide="check"></i> Physical hardcover edition</li>
                <li><i data-lucide="check"></i> Full narrative access</li>
                <li><i data-lucide="check"></i> Free shipping on $50+</li>
                <li><i data-lucide="check"></i> 30-day return policy</li>
                <li><i data-lucide="check"></i> Authenticated &amp; verified</li>
                <li><i data-lucide="check"></i> Gift-ready packaging</li>
            </ul>
        </div>

        <!-- Tabs -->
        <div class="sp-tabs" role="tablist">
            <button class="sp-tab active" onclick="switchTab(event,'tab-desc')">Description</button>
            <button class="sp-tab" onclick="switchTab(event,'tab-details')">Book Details</button>
        </div>

        <!-- Description tab -->
        <div id="tab-desc" class="sp-tab-panel active">
            <?php if($synopsis['text']): ?>
                <p class="sp-description"><?php echo nl2br(htmlspecialchars($synopsis['text'])); ?></p>
                <?php if($synopsis['source'] === 'wikipedia' && $synopsis['pageUrl']): ?>
                    <div style="margin-top:20px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.06);display:flex;align-items:center;gap:8px;">
                        <span style="font-size:11px;color:rgba(255,255,255,0.2);font-family:'JetBrains Mono',monospace;">Synopsis via</span>
                        <a href="<?php echo htmlspecialchars($synopsis['pageUrl']); ?>" target="_blank" rel="noopener"
                           style="font-size:11px;color:rgba(255,255,255,0.35);font-family:'JetBrains Mono',monospace;text-decoration:none;letter-spacing:0.05em;">Wikipedia &nearr;</a>
                        <?php if($synopsis['cached']): ?>
                            <span style="font-size:10px;color:rgba(255,255,255,0.12);font-family:'JetBrains Mono',monospace;margin-left:auto;">CACHED</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p class="sp-description" style="opacity:0.3;">No description available.</p>
            <?php endif; ?>
        </div>

        <!-- Details tab -->
        <div id="tab-details" class="sp-tab-panel">
            <table class="sp-details-table">
                <?php if(isset($product->publisher) && $product->publisher): ?>
                <tr><td>Publisher</td><td><?php echo htmlspecialchars($product->publisher); ?></td></tr>
                <?php endif; ?>
                <?php if(isset($product->pages) && $product->pages): ?>
                <tr><td>Pages</td><td><?php echo $product->pages; ?> pages</td></tr>
                <?php endif; ?>
                <?php if(isset($product->language) && $product->language): ?>
                <tr><td>Language</td><td><?php echo htmlspecialchars($product->language); ?></td></tr>
                <?php endif; ?>
                <?php if(isset($product->isbn) && $product->isbn): ?>
                <tr><td>ISBN</td><td><?php echo htmlspecialchars($product->isbn); ?></td></tr>
                <?php endif; ?>
                <?php if($product->category_name): ?>
                <tr><td>Category</td><td><?php echo htmlspecialchars($product->category_name); ?></td></tr>
                <?php endif; ?>
                <tr><td>Stock</td><td><?php echo $product->stock; ?> available</td></tr>
            </table>
        </div>
    </div>

    <!-- ── RIGHT: Sticky Purchase Card (no cover image — it's in the hero) ── -->
    <div>
        <div id="sp-card">
            <div class="sp-card-body">
                <!-- Price -->
                <div class="sp-price-main">$<?php echo number_format($displayPrice, 2); ?></div>
                <?php if($hasDiscount): ?>
                    <div style="margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                        <span class="sp-price-original">$<?php echo number_format($originalPrice, 2); ?></span>
                        <span class="sp-price-save">SAVE <?php echo $savePct; ?>%</span>
                    </div>
                <?php else: ?>
                    <div style="margin-bottom:16px;"></div>
                <?php endif; ?>

                <?php if($product->stock > 0): ?>
                    <!-- Qty stepper -->
                    <div class="sp-qty">
                        <button class="sp-qty-btn" onclick="adjustQty(-1)">−</button>
                        <input class="sp-qty-input" id="sp-qty-val" type="number" value="1" min="1" max="<?php echo $product->stock; ?>" readonly>
                        <button class="sp-qty-btn" onclick="adjustQty(1)">+</button>
                    </div>

                    <!-- Add to Cart -->
                    <form method="POST" action="<?php echo APPURL; ?>shopping/cart.php">
                        <input type="hidden" name="product_id" value="<?php echo $product->id; ?>">
                        <input type="hidden" name="quantity" id="form-qty" value="1">
                        <button type="submit" name="add_to_cart" class="sp-btn-primary">
                            Add to Cart
                        </button>
                    </form>

                    <button class="sp-btn-secondary">
                        <span style="display:flex;align-items:center;justify-content:center;gap:8px;">
                            <i data-lucide="heart" style="width:14px;height:14px;"></i> Add to Wishlist
                        </span>
                    </button>

                    <p style="text-align:center;font-size:11px;color:rgba(255,255,255,0.25);margin-top:4px;">
                        30-Day Money-Back Guarantee
                    </p>

                <?php else: ?>
                    <button class="sp-btn-primary" disabled style="opacity:0.4;cursor:not-allowed;">Out of Stock</button>
                    <p style="text-align:center;font-size:11px;color:rgba(255,255,255,0.25);margin-top:8px;">This item is currently unavailable</p>
                <?php endif; ?>

                <!-- Guarantees -->
                <div class="sp-guarantees">
                    <div class="sp-guarantee-item">
                        <i data-lucide="truck"></i>
                        <span>Free shipping on orders over $50</span>
                    </div>
                    <div class="sp-guarantee-item">
                        <i data-lucide="rotate-ccw"></i>
                        <span>30-day hassle-free returns</span>
                    </div>
                    <div class="sp-guarantee-item">
                        <i data-lucide="shield-check"></i>
                        <span>Authenticated &amp; verified edition</span>
                    </div>
                    <div class="sp-guarantee-item">
                        <i data-lucide="headphones"></i>
                        <span>24/7 customer support</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div><!-- #sp-body -->

<!-- ══ RELATED BOOKS ══════════════════════════════════════════ -->
<?php if($related): ?>
<div id="sp-related">
    <p class="sp-section-title">More from this category</p>
    <div class="sp-related-grid">
        <?php foreach($related as $rel): ?>
            <a href="single.php?id=<?php echo $rel->id; ?>" class="sp-related-card">
                <?php echo getBookCoverImage($rel->isbn ?? '', $rel->name, 'M', 'sp-related-cover'); ?>
                <p class="sp-related-title"><?php echo htmlspecialchars(mb_strimwidth($rel->name, 0, 45, '…')); ?></p>
                <?php if(isset($rel->author) && $rel->author): ?>
                    <p class="sp-related-author"><?php echo htmlspecialchars($rel->author); ?></p>
                <?php endif; ?>
                <p class="sp-related-price">$<?php echo number_format($rel->price, 2); ?></p>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

</div><!-- #sp-page -->

<script>
// ── Qty stepper ────────────────────────────────────
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

// ── Tab switching ──────────────────────────────────
function switchTab(e, panelId) {
    document.querySelectorAll('.sp-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.sp-tab-panel').forEach(p => p.classList.remove('active'));
    e.currentTarget.classList.add('active');
    const panel = document.getElementById(panelId);
    if (panel) panel.classList.add('active');
}
</script>

<?php require '../includes/footer.php'; ?>