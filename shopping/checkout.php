<?php
session_start();
require '../config/config.php';
require '../middleware/auth.php';
require '../includes/product-image.php';

// Fetch cart for checkout
$stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.discount_price, p.image, p.brand FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $cartItems) {
    try {
        $userId = $_SESSION['user_id'];
        $orderNumber = 'SM-' . strtoupper(uniqid());
        
        $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_address, shipping_city, shipping_zip, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $stmt->execute([$userId, $orderNumber, $total, $_POST['address'] ?? '', $_POST['city'] ?? '', $_POST['zip'] ?? '', $_POST['payment'] ?? 'card']);
        $orderId = $conn->lastInsertId();

        // Handle Wallet Payment
        if (($_POST['payment'] ?? '') === 'wallet') {
            $stmtWallet = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
            $stmtWallet->execute([$userId]);
            $wallet = $stmtWallet->fetchColumn();
            
            if ($wallet >= 0 && $wallet < $total) {
                throw new Exception("Insufficient wallet balance. You have $" . number_format($wallet, 2));
            }
            
            if ($wallet >= 0) {
                // Deduct balance
                $conn->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ?")->execute([$total, $userId]);
            }
        }

        foreach ($cartItems as $item) {
            $itemPrice = ($item->discount_price && $item->discount_price > 0) ? $item->discount_price : $item->price;
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$orderId, $item->product_id, $item->name, $itemPrice, $item->quantity, $itemPrice * $item->quantity]);
            
            // If item has a seller, credit their wallet
            $stmtSeller = $conn->prepare("SELECT seller_id FROM products WHERE id = ?");
            $stmtSeller->execute([$item->product_id]);
            $sellerId = $stmtSeller->fetchColumn();
            if ($sellerId) {
                $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ? AND wallet_balance >= 0")->execute([$itemPrice * $item->quantity, $sellerId]);
            }
        }

        // Clear cart
        $conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$userId]);

        $_SESSION['checkout_success'] = "Order #$orderNumber placed successfully!";
        header("Location: checkout.php?success=1");
        exit;
    } catch (Exception $e) {
        $errorMsg = $e->getMessage();
    } catch (PDOException $e) {
        $errorMsg = "Order failed. Please try again.";
    }
}
?>
<?php include '../includes/header.php'; ?>

