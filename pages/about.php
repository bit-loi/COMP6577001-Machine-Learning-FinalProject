<?php require '../includes/header.php'; ?>
<?php require '../config/config.php'; ?>

<div class="container">
    
    <!-- Hero Section -->
    <section class="py-5 my-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0 animate-fade-in">
                <span class="badge badge-primary mb-3">About Us</span>
                <h1 class="display-4 mb-4">Your Trusted <span class="gradient-text">Online Bookstore</span></h1>
                <p class="lead text-muted mb-4">
                    We are passionate about books and dedicated to bringing the best reading experience to book lovers worldwide.
                </p>
                <p class="text-muted mb-4">
                    Since our founding, we've been committed to providing a curated selection of books across all genres, 
                    from timeless classics to the latest bestsellers. Our mission is to make quality literature accessible to everyone.
                </p>
                <a href="<?php echo APPURL; ?>" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-book me-2"></i>Browse Books
                </a>
                <a href="<?php echo APPURL; ?>pages/contact.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-envelope me-2"></i>Contact Us
                </a>
            </div>
            <div class="col-lg-6 animate-fade-in">
                <div class="position-relative">
                    <div style="background: var(--primary-gradient); border-radius: 20px; padding: 40px; text-align: center;">
                        <i class="fas fa-book-reader" style="font-size: 150px; color: white; opacity: 0.9;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-5 mb-5">
        <div class="row g-4 text-center">
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-custom h-100">
                    <div class="card-body p-4">
                        <div class="icon-box mb-3 mx-auto" style="width: 70px; height: 70px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-book fa-2x text-white"></i>
                        </div>
                        <h3 class="gradient-text mb-2">10,000+</h3>
                        <p class="text-muted mb-0">Books Available</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-custom h-100">
                    <div class="card-body p-4">
                        <div class="icon-box mb-3 mx-auto" style="width: 70px; height: 70px; border-radius: 50%; background: var(--success-gradient); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-users fa-2x text-white"></i>
                        </div>
                        <h3 class="gradient-text mb-2">50,000+</h3>
                        <p class="text-muted mb-0">Happy Customers</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-custom h-100">
                    <div class="card-body p-4">
                        <div class="icon-box mb-3 mx-auto" style="width: 70px; height: 70px; border-radius: 50%; background: var(--secondary-gradient); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shipping-fast fa-2x text-white"></i>
                        </div>
                        <h3 class="gradient-text mb-2">100+</h3>
                        <p class="text-muted mb-0">Countries Served</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-custom h-100">
                    <div class="card-body p-4">
                        <div class="icon-box mb-3 mx-auto" style="width: 70px; height: 70px; border-radius: 50%; background: var(--gradient-warning); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-star fa-2x text-white"></i>
                        </div>
                        <h3 class="gradient-text mb-2">4.9/5</h3>
                        <p class="text-muted mb-0">Customer Rating</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="py-5 mb-5">
        <div class="text-center mb-5">
            <h2 class="h1 gradient-text mb-3">Our Core Values</h2>
            <p class="text-muted">What makes us different</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card shadow-custom h-100 border-0">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-heart fa-3x" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                        </div>
                        <h4 class="mb-3">Passion for Books</h4>
                        <p class="text-muted">We carefully curate every book in our collection, ensuring quality and variety for all readers.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-custom h-100 border-0">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-shield-alt fa-3x" style="background: var(--success-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                        </div>
                        <h4 class="mb-3">Quality Assurance</h4>
                        <p class="text-muted">Every book is carefully inspected to ensure it meets our high standards before shipping.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-custom h-100 border-0">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <i class="fas fa-truck fa-3x" style="background: var(--secondary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                        </div>
                        <h4 class="mb-3">Fast Delivery</h4>
                        <p class="text-muted">We ship worldwide with trusted courier services to get your books to you quickly and safely.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-5 mb-5">
        <div class="card shadow-custom border-0" style="background: var(--primary-gradient);">
            <div class="card-body p-5 text-white text-center">
                <h2 class="mb-4">Our Mission</h2>
                <p class="lead mb-0" style="max-width: 800px; margin: 0 auto;">
                    To inspire a love of reading by providing access to quality books at affordable prices, 
                    while delivering exceptional customer service and supporting authors and publishers worldwide.
                </p>
            </div>
        </div>
    </section>

    <!-- Team Section (Optional) -->
    <section class="py-5 mb-5">
        <div class="text-center mb-5">
            <h2 class="h1 gradient-text mb-3">Why Choose Us?</h2>
            <p class="text-muted">Benefits of shopping with us</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6">
                <div class="d-flex align-items-start mb-4">
                    <div class="me-3">
                        <div style="width: 50px; height: 50px; border-radius: 12px; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check text-white fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-2">Wide Selection</h5>
                        <p class="text-muted mb-0">Browse through thousands of titles across all genres and categories.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-start mb-4">
                    <div class="me-3">
                        <div style="width: 50px; height: 50px; border-radius: 12px; background: var(--success-gradient); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check text-white fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-2">Competitive Prices</h5>
                        <p class="text-muted mb-0">Enjoy affordable prices and regular discounts on bestsellers.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-start mb-4">
                    <div class="me-3">
                        <div style="width: 50px; height: 50px; border-radius: 12px; background: var(--secondary-gradient); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check text-white fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-2">Secure Shopping</h5>
                        <p class="text-muted mb-0">Shop with confidence using our secure payment gateway.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex align-items-start mb-4">
                    <div class="me-3">
                        <div style="width: 50px; height: 50px; border-radius: 12px; background: var(--gradient-warning); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check text-white fa-lg"></i>
                        </div>
                    </div>
                    <div>
                        <h5 class="mb-2">24/7 Support</h5>
                        <p class="text-muted mb-0">Our customer service team is always ready to help you.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<?php require '../includes/footer.php'; ?>
