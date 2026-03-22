<?php
session_start();
require '../config/config.php';
require '../middleware/auth.php';

// If form is submitted, process the Dummy Payment Gateway Simulation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Here we simulate the checkout process without checking a real bank balance.
        $userId = $_SESSION['user_id'] ?? 1; // Fallback to 1 if session user_id missing
        $totalAmount = 62.66; // Hardcoded dummy amount based on the UI
        $status = 'pending'; // Normal E-commerce starts with pending
        
        // Insert dummy transaction into orders table for ML scanning later!
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (:user_id, :total_amount, :status, NOW())");
        $stmt->execute([
            ':user_id' => $userId,
            ':total_amount' => $totalAmount,
            ':status' => $status
        ]);
        
        // Success redirect
        $_SESSION['checkout_success'] = "Payment Simulation Successful! Your order has been placed and is now being analyzed by our AI System.";
        header("Location: checkout.php?success=1");
        exit;
    } catch (PDOException $e) {
        $errorMsg = "Order failed to save: " . $e->getMessage();
    }
}
?>
<?php include '../includes/header.php'; ?>

<div class="container my-5">

    <!-- Page Header -->
    <div class="text-center mb-5">
        <h1 class="display-4 mb-3">
            <span class="gradient-text">Secure Checkout</span>
        </h1>
        <p class="lead text-muted">Complete your purchase securely</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success d-flex align-items-center mb-4 border-0 shadow-sm rounded-4 p-4" role="alert" style="background-color: #d1fae5; color: #065f46;">
            <i class="fas fa-check-circle fa-2x me-3"></i>
            <div>
                <h5 class="mb-1 fw-bold">Transaction Successful!</h5>
                <p class="mb-0"><?php echo htmlspecialchars($_SESSION['checkout_success'] ?? 'Order placed.'); ?></p>
            </div>
        </div>
        <?php unset($_SESSION['checkout_success']); ?>
        <div class="text-center mt-5 mb-5 pb-5">
             <a href="../admin/index.php" class="btn btn-outline-primary rounded-pill px-4">Go to Admin Dashboard to See AI Analysis</a>
             <a href="../index.php" class="btn btn-primary rounded-pill px-4 ms-2">Continue Shopping</a>
        </div>
    <?php else: ?>

    <?php if (isset($errorMsg)): ?>
        <div class="alert alert-danger mb-4"><?php echo $errorMsg; ?></div>
    <?php endif; ?>

    <form method="POST" action="checkout.php">
        <div class="row g-4">
            
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4 rounded-4">
                    <div class="card-header bg-white py-3 border-bottom-0 pt-4 px-4">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-user-circle me-2 text-primary"></i>Customer Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">First Name *</label>
                                <input type="text" name="first_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Last Name *</label>
                                <input type="text" name="last_name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Email *</label>
                                <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" placeholder="+1 (555) 000-0000" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-4 rounded-4">
                    <div class="card-header bg-white py-3 border-bottom-0 pt-4 px-4">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-shipping-fast me-2 text-primary"></i>Shipping Address
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold">Street Address *</label>
                                <input type="text" name="address" class="form-control" placeholder="1234 Main St" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">City *</label>
                                <select name="city" class="form-select" required>
                                    <option value="" selected disabled>Choose City</option>
                                    <option value="New York">New York</option>
                                    <option value="Los Angeles">Los Angeles</option>
                                    <option value="Chicago">Chicago</option>
                                    <option value="Houston">Houston</option>
                                    <option value="Phoenix">Phoenix</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">State *</label>
                                <select name="state" class="form-select" required>
                                    <option value="" selected disabled>State</option>
                                    <option value="CA">CA</option>
                                    <option value="NY">NY</option>
                                    <option value="TX">TX</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Zip Code *</label>
                                <input type="text" name="zip" class="form-control" placeholder="10001" required>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-white py-3 border-bottom-0 pt-4 px-4">
                        <h5 class="mb-0 fw-bold">
                            <i class="fas fa-credit-card me-2 text-primary"></i>Payment Method (Simulation)
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="form-check card p-3 border" style="cursor: pointer;">
                                    <input class="form-check-input" type="radio" value="card" name="payment" id="card" checked>
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
                                    <input class="form-check-input" type="radio" value="paypal" name="payment" id="paypal">
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
                                    <input class="form-check-input" type="radio" value="cod" name="payment" id="cod">
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

                        <div id="cardDetails" class="bg-light p-4 rounded-3">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label fw-bold">Card Number (Mock Data)</label>
                                    <input type="text" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Expiry Date</label>
                                    <input type="text" name="card_expiry" class="form-control" placeholder="MM/YY" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">CVV</label>
                                    <input type="text" name="card_cvv" class="form-control" placeholder="123" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top rounded-4" style="top: 100px;">
                    <div class="card-header bg-white py-3 border-bottom-0 pt-4 px-4">
                        <h5 class="mb-0 fw-bold">Order Summary</h5>
                    </div>
                    <div class="card-body px-4 pb-4">
                        <!-- Cart Items Summary -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <img src="https://via.placeholder.com/50x70/667eea/ffffff?text=1" class="rounded me-3 shadow-sm" style="width: 45px; height: 60px; object-fit: cover;">
                                    <div>
                                        <span class="fw-bold d-block text-dark">The Great Gatsby</span>
                                        <small class="text-muted">Qty: 1</small>
                                    </div>
                                </div>
                                <span class="fw-bold text-dark mt-2">$12.99</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <img src="https://via.placeholder.com/50x70/f5576c/ffffff?text=2" class="rounded me-3 shadow-sm" style="width: 45px; height: 60px; object-fit: cover;">
                                    <div>
                                        <span class="fw-bold d-block text-dark">To Kill a Mockingbird</span>
                                        <small class="text-muted">Qty: 2</small>
                                    </div>
                                </div>
                                <span class="fw-bold text-dark mt-2">$29.98</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <img src="https://via.placeholder.com/50x70/4facfe/ffffff?text=3" class="rounded me-3 shadow-sm" style="width: 45px; height: 60px; object-fit: cover;">
                                    <div>
                                        <span class="fw-bold d-block text-dark">1984</span>
                                        <small class="text-muted">Qty: 1</small>
                                    </div>
                                </div>
                                <span class="fw-bold text-dark mt-2">$13.99</span>
                            </div>
                        </div>

                        <hr class="text-muted opacity-25">

                        <!-- Price Breakdown -->
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-bold text-dark">$56.96</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping:</span>
                            <span class="text-success fw-bold">FREE</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Tax (10%):</span>
                            <span class="fw-bold text-dark">$5.70</span>
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <div class="d-flex justify-content-between mb-4 mt-2">
                            <span class="fw-bold fs-5 text-dark">Total:</span>
                            <span class="fw-bold fs-5 text-primary">$62.66</span>
                        </div>

                        <!-- Place Order Button -->
                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3 rounded-pill py-3 fw-bold shadow-sm">
                            <i class="fas fa-lock me-2"></i>Place Order
                        </button>

                        <!-- Security Badges -->
                        <div class="text-center mt-4">
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-shield-alt me-1 text-success"></i>Secure & Encrypted Checkout
                            </small>
                            <small class="text-muted d-block">
                                <i class="fas fa-robot me-1 text-info"></i>Transactions Protected by A.I. Anomaly Detection
                            </small>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
    <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>
