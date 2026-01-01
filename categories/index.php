<?php require '../includes/header.php'; ?>
<?php require '../config/config.php'; ?>

<?php
    // Fetch all categories
    $stmt = $conn->prepare("SELECT c.*, COUNT(p.id) as product_count 
                           FROM categories c 
                           LEFT JOIN products p ON c.id = p.category_id AND p.status = 1
                           GROUP BY c.id 
                           ORDER BY c.name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_OBJ);
?>

<div class="container">
    
    <!-- Page Header -->
    <div class="text-center my-5 animate-fade-in">
        <h1 class="display-4 mb-3">
            <span class="gradient-text">Browse by Category</span>
        </h1>
        <p class="lead text-muted">Explore our curated collection of books organized by genre</p>
    </div>

    <!-- Categories Grid -->
    <div class="row g-4 mb-5">
        <?php if($categories): ?>
            <?php
            $gradients = [
                'linear-gradient(135deg, #06B6D4 0%, #00D9FF 100%)',
                'linear-gradient(135deg, #14B8A6 0%, #00E5CC 100%)',
                'linear-gradient(135deg, #0EA5E9 0%, #38BDF8 100%)',
                'linear-gradient(135deg, #0891B2 0%, #06B6D4 100%)',
                'linear-gradient(135deg, #0D9488 0%, #14B8A6 100%)',
                'linear-gradient(135deg, #0284C7 0%, #0EA5E9 100%)',
                'linear-gradient(135deg, #0E7490 0%, #06B6D4 100%)',
                'linear-gradient(135deg, #0F766E 0%, #14B8A6 100%)',
            ];
            $icons = [
                'fa-book',
                'fa-book-open',
                'fa-book-reader',
                'fa-graduation-cap',
                'fa-child',
                'fa-laptop-code',
                'fa-briefcase',
                'fa-heart'
            ];
            ?>
            <?php foreach($categories as $index => $category) : ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card h-100 border-0 shadow-sm hover-lift" style="overflow: hidden;">
                        <div class="card-body text-center p-4">
                            <!-- Icon Box with Gradient -->
                            <div class="icon-box mb-4 mx-auto" style="width: 100px; height: 100px; border-radius: 20px; background: <?php echo $gradients[$index % count($gradients)]; ?>; display: flex; align-items: center; justify-content: center;">
                                <i class="fas <?php echo $icons[$index % count($icons)]; ?> fa-3x text-white"></i>
                            </div>
                            
                            <!-- Category Name -->
                            <h4 class="mb-2" style="font-weight: 700;">
                                <?php echo htmlspecialchars($category->name); ?>
                            </h4>
                            
                            <!-- Description -->
                            <p class="text-muted small mb-3">
                                <?php echo htmlspecialchars($category->description ?? 'Explore our collection'); ?>
                            </p>
                            
                            <!-- Product Count Badge -->
                            <div class="mb-3">
                                <span class="badge" style="background: <?php echo $gradients[$index % count($gradients)]; ?>; font-size: 0.9rem; padding: 0.5rem 1rem;">
                                    <?php echo $category->product_count; ?> 
                                    <?php echo $category->product_count == 1 ? 'Book' : 'Books'; ?>
                                </span>
                            </div>
                            
                            <!-- Browse Button -->
                            <a href="category.php?id=<?php echo $category->id; ?>" class="btn btn-primary w-100">
                                <i class="fas fa-arrow-right me-2"></i>Explore
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-tags fa-5x text-muted mb-4" style="opacity: 0.3;"></i>
                <h3 class="text-muted">No Categories Available</h3>
                <p class="text-muted">Categories will be added soon!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Call to Action -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: var(--primary-gradient); border-radius: 20px; overflow: hidden;">
                <div class="card-body p-5 text-center text-white">
                    <h2 class="mb-3" style="font-family: 'Playfair Display', serif;">Can't Find What You're Looking For?</h2>
                    <p class="lead mb-4">Browse all our books or contact us for recommendations</p>
                    <div class="d-flex gap-3 justify-content-center">
                        <a href="<?php echo APPURL; ?>" class="btn btn-light btn-lg px-5">
                            <i class="fas fa-book me-2"></i>View All Books
                        </a>
                        <a href="<?php echo APPURL; ?>pages/contact.php" class="btn btn-outline-light btn-lg px-5">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require '../includes/footer.php'; ?>