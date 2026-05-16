<?php
session_start();
require '../config/config.php';
require '../middleware/auth.php';

$userId = $_SESSION['user_id'];
$successMsg = '';
$errorMsg = '';

$conn->exec("CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('topup','payment','earning','refund') NOT NULL DEFAULT 'topup',
    amount DECIMAL(10,2) NOT NULL,
    balance_after DECIMAL(10,2) DEFAULT NULL,
    description VARCHAR(255) DEFAULT NULL,
    reference VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$stmtUser = $conn->prepare("SELECT wallet_balance, username, first_name, last_name FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch(PDO::FETCH_OBJ);
$walletBalance = $user ? (float)$user->wallet_balance : 0;
$accountName = $user ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) : '';
if ($accountName === '') {
    $accountName = ucwords($user->username ?? $_SESSION['username'] ?? 'User');
}
$isAdminWallet = ($walletBalance < 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['topup'])) {
    if ($isAdminWallet) {
        $errorMsg = 'Akun admin memiliki saldo unlimited dan tidak perlu top-up.';
    } else {
        $amount = round((float)($_POST['amount'] ?? 0), 2);
        $method = $_POST['method'] ?? 'card';
        if ($amount < 5) {
            $errorMsg = 'Minimum top-up adalah $5.00';
        } elseif ($amount > 5000) {
            $errorMsg = 'Maksimum top-up adalah $5,000.00';
        } else {
            try {
                $conn->beginTransaction();
                $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?")->execute([$amount, $userId]);
                $newBalance = $walletBalance + $amount;
                $ref = 'TOP-' . strtoupper(substr(uniqid(), -8));
                $desc = 'Top-up via ' . ucfirst($method);
                $conn->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_after, description, reference) VALUES (?, 'topup', ?, ?, ?, ?)")
                     ->execute([$userId, $amount, $newBalance, $desc, $ref]);
                $conn->commit();
                $walletBalance = $newBalance;
                $successMsg = 'Berhasil menambahkan $' . number_format($amount, 2) . ' ke wallet Anda!';
            } catch (Exception $e) {
                $conn->rollBack();
                $errorMsg = 'Top-up gagal. Silakan coba lagi.';
            }
        }
    }
}

