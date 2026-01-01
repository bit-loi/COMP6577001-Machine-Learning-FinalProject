<?php require 'includes/header.php'; ?>
<?php require 'config/config.php'; ?>
<?php require 'includes/book-cover.php'; ?>

<?php
    $stmt = $conn->prepare("SELECT * FROM products WHERE status = 1 ORDER BY created_at DESC LIMIT 12"); 
    $stmt->execute();
    $allProducts = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<!-- Hero Section -->
<section class="hero-section py-5 mb-5">
    <div class="container">
        <div class="row align-items-center py-5">
            <div class="col-lg-6 text-white">
                <h1 class="display-3 fw-bold mb-4" style="font-family: 'Playfair Display', serif;">
                    Discover Your Next <br>Favorite Book
                </h1>
                <p class="lead mb-4" style="font-size: 1.25rem;">
                    Explore thousands of books across all genres. From bestsellers to hidden gems, find your perfect read today.
                </p>
                <div class="d-flex gap-3">
                    <a href="#products" class="btn btn-light btn-lg px-4">
                        <i class="fas fa-book me-2"></i>Browse Books
                    </a>
                    <a href="<?php echo APPURL; ?>categories/index.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-th-large me-2"></i>Categories
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <i class="fas fa-book-open" style="font-size: 15rem; color: rgba(255,255,255,0.2);"></i>
            </div>
        </div>
    </div>
</section>

<!-- Products Section -->
<div class="container" id="products">
    
    <!-- Section Header -->
    <div class="text-center mb-5 animate-fade-in">
        <h2 class="display-5 mb-3">
            <span class="gradient-text">Featured Books</span>
        </h2>
        <p class="text-muted lead">Handpicked recommendations just for you</p>
    </div>

    <!-- Products Grid -->
    <div class="row g-4 mb-5">
        <?php if($allProducts): ?>
            <?php foreach($allProducts as $product) : ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card h-100 shadow-sm hover-lift" style="transition: all 0.3s ease;">
                        <div style="height: 280px; overflow: hidden; border-radius: 12px 12px 0 0; background: linear-gradient(135deg, #0D1B2A 0%, #1B263B 100%); display: flex; align-items: center; justify-content: center;">
                            <?php echo getBookCoverImage($product->isbn, $product->name, 'M', 'card-img-top', ['style' => 'height: 100%; width: auto; object-fit: cover;']); ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-2" style="font-size: 1.1rem; font-weight: 600;">
                                <?php echo htmlspecialchars($product->name); ?>
                            </h5>
                            <p class="text-muted small mb-3" style="flex-grow: 1;">
                                <?php echo substr(htmlspecialchars($product->description), 0, 80); ?>...
                            </p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <?php if(isset($product->discount_price) && $product->discount_price > 0): ?>
                                        <span class="text-muted text-decoration-line-through small">$<?php echo number_format($product->price, 2); ?></span>
                                        <span class="price ms-2">$<?php echo number_format($product->discount_price, 2); ?></span>
                                    <?php else: ?>
                                        <span class="price">$<?php echo number_format($product->price, 2); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if($product->stock > 0): ?>
                                    <span class="badge badge-success">In Stock</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo APPURL; ?>shopping/single.php?id=<?php echo $product->id; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-book-open fa-5x text-muted mb-4" style="opacity: 0.3;"></i>
                <h3 class="text-muted">No Books Available</h3>
                <p class="text-muted">Check back soon for new arrivals!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Features Section -->
    <div class="row g-4 py-5 mb-5" style="background: rgba(30, 41, 59, 0.5); border-radius: 20px; margin: 0 -12px; padding: 0 12px; border: 1px solid rgba(0, 217, 255, 0.2);">
        <div class="col-md-3 col-sm-6 text-center">
            <div class="p-4">
                <div class="icon-box mb-3 mx-auto" style="width: 70px; height: 70px; border-radius: 50%; background: var(--gradient-cyan); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-shipping-fast fa-2x text-dark"></i>
                </div>
                <h5 class="fw-bold text-white">Free Shipping</h5>
                <p class="text-muted small mb-0">On orders over $50</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 text-center">
            <div class="p-4">
                <div class="icon-box mb-3 mx-auto" style="width: 70px; height: 70px; border-radius: 50%; background: var(--gradient-teal); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-undo fa-2x text-white"></i>
                </div>
                <h5 class="fw-bold text-white">Easy Returns</h5>
                <p class="text-muted small mb-0">30-day return policy</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 text-center">
            <div class="p-4">
                <div class="icon-box mb-3 mx-auto" style="width: 70px; height: 70px; border-radius: 50%; background: var(--gradient-aqua); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-lock fa-2x text-dark"></i>
                </div>
                <h5 class="fw-bold text-white">Secure Payment</h5>
                <p class="text-muted small mb-0">100% secure transactions</p>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 text-center">
            <div class="p-4">
                <div class="icon-box mb-3 mx-auto" style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, #0EA5E9 0%, #38BDF8 100%); display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-headset fa-2x text-white"></i>
                </div>
                <h5 class="fw-bold text-white">24/7 Support</h5>
                <p class="text-muted small mb-0">Always here to help</p>
            </div>
        </div>
    </div>

</div>

<?php require 'includes/footer.php'; ?>
