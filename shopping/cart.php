<?php require '../includes/header.php'; ?>
<?php require '../config/config.php'; ?>

<?php
    // TODO: Fetch real cart items from database
    // For now using dummy data
    $cartItems = []; // Will be populated from database
?>

<div class="container my-5">
    
    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1 class="display-4 mb-3">
            <span class="gradient-text">Shopping Cart</span>
        </h1>
        <p class="lead text-muted">Review your items before checkout</p>
    </div>

    <div class="row g-4">
        
        <!-- Cart Items -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Cart Items</h5>
                        <span class="badge badge-primary px-3 py-2">3 Items</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th scope="col" class="border-0" style="width: 80px;"></th>
                                    <th scope="col" class="border-0">Product</th>
                                    <th scope="col" class="border-0">Price</th>
                                    <th scope="col" class="border-0" style="width: 140px;">Quantity</th>
                                    <th scope="col" class="border-0">Subtotal</th>
                                    <th scope="col" class="border-0" style="width: 80px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Sample Cart Item 1 -->
                                <tr>
                                    <td class="align-middle">
                                        <img src="https://via.placeholder.com/100x140/667eea/ffffff?text=Book+1" 
                                             class="img-fluid rounded" 
                                             alt="Book"
                                             style="width: 60px; height: 80px; object-fit: cover;">
                                    </td>
                                    <td class="align-middle">
                                        <h6 class="mb-1 fw-bold">The Great Gatsby</h6>
                                        <small class="text-muted">by F. Scott Fitzgerald</small>
                                    </td>
                                    <td class="align-middle fw-bold">$12.99</td>
                                    <td class="align-middle">
                                        <div class="input-group input-group-sm" style="max-width: 130px;">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center" value="1" min="1">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="align-middle fw-bold text-primary">$12.99</td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-sm btn-danger" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Sample Cart Item 2 -->
                                <tr>
                                    <td class="align-middle">
                                        <img src="https://via.placeholder.com/100x140/f5576c/ffffff?text=Book+2" 
                                             class="img-fluid rounded" 
                                             alt="Book"
                                             style="width: 60px; height: 80px; object-fit: cover;">
                                    </td>
                                    <td class="align-middle">
                                        <h6 class="mb-1 fw-bold">To Kill a Mockingbird</h6>
                                        <small class="text-muted">by Harper Lee</small>
                                    </td>
                                    <td class="align-middle fw-bold">$14.99</td>
                                    <td class="align-middle">
                                        <div class="input-group input-group-sm" style="max-width: 130px;">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center" value="2" min="1">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="align-middle fw-bold text-primary">$29.98</td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-sm btn-danger" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Sample Cart Item 3 -->
                                <tr>
                                    <td class="align-middle">
                                        <img src="https://via.placeholder.com/100x140/4facfe/ffffff?text=Book+3" 
                                             class="img-fluid rounded" 
                                             alt="Book"
                                             style="width: 60px; height: 80px; object-fit: cover;">
                                    </td>
                                    <td class="align-middle">
                                        <h6 class="mb-1 fw-bold">1984</h6>
                                        <small class="text-muted">by George Orwell</small>
                                    </td>
                                    <td class="align-middle fw-bold">$13.99</td>
                                    <td class="align-middle">
                                        <div class="input-group input-group-sm" style="max-width: 130px;">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center" value="1" min="1">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="align-middle fw-bold text-primary">$13.99</td>
                                    <td class="align-middle text-center">
                                        <button class="btn btn-sm btn-danger" title="Remove">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-between">
                        <a href="<?php echo APPURL; ?>" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                        <button class="btn btn-outline-danger">
                            <i class="fas fa-trash me-2"></i>Clear Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 sticky-top" style="top: 100px;">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Subtotal (3 items):</span>
                        <span class="fw-bold">$56.96</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Shipping:</span>
                        <span class="text-success fw-bold">FREE</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tax:</span>
                        <span class="fw-bold">$5.70</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-4 text-primary">$62.66</span>
                    </div>

                    <!-- Promo Code -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Have a promo code?</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Enter code">
                            <button class="btn btn-outline-secondary" type="button">Apply</button>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <a href="<?php echo APPURL; ?>shopping/checkout.php" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-lock me-2"></i>Proceed to Checkout
                    </a>

                    <!-- Secure Badges -->
                    <div class="text-center">
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-shield-alt me-1"></i>Secure Checkout
                        </small>
                        <div class="d-flex justify-content-center gap-2">
                            <i class="fab fa-cc-visa fa-2x text-primary"></i>
                            <i class="fab fa-cc-mastercard fa-2x text-warning"></i>
                            <i class="fab fa-cc-paypal fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Trust Badges -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-body text-center p-4">
                    <i class="fas fa-shipping-fast fa-3x mb-3" style="background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                    <h6 class="fw-bold mb-2">Free Shipping</h6>
                    <small class="text-muted">On orders over $50</small>
                </div>
            </div>
        </div>

    </div>

</div>

<?php require '../includes/footer.php'; ?>