<?php
session_start();
require '../config/config.php';
require '../middleware/auth.php';
require '../includes/product-image.php';

// Handle add to cart
if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    require_valid_csrf_token();

    $productId = (int)$_POST['product_id'];
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $userId = $_SESSION['user_id'];
    
    // Check if item already in cart
    $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$userId, $productId]);
    $existing = $stmt->fetch(PDO::FETCH_OBJ);
    
    if ($existing) {
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$quantity, $existing->id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$userId, $productId, $quantity]);
    }
}

// Handle remove from cart
if (isset($_POST['remove_from_cart']) && isset($_POST['cart_id'])) {
    require_valid_csrf_token();

    $cartId = (int)$_POST['cart_id'];
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cartId, $_SESSION['user_id']]);
    header("Location: cart.php");
    exit;
}

// Fetch cart items
$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.discount_price, p.image, p.stock, p.brand FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ? ORDER BY c.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll(PDO::FETCH_OBJ);

$subtotal = 0;
foreach ($cartItems as $item) {
    $itemPrice = ($item->discount_price && $item->discount_price > 0) ? $item->discount_price : $item->price;
    $subtotal += $itemPrice * $item->quantity;
}
$tax = round($subtotal * 0.1, 2);
$shipping = $subtotal >= 50 ? 0 : 5.99;
$total = $subtotal + $tax + $shipping;
?>
<?php include '../includes/header.php'; ?>

<div style="background: #f5f5f5; min-height: 60vh; padding: 40px 0;">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #222; margin-bottom: 32px;">Shopping Cart</h1>

        <?php if($cartItems): ?>
        <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start;">
            <!-- Cart Items -->
            <div style="background: #fff; border-radius: 16px; border: 1px solid #eee; overflow: hidden;">
                <?php foreach($cartItems as $item):
                    $itemPrice = ($item->discount_price && $item->discount_price > 0) ? $item->discount_price : $item->price;
                ?>
                <div style="display: flex; align-items: center; gap: 16px; padding: 20px 24px; border-bottom: 1px solid #f5f5f5;">
                    <div style="width: 80px; height: 80px; border-radius: 12px; overflow: hidden; background: #f9f9f9; flex-shrink: 0;">
                        <?php echo getProductImage($item, '160x160', '', ['style' => 'width:100%; height:100%; object-fit:cover;']); ?>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="font-size: 0.9rem; font-weight: 600; color: #333;"><?php echo htmlspecialchars($item->name); ?></div>
                        <?php if($item->brand): ?>
                            <div style="font-size: 0.75rem; color: #aaa;"><?php echo htmlspecialchars($item->brand); ?></div>
                        <?php endif; ?>
                        <div style="font-size: 0.85rem; color: #555; margin-top: 4px;">Qty: <?php echo $item->quantity; ?></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1rem; font-weight: 700; color: #EE4D2D;">$<?php echo number_format($itemPrice * $item->quantity, 2); ?></div>
                        <form method="POST" action="cart.php" style="margin-top: 4px;">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="cart_id" value="<?php echo $item->id; ?>">
                            <button type="submit" name="remove_from_cart" style="font-size: 0.75rem; color: #ef4444; background: transparent; border: 0; padding: 0; cursor: pointer;">Remove</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
                <div style="padding: 16px 24px;">
                    <a href="<?php echo APPURL; ?>" style="font-size: 0.85rem; color: #FF6B35; text-decoration: none; font-weight: 600;">← Continue Shopping</a>
                </div>
            </div>

            <!-- Order Summary -->
            <div style="background: #fff; border-radius: 16px; border: 1px solid #eee; padding: 24px; position: sticky; top: 100px;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #222; margin: 0 0 20px; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="receipt" style="width: 18px; height: 18px; color: #FF6B35;"></i> Order Summary
                </h3>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;"><span style="color: #888;">Subtotal (<?php echo count($cartItems); ?> items)</span><span style="font-weight: 600;">$<?php echo number_format($subtotal, 2); ?></span></div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;"><span style="color: #888;">Shipping</span><span style="font-weight: 600; color: <?php echo $shipping == 0 ? '#059669' : '#333'; ?>;"><?php echo $shipping == 0 ? 'FREE' : '$' . number_format($shipping, 2); ?></span></div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 16px;"><span style="color: #888;">Tax (10%)</span><span style="font-weight: 600;">$<?php echo number_format($tax, 2); ?></span></div>
                <div style="height: 1px; background: #eee; margin-bottom: 16px;"></div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 24px;"><span style="font-weight: 700; font-size: 1.1rem;">Total</span><span style="font-weight: 800; font-size: 1.3rem; color: #EE4D2D;">$<?php echo number_format($total, 2); ?></span></div>
                <a href="<?php echo APPURL; ?>shopping/checkout.php" style="display: block; text-align: center; padding: 16px; background: #FF6B35; color: white; font-weight: 700; border-radius: 12px; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#EE4D2D'" onmouseout="this.style.background='#FF6B35'">Proceed to Checkout</a>
            </div>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 80px 40px; background: #fff; border-radius: 16px; border: 1px solid #eee;">
            <i data-lucide="shopping-cart" style="width:48px; height:48px; color: #ddd; margin-bottom: 16px;"></i>
            <h3 style="font-size: 1.2rem; font-weight: 700; color: #555;">Your cart is empty</h3>
            <p style="color: #999; margin-bottom: 24px;">Browse our products and add items to your cart.</p>
            <a href="<?php echo APPURL; ?>" style="display: inline-block; padding: 12px 28px; background: #FF6B35; color: white; font-weight: 600; border-radius: 10px; text-decoration: none;">Start Shopping</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require '../includes/footer.php'; ?>
