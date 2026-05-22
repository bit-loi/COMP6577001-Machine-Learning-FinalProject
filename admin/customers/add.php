<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../config/config.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (empty($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid security token. Please refresh the page.';
    } else {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $role = $_POST['role'] ?? 'customer';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Username, email, and password are required.";
    } else {
        // Check if username or email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Username or email already exists.";
        } else {
            try {
                $hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $isAdmin = ($role === 'admin') ? 1 : 0;
                $wallet = ($role === 'admin') ? -1.00 : 500.00;
                
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, first_name, last_name, role, is_admin, wallet_balance) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashed, $firstName, $lastName, $role, $isAdmin, $wallet]);
                
                $success = "User successfully added!";
            } catch (PDOException $e) {
                error_log('Admin add user error: ' . $e->getMessage());
                $error = "Failed to add user. Please try again.";
            }
        }
    }
    } // end CSRF check
}

$pageTitle = 'Add New User';
$pageDescription = 'Create a new customer or administrator account';
require_once '../includes/header.php';
?>

<div style="background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; padding: 24px; max-width: 600px;">
    
    <?php if($error): ?>
        <div style="background: #fef2f2; color: #ef4444; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; font-weight: 600;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div style="background: #f0fdf4; color: #16a34a; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.85rem; font-weight: 600;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Username *</label>
                <input type="text" name="username" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.9rem;" placeholder="johndoe">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Email Address *</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.9rem;" placeholder="john@example.com">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">First Name</label>
                <input type="text" name="first_name" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.9rem;" placeholder="John">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Last Name</label>
                <input type="text" name="last_name" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.9rem;" placeholder="Doe">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Password *</label>
                <input type="password" name="password" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.9rem;">
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: #475569; margin-bottom: 6px;">Role</label>
                <select name="role" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; outline: none; font-size: 0.9rem; background: #fff;">
                    <option value="customer">Customer</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 12px;">
            <button type="submit" style="background: #EE4D2D; color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#D74226'" onmouseout="this.style.background='#EE4D2D'">
                Create User
            </button>
            <a href="index.php" style="background: #f1f5f9; color: #475569; padding: 10px 24px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; text-decoration: none; display: inline-block; transition: background 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                Cancel
            </a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
