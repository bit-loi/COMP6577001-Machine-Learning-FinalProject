<?php require '../includes/header.php'; ?>
<?php require '../config/config.php'; ?>

<div class="container my-5">

    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1 class="display-4 mb-3">
            <span class="gradient-text">Secure Checkout</span>
        </h1>
        <p class="lead text-muted">Complete your purchase securely</p>
    </div>

    <div class="row g-4">
        
        <!-- Checkout Form -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-user-circle me-2 text-primary"></i>Customer Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First Name *</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last Name *</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email *</label>
                                <input type="email" class="form-control" placeholder="you@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number *</label>
                                <input type="tel" class="form-control" placeholder="+1 (555) 000-0000" required>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-shipping-fast me-2 text-primary"></i>Shipping Address
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Street Address *</label>
                                <input type="text" class="form-control" placeholder="1234 Main St" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">City *</label>
                                <select class="form-select" required>
                                    <option selected disabled>Choose City</option>
                                    <option>New York</option>
                                    <option>Los Angeles</option>
                                    <option>Chicago</option>
                                    <option>Houston</option>
                                    <option>Phoenix</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">State *</label>
                                <select class="form-select" required>
                                    <option selected disabled>State</option>
                                    <option>CA</option>
                                    <option>NY</option>
                                    <option>TX</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Zip Code *</label>
                                <input type="text" class="form-control" placeholder="10001" required>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">
                        <i class="fas fa-credit-card me-2 text-primary"></i>Payment Method
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="form-check card p-3 border" style="cursor: pointer;">
                                <input class="form-check-input" type="radio" name="payment" id="card" checked>
                                <label class="form-check-label w-100" for="card">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-credit-card fa-2x me-3 text-primary"></i>
                                        <div>
                                            <strong>Credit Card</strong>
                                            <small class="d-block text-muted">Visa, Mastercard</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check card p-3 border" style="cursor: pointer;">
                                <input class="form-check-input" type="radio" name="payment" id="paypal">
                                <label class="form-check-label w-100" for="paypal">
                                    <div class="d-flex align-items-center">
                                        <i class="fab fa-paypal fa-2x me-3 text-info"></i>
                                        <div>
                                            <strong>PayPal</strong>
                                            <small class="d-block text-muted">Fast & Secure</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check card p-3 border" style="cursor: pointer;">
                                <input class="form-check-input" type="radio" name="payment" id="cod">
                                <label class="form-check-label w-100" for="cod">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-money-bill-wave fa-2x me-3 text-success"></i>
                                        <div>
                                            <strong>Cash on Delivery</strong>
                                            <small class="d-block text-muted">Pay at doorstep</small>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="cardDetails">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Card Number</label>
                                <input type="text" class="form-control" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Expiry Date</label>
                                <input type="text" class="form-control" placeholder="MM/YY">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">CVV</label>
                                <input type="text" class="form-control" placeholder="123">
                            </div>
                        </div>
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
                    <!-- Cart Items Summary -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/50x70/667eea/ffffff?text=1" class="rounded me-2" style="width: 40px; height: 55px; object-fit: cover;">
                                <div>
                                    <small class="fw-bold d-block">The Great Gatsby</small>
                                    <small class="text-muted">Qty: 1</small>
                                </div>
                            </div>
                            <span class="fw-bold">$12.99</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/50x70/f5576c/ffffff?text=2" class="rounded me-2" style="width: 40px; height: 55px; object-fit: cover;">
                                <div>
                                    <small class="fw-bold d-block">To Kill a Mockingbird</small>
                                    <small class="text-muted">Qty: 2</small>
                                </div>
                            </div>
                            <span class="fw-bold">$29.98</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <div class="d-flex align-items-center">
                                <img src="https://via.placeholder.com/50x70/4facfe/ffffff?text=3" class="rounded me-2" style="width: 40px; height: 55px; object-fit: cover;">
                                <div>
                                    <small class="fw-bold d-block">1984</small>
                                    <small class="text-muted">Qty: 1</small>
                                </div>
                            </div>
                            <span class="fw-bold">$13.99</span>
                        </div>
                    </div>

                    <hr>

                    <!-- Price Breakdown -->
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal:</span>
                        <span class="fw-bold">$56.96</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping:</span>
                        <span class="text-success fw-bold">FREE</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Tax (10%):</span>
                        <span class="fw-bold">$5.70</span>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold fs-5">Total:</span>
                        <span class="fw-bold fs-4 text-primary">$62.66</span>
                    </div>

                    <!-- Place Order Button -->
                    <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                        <i class="fas fa-lock me-2"></i>Place Order
                    </button>

                    <!-- Security Badges -->
                    <div class="text-center mt-3">
                        <small class="text-muted d-block mb-2">
                            <i class="fas fa-shield-alt me-1"></i>Secure & Encrypted Checkout
                        </small>
                        <small class="text-muted d-block">
                            <i class="fas fa-undo me-1"></i>30-Day Return Policy
                        </small>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<?php require '../includes/footer.php'; ?>