$transactions = [];
try {
    $stmtTx = $conn->prepare("SELECT * FROM wallet_transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $stmtTx->execute([$userId]);
    $transactions = $stmtTx->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $transactions = [];
}

$currentPage = 'wallet';
include '../includes/header.php';
include 'includes/styles.php';
?>

<div class="account-page">
    <div class="account-wrap">
        <nav class="account-breadcrumb">
            <a href="<?php echo APPURL; ?>">Home</a>
            <span>›</span>
            <strong>Wallet & Topup</strong>
        </nav>

        <div class="account-layout">
            <?php include 'includes/sidebar.php'; ?>

            <div style="display:flex;flex-direction:column;gap:20px;">
                <?php if ($successMsg): ?>
                <div class="alert-success">
                    <i data-lucide="check-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                    <?php echo htmlspecialchars($successMsg); ?>
                </div>
                <?php endif; ?>
                <?php if ($errorMsg): ?>
                <div class="alert-error">
                    <i data-lucide="alert-circle" style="width:18px;height:18px;flex-shrink:0;"></i>
                    <?php echo htmlspecialchars($errorMsg); ?>
                </div>
                <?php endif; ?>

                <!-- Balance hero -->
                <div class="wallet-hero">
                    <div class="wallet-hero-inner">
                        <div class="wallet-hero-label">
                            <i data-lucide="wallet" style="width:17px;height:17px;"></i>
                            Saldo Shopmart Wallet
                        </div>
                        <div class="wallet-hero-amount">
                            <?php if ($isAdminWallet): ?>
                                <span style="display:flex;align-items:center;gap:10px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 12c-2-2.67-4-4-6-4a4 4 0 1 0 0 8c2 0 4-1.33 6-4Zm0 0c2 2.67 4 4 6 4a4 4 0 1 0 0-8c-2 0-4 1.33-6 4Z"/></svg>
                                    Unlimited
                                </span>
                            <?php else: ?>
                                $<?php echo number_format($walletBalance, 2); ?>
                            <?php endif; ?>
                        </div>
                        <div class="wallet-hero-sub">Bayar checkout lebih cepat dengan saldo wallet Anda</div>
                    </div>
                </div>

                <?php if (!$isAdminWallet): ?>
                <div class="account-card account-card-pad">
                    <h2 class="account-card-title">
                        <span class="icon-wrap"><i data-lucide="plus-circle" style="width:18px;height:18px;"></i></span>
                        Top Up Wallet
                    </h2>
                    <form method="POST" id="topup-form">
                        <input type="hidden" name="topup" value="1">
                        <div style="margin-bottom:22px;">
                            <label style="font-size:0.8rem;font-weight:700;color:#444;display:block;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.04em;">Pilih Nominal</label>
                            <div style="display:flex;flex-wrap:wrap;gap:10px;" id="amount-buttons">
                                <?php foreach ([10, 25, 50, 100, 200] as $preset): ?>
                                <button type="button" data-amount="<?php echo $preset; ?>" class="amount-btn">$<?php echo $preset; ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div style="margin-bottom:22px;">
                            <label style="font-size:0.8rem;font-weight:700;color:#444;display:block;margin-bottom:8px;">Atau masukkan nominal</label>
                            <div style="position:relative;max-width:300px;">
                                <span style="position:absolute;left:16px;top:50%;transform:translateY(-50%);font-weight:800;color:#888;font-size:1.1rem;">$</span>
                                <input type="number" name="amount" id="topup-amount" min="5" max="5000" step="0.01" placeholder="0.00" required
                                    style="width:100%;padding:14px 14px 14px 32px;border:2px solid #e8e8e8;border-radius:12px;font-size:1.1rem;font-weight:700;outline:none;font-family:inherit;box-shadow:inset 0 2px 4px rgba(0,0,0,0.04);"
                                    onfocus="this.style.borderColor='#EE4D2D';this.style.boxShadow='0 0 0 3px rgba(238,77,45,0.12)'"
                                    onblur="this.style.borderColor='#e8e8e8';this.style.boxShadow='inset 0 2px 4px rgba(0,0,0,0.04)'">
                            </div>
                            <p style="font-size:0.75rem;color:#999;margin-top:8px;">Min $5 — Maks $5,000</p>
                        </div>
                        <div style="margin-bottom:28px;">
                            <label style="font-size:0.8rem;font-weight:700;color:#444;display:block;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.04em;">Metode Pembayaran</label>
                            <div style="display:flex;flex-wrap:wrap;gap:12px;">
                                <?php foreach (['card' => ['Credit Card', 'credit-card'], 'bank' => ['Bank Transfer', 'landmark'], 'ewallet' => ['E-Wallet', 'smartphone']] as $val => [$label, $icon]): ?>
                                <label class="method-card">
                                    <input type="radio" name="method" value="<?php echo $val; ?>" <?php echo $val === 'card' ? 'checked' : ''; ?> style="accent-color:#EE4D2D;">
                                    <i data-lucide="<?php echo $icon; ?>" style="width:18px;height:18px;color:#EE4D2D;"></i>
                                    <?php echo $label; ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">
                            <i data-lucide="zap" style="width:16px;height:16px;display:inline;vertical-align:middle;margin-right:6px;"></i>
                            Top Up Sekarang
                        </button>
                        <p style="font-size:0.75rem;color:#aaa;margin-top:14px;display:flex;align-items:center;gap:6px;">
                            <i data-lucide="info" style="width:13px;height:13px;"></i>
                            Mode demo: saldo ditambahkan langsung tanpa pembayaran nyata.
                        </p>
                    </form>
                </div>
                <?php endif; ?>

                <div class="account-card account-card-pad">
                    <h2 class="account-card-title">
                        <span class="icon-wrap"><i data-lucide="history" style="width:18px;height:18px;"></i></span>
                        Riwayat Transaksi
                    </h2>
                    <?php if ($transactions): ?>
                        <?php foreach ($transactions as $tx):
                            $isCredit = in_array($tx->type, ['topup', 'earning', 'refund']);
                            $typeLabels = ['topup' => 'Top Up', 'payment' => 'Pembayaran', 'earning' => 'Pendapatan', 'refund' => 'Refund'];
                        ?>
                        <div class="tx-row">
                            <div style="display:flex;align-items:center;gap:14px;min-width:0;">
                                <div class="tx-icon <?php echo $isCredit ? 'credit' : 'debit'; ?>">
                                    <i data-lucide="<?php echo $isCredit ? 'arrow-down-left' : 'arrow-up-right'; ?>" style="width:20px;height:20px;color:<?php echo $isCredit ? '#059669' : '#dc2626'; ?>;"></i>
                                </div>
                                <div>
                                    <div style="font-size:0.92rem;font-weight:700;color:#111;"><?php echo htmlspecialchars($tx->description ?? ($typeLabels[$tx->type] ?? 'Transaksi')); ?></div>
                                    <div style="font-size:0.75rem;color:#999;margin-top:3px;">
                                        <?php echo date('d M Y, H:i', strtotime($tx->created_at)); ?>
                                        <?php if ($tx->reference): ?> · <?php echo htmlspecialchars($tx->reference); ?><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div style="text-align:right;flex-shrink:0;">
                                <div style="font-size:1rem;font-weight:800;color:<?php echo $isCredit ? '#059669' : '#dc2626'; ?>;">
                                    <?php echo $isCredit ? '+' : '-'; ?>$<?php echo number_format(abs($tx->amount), 2); ?>
                                </div>
                                <?php if ($tx->balance_after !== null): ?>
                                <div style="font-size:0.72rem;color:#aaa;margin-top:3px;">Saldo: $<?php echo number_format($tx->balance_after, 2); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i data-lucide="receipt" style="width:36px;height:36px;color:#EE4D2D;"></i>
                        </div>
                        <h3 style="font-size:1rem;font-weight:700;color:#333;margin:0 0 8px;">Belum ada transaksi</h3>
                        <p style="font-size:0.88rem;color:#999;margin:0;">Top up wallet Anda untuk memulai.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.amount-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.getElementById('topup-amount').value = this.dataset.amount;
    });
});
</script>

<?php require '../includes/footer.php'; ?>
