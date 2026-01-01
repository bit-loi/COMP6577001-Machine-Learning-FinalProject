<?php require '../includes/header.php'; ?>
<?php require '../config/config.php'; ?>

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
    } else {
        header("Location: " . APPURL);
        exit();
    }
?>

<div class="container my-5">
    
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo APPURL; ?>">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo APPURL; ?>categories/index.php">Categories</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product->name); ?></li>
        </ol>
    </nav>

    <!-- Product Details -->
    <div class="row g-4 mb-5">
        
        <!-- Product Image -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-0">
                    <img 
                        src="../images/<?php echo $product->image; ?>" 
                        class="img-fluid w-100" 
                        alt="<?php echo htmlspecialchars($product->name); ?>"
                        style="border-radius: 12px; object-fit: cover; max-height: 600px;"
                        onerror="this.src='https://via.placeholder.com/500x700/667eea/ffffff?text=Book+Cover'"
                    >
                </div>
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-lg-7">
            <div class="mb-3">
                <?php if($product->category_name): ?>
                    <span class="badge badge-primary px-3 py-2"><?php echo $product->category_name; ?></span>
                <?php endif; ?>
                <?php if($product->featured): ?>
                    <span class="badge px-3 py-2" style="background: var(--gradient-warning);">
                        <i class="fas fa-star me-1"></i>Featured
                    </span>
                <?php endif; ?>
            </div>

            <h1 class="display-5 mb-3" style="font-family: 'Playfair Display', serif;">
                <?php echo htmlspecialchars($product->name); ?>
            </h1>

            <?php if($product->author): ?>
                <p class="lead text-muted mb-4">
                    <i class="fas fa-user-edit me-2"></i>by <?php echo htmlspecialchars($product->author); ?>
                </p>
            <?php endif; ?>

            <!-- Price -->
            <div class="mb-4">
                <?php if(isset($product->discount_price) && $product->discount_price > 0): ?>
                    <div class="d-flex align-items-center gap-3">
                        <span class="display-5 fw-bold text-primary">
                            $<?php echo number_format($product->discount_price, 2); ?>
                        </span>
                        <span class="h4 text-muted text-decoration-line-through">
                            $<?php echo number_format($product->price, 2); ?>
                        </span>
                        <span class="badge bg-danger px-3 py-2">
                            SAVE <?php echo round((($product->price - $product->discount_price) / $product->price) * 100); ?>%
                        </span>
                    </div>
                <?php else: ?>
                    <span class="display-5 fw-bold text-primary">
                        $<?php echo number_format($product->price, 2); ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Stock Status -->
            <div class="mb-4">
                <?php if($product->stock > 0): ?>
                    <span class="badge badge-success px-3 py-2 fs-6">
                        <i class="fas fa-check-circle me-2"></i>In Stock (<?php echo $product->stock; ?> available)
                    </span>
                <?php else: ?>
                    <span class="badge badge-danger px-3 py-2 fs-6">
                        <i class="fas fa-times-circle me-2"></i>Out of Stock
                    </span>
                <?php endif; ?>
            </div>

            <!-- Description -->
            <div class="mb-4">
                <h5 class="fw-bold mb-3">Description</h5>
                <p class="text-muted" style="line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($product->description)); ?>
                </p>
            </div>

            <!-- Book Details -->
            <div class="card bg-light border-0 mb-4">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Book Details</h6>
                    <div class="row g-3">
                        <?php if($product->publisher): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Publisher</small>
                                <strong><?php echo htmlspecialchars($product->publisher); ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if($product->pages): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Pages</small>
                                <strong><?php echo $product->pages; ?> pages</strong>
                            </div>
                        <?php endif; ?>
                        <?php if($product->language): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Language</small>
                                <strong><?php echo htmlspecialchars($product->language); ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if($product->isbn): ?>
                            <div class="col-md-6">
                                <small class="text-muted d-block">ISBN</small>
                                <strong><?php echo htmlspecialchars($product->isbn); ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Add to Cart -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Quantity</label>
                    <div class="input-group">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="form-control text-center" value="1" min="1" max="<?php echo $product->stock; ?>">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold small d-block">&nbsp;</label>
                    <?php if($product->stock > 0): ?>
                        <button class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                        </button>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg w-100" disabled>
                          <i class="fas fa-ban me-2"></i>Out of Stock
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Additional Actions -->
            <div class="d-flex gap-2">
                <button class="btn btn-outline-primary flex-fill">
                    <i class="fas fa-heart me-2"></i>Add to Wishlist
                </button>
                <button class="btn btn-outline-primary flex-fill">
                    <i class="fas fa-share-alt me-2"></i>Share
                </button>
            </div>

            <!-- Features -->
            <div class="row g-3 mt-4">
                <div class="col-md-4 text-center">
                    <i class="fas fa-shipping-fast fa-2x mb-2" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                    <h6 class="fw-bold small mb-1">Free Shipping</h6>
                    <small class="text-muted">On orders $50+</small>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-undo fa-2x mb-2" style="background: var(--success-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                    <h6 class="fw-bold small mb-1">Easy Returns</h6>
                    <small class="text-muted">30-day policy</small>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-headset fa-2x mb-2" style="background: var(--secondary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                    <h6 class="fw-bold small mb-1">24/7 Support</h6>
                    <small class="text-muted">Always available</small>
                </div>
            </div>
        </div>

    </div>

    <!-- Related Products (Optional - can be added later) -->

</div>

<?php require '../includes/footer.php'; ?>