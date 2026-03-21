<?php require '../includes/header.php'; ?>
<?php require '../config/config.php'; ?>
<?php require '../includes/book-cover.php'; ?>

<?php
    if (!isset($_GET['id'])) {
        header("Location: " . APPURL . "categories/index.php");
        exit();
    }

    $catId = (int)$_GET['id'];

    // Fetch category info
    $stmtCat = $conn->prepare("SELECT * FROM categories WHERE id = ?");
    $stmtCat->execute([$catId]);
    $category = $stmtCat->fetch(PDO::FETCH_OBJ);

    if (!$category) {
        header("Location: " . APPURL . "categories/index.php");
        exit();
    }

    // Fetch products in this category
    $stmtP = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND status = 1 ORDER BY id DESC");
    $stmtP->execute([$catId]);
    $products = $stmtP->fetchAll(PDO::FETCH_OBJ);
?>

<style>
#cat-single-page { background: #000; color: #fff; min-height: 100vh; }

/* Hero */
#csp-hero {
    background: #000;
    border-bottom: 1px solid rgba(255,255,255,0.07);
    padding: clamp(48px,8vh,90px) 0 clamp(32px,5vh,56px);
}
#csp-hero-inner {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 clamp(20px,5vw,80px);
}

/* Breadcrumb */
.csp-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    letter-spacing: 0.05em;
    color: rgba(255,255,255,0.3);
    margin-bottom: 24px;
    font-family: 'JetBrains Mono', monospace;
}
.csp-breadcrumb a { color: rgba(255,255,255,0.4); text-decoration: none; }
.csp-breadcrumb a:hover { color: rgba(255,255,255,0.8); }

/* Title */
.csp-title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: clamp(2rem, 5vw, 3.5rem);
    font-weight: 700;
    color: #fff;
    margin: 0 0 12px;
    letter-spacing: -0.02em;
    font-style: italic;
}
.csp-subtitle {
    font-size: 13px;
    color: rgba(255,255,255,0.35);
    font-family: 'JetBrains Mono', monospace;
    letter-spacing: 0.08em;
}

/* Grid */
#csp-body {
    max-width: 1180px;
    margin: 0 auto;
    padding: clamp(32px,5vh,56px) clamp(20px,5vw,80px) 100px;
}

.csp-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 24px;
}

/* Product Card */
.csp-card {
    display: block;
    text-decoration: none;
    transition: transform 0.35s cubic-bezier(.22,.68,0,1.2);
}
.csp-card:hover { transform: translateY(-5px); }

.csp-cover {
    width: 100%;
    aspect-ratio: 3/4;
    object-fit: cover;
    border-radius: 6px;
    display: block;
    background: #0d0d0d;
    border: 1px solid rgba(255,255,255,0.06);
    margin-bottom: 12px;
    transition: border-color 0.3s;
}
.csp-card:hover .csp-cover { border-color: rgba(255,255,255,0.14); }

.csp-name {
    font-size: 13px;
    font-weight: 600;
    color: #fff;
    line-height: 1.35;
    margin-bottom: 4px;
}
.csp-author {
    font-size: 11px;
    color: rgba(255,255,255,0.35);
    margin-bottom: 8px;
    font-family: 'JetBrains Mono', monospace;
}
.csp-price {
    font-size: 14px;
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
}
.csp-price-original {
    font-size: 11px;
    color: rgba(255,255,255,0.3);
    text-decoration: line-through;
    font-weight: 400;
}

/* Empty state */
.csp-empty {
    text-align: center;
    padding: 80px 20px;
    color: rgba(255,255,255,0.2);
    font-family: 'JetBrains Mono', monospace;
    font-size: 13px;
}
</style>

<div id="cat-single-page">

    <!-- Hero -->
    <div id="csp-hero">
        <div id="csp-hero-inner">
            <nav class="csp-breadcrumb">
                <a href="<?php echo APPURL; ?>">Home</a>
                <span>/</span>
                <a href="<?php echo APPURL; ?>categories/index.php">Categories</a>
                <span>/</span>
                <span style="color:rgba(255,255,255,0.6);"><?php echo htmlspecialchars($category->name); ?></span>
            </nav>

            <h1 class="csp-title"><?php echo htmlspecialchars($category->name); ?></h1>
            <p class="csp-subtitle">
                <?php echo count($products); ?> <?php echo count($products) == 1 ? 'volume' : 'volumes'; ?> available
                &nbsp;·&nbsp; <?php echo strtoupper(htmlspecialchars($category->name)); ?> COLLECTION
            </p>
        </div>
    </div>

    <!-- Products Grid -->
    <div id="csp-body">
        <?php if ($products): ?>
            <div class="csp-grid">
                <?php foreach ($products as $p): ?>
                    <a href="<?php echo APPURL; ?>shopping/single.php?id=<?php echo $p->id; ?>" class="csp-card">
                        <?php echo getBookCoverImage($p->isbn ?? '', $p->name, 'M', 'csp-cover'); ?>
                        <p class="csp-name"><?php echo htmlspecialchars(mb_strimwidth($p->name, 0, 50, '…')); ?></p>
                        <?php if (isset($p->author) && $p->author): ?>
                            <p class="csp-author"><?php echo htmlspecialchars($p->author); ?></p>
                        <?php endif; ?>
                        <div class="csp-price">
                            <?php if (isset($p->discount_price) && $p->discount_price > 0): ?>
                                $<?php echo number_format($p->discount_price, 2); ?>
                                <span class="csp-price-original">$<?php echo number_format($p->price, 2); ?></span>
                            <?php else: ?>
                                $<?php echo number_format($p->price, 2); ?>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="csp-empty">
                <p style="font-size:2rem;margin-bottom:12px;">∅</p>
                <p>No books in this category yet.</p>
                <a href="<?php echo APPURL; ?>categories/index.php" style="color:rgba(255,255,255,0.5);margin-top:16px;display:inline-block;">← Back to all categories</a>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require '../includes/footer.php'; ?>