<div style="background: #f5f5f5; min-height: 60vh; padding: 40px 0;">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
        <h1 style="font-size: 1.5rem; font-weight: 800; color: #222; margin-bottom: 32px;">Checkout</h1>

        <?php if (isset($_GET['success'])): ?>
        <div style="text-align: center; padding: 60px 40px; background: #fff; border-radius: 16px; border: 1px solid #eee;">
            <div style="font-size: 3rem; margin-bottom: 16px;">🎉</div>
            <h2 style="font-size: 1.5rem; font-weight: 800; color: #059669; margin-bottom: 8px;">Order Placed!</h2>
            <p style="color: #666; margin-bottom: 24px;"><?php echo htmlspecialchars($_SESSION['checkout_success'] ?? 'Your order is being processed.'); ?></p>
            <?php unset($_SESSION['checkout_success']); ?>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="<?php echo APPURL; ?>account/purchases.php" style="padding: 12px 24px; background: #fff; color: #EE4D2D; font-weight: 600; border-radius: 10px; text-decoration: none; border: 2px solid #EE4D2D;">Lihat Pesanan Saya</a>
                <a href="<?php echo APPURL; ?>" style="padding: 12px 24px; background: #FF6B35; color: white; font-weight: 600; border-radius: 10px; text-decoration: none;">Continue Shopping</a>
            </div>
        </div>
        <?php elseif(!$cartItems): ?>
        <div style="text-align: center; padding: 60px 40px; background: #fff; border-radius: 16px; border: 1px solid #eee;">
            <h3 style="color: #555;">Your cart is empty</h3>
            <a href="<?php echo APPURL; ?>" style="color: #FF6B35; text-decoration: none; font-weight: 600;">← Go shopping</a>
        </div>
        <?php else: ?>
        <?php if (isset($errorMsg)): ?>
            <div style="padding: 16px; background: #FEE2E2; color: #DC2626; border-radius: 10px; margin-bottom: 20px;"><?php echo $errorMsg; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div style="display: grid; grid-template-columns: 1fr 380px; gap: 24px; align-items: start;">
                <div>
                    <!-- Shipping -->
                    <div style="background: #fff; border-radius: 16px; border: 1px solid #eee; padding: 24px; margin-bottom: 20px;">
                        <h3 style="font-size: 1rem; font-weight: 700; margin: 0 0 20px;">Shipping Address</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                            <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">First Name</label><input type="text" name="first_name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none;"></div>
                            <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Last Name</label><input type="text" name="last_name" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none;"></div>
                        </div>
                        <div style="margin-top: 16px;"><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Address</label><input type="text" name="address" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none;"></div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 16px;">
                            <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">City</label><input type="text" name="city" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none;"></div>
                            <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">State</label><input type="text" name="state" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none;"></div>
                            <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Zip Code</label><input type="text" name="zip" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none;"></div>
                        </div>
                    </div>
                    <!-- Payment -->
                    <div style="background: #fff; border-radius: 16px; border: 1px solid #eee; padding: 24px;">
                        <h3 style="font-size: 1rem; font-weight: 700; margin: 0 0 20px;">Payment Method</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                            <label style="display: flex; align-items: center; gap: 8px; padding: 16px; border: 2px solid #FF6B35; border-radius: 12px; cursor: pointer; background: #FFF4ED;">
                                <input type="radio" name="payment" value="wallet" checked> 
                                <div>
                                    <span style="font-weight: 600; font-size: 0.85rem; display:block;">My Wallet</span>
                                    <span style="font-size: 0.75rem; color:#666; display:flex; align-items:center; gap:4px;">Balance: <?php 
                                        $stmtWallet = $conn->prepare("SELECT wallet_balance FROM users WHERE id = ?");
                                        $stmtWallet->execute([$_SESSION['user_id']]);
                                        $bal = $stmtWallet->fetchColumn();
                                        if ($bal < 0) {
                                            echo '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12c-2-2.67-4-4-6-4a4 4 0 1 0 0 8c2 0 4-1.33 6-4Zm0 0c2 2.67 4 4 6 4a4 4 0 1 0 0-8c-2 0-4 1.33-6 4Z"/></svg> (Admin)';
                                        } else {
                                            echo '$'.number_format($bal, 2);
                                        }
                                    ?></span>
                                </div>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; padding: 16px; border: 1px solid #ddd; border-radius: 12px; cursor: pointer;">
                                <input type="radio" name="payment" value="card"> <span style="font-weight: 600; font-size: 0.85rem;">Credit Card</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; padding: 16px; border: 1px solid #ddd; border-radius: 12px; cursor: pointer;">
                                <input type="radio" name="payment" value="cod"> <span style="font-weight: 600; font-size: 0.85rem;">COD</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div style="background: #fff; border-radius: 16px; border: 1px solid #eee; padding: 24px; position: sticky; top: 100px;">
                    <h3 style="font-size: 1rem; font-weight: 700; margin: 0 0 20px;">Order Summary</h3>
                    <?php foreach($cartItems as $item):
                        $itemPrice = ($item->discount_price && $item->discount_price > 0) ? $item->discount_price : $item->price;
                    ?>
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                        <div style="width: 48px; height: 48px; border-radius: 8px; overflow: hidden; background: #f9f9f9; flex-shrink: 0;">
                            <?php echo getProductImage($item, '96x96', '', ['style' => 'width:100%; height:100%; object-fit:cover;']); ?>
                        </div>
                        <div style="flex: 1;"><div style="font-size: 0.8rem; font-weight: 600; color: #333;"><?php echo htmlspecialchars($item->name); ?></div><div style="font-size: 0.7rem; color: #999;">Qty: <?php echo $item->quantity; ?></div></div>
                        <div style="font-weight: 600; font-size: 0.85rem;">$<?php echo number_format($itemPrice * $item->quantity, 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                    <div style="height: 1px; background: #eee; margin: 16px 0;"></div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;"><span style="color: #888; font-size: 0.85rem;">Subtotal</span><span style="font-weight: 600;">$<?php echo number_format($subtotal, 2); ?></span></div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;"><span style="color: #888; font-size: 0.85rem;">Shipping</span><span style="font-weight: 600; color: <?php echo $shipping == 0 ? '#059669' : '#333'; ?>;"><?php echo $shipping == 0 ? 'FREE' : '$' . number_format($shipping, 2); ?></span></div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 16px;"><span style="color: #888; font-size: 0.85rem;">Tax</span><span style="font-weight: 600;">$<?php echo number_format($tax, 2); ?></span></div>
                    <div style="height: 1px; background: #eee; margin-bottom: 16px;"></div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 24px;"><span style="font-weight: 700; font-size: 1.1rem;">Total</span><span style="font-weight: 800; font-size: 1.3rem; color: #EE4D2D;">$<?php echo number_format($total, 2); ?></span></div>
                    <button type="submit" style="display: block; width: 100%; padding: 16px; background: #FF6B35; color: white; font-weight: 700; font-size: 0.9rem; border: none; border-radius: 12px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#EE4D2D'" onmouseout="this.style.background='#FF6B35'">Place Order</button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require '../includes/footer.php'; ?>
