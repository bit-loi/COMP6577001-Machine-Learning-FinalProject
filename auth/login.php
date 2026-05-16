<?php 
session_start();
require '../config/config.php';
require_once '../middleware/RateLimiter.php';

RateLimiter::login();

if (isset($_SESSION['username'])){
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header('Location: '.APPURL.'admin/');
    } else {
        header('Location: '.APPURL.'index.php');
    }
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errorMessage = '';
if (isset($_POST['login'])) {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMessage = 'Invalid request. Please try again.';
    } elseif (empty($_POST['email']) || empty($_POST['password'])) {
        $errorMessage = 'All fields are required.';
    } elseif (strlen($_POST['password']) < 8) {
        $errorMessage = 'Invalid email or password.';
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Invalid email or password.';
        } else {
            $login = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = :email LIMIT 1"); 
            $login->execute(['email' => $email]);
            $fetch = $login->fetch(PDO::FETCH_ASSOC);
            if ($fetch && password_verify($password, $fetch['password'])) {
                session_regenerate_id(true);
                $_SESSION['username'] = $fetch['username'];
                $_SESSION['user_id']  = $fetch['id'];
                $_SESSION['is_admin'] = ($fetch['role'] === 'admin') ? 1 : 0;
                $_SESSION['role']     = $fetch['role'];
                unset($_SESSION['csrf_token']);
                if ($fetch['role'] === 'admin') {
                    header('Location: '.APPURL.'admin/');
                } else {
                    $redirect = $_GET['redirect'] ?? '';
                    $redirect = urldecode($redirect);
                    if (!empty($redirect) && str_starts_with($redirect, '/shopmart/')) {
                        header('Location: ' . APPURL . ltrim(str_replace('/shopmart/', '', $redirect), '/'));
                    } else {
                        header('Location: '.APPURL.'index.php');
                    }
                }
                exit;
            } else {
                $errorMessage = 'Invalid email or password.';
                usleep(random_int(100000, 300000));
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign In — Shopmart</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Inter', sans-serif; background: #f5f5f5; color: #333; margin: 0; }
    input:-webkit-autofill { -webkit-text-fill-color: #333; -webkit-box-shadow: 0 0 0px 1000px #fff inset; }
  </style>
</head>
<body>
<div style="display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh;" class="auth-grid">
  <!-- Left: Form -->
  <div style="display: flex; align-items: center; justify-content: center; padding: 48px; background: #fff;">
    <div style="width: 100%; max-width: 400px;">
      <a href="<?php echo APPURL; ?>" style="display: flex; align-items: center; gap: 8px; margin-bottom: 48px; text-decoration: none;">
        <span style="font-size: 1.3rem; font-weight: 800; color: #FF6B35;">🛒 Shopmart</span>
      </a>
      <h1 style="font-size: 2rem; font-weight: 800; color: #222; margin: 0 0 8px;">Welcome back</h1>
      <p style="color: #999; font-size: 0.875rem; margin: 0 0 32px;">Sign in to your Shopmart account.</p>

      <?php if ($errorMessage): ?>
      <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 10px; font-size: 0.85rem; color: #DC2626; background: #FEE2E2;"><?php echo htmlspecialchars($errorMessage); ?></div>
      <?php endif; ?>

      <?php if (isset($_GET['registered'])): ?>
      <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 10px; font-size: 0.85rem; color: #059669; background: #D1FAE5;">Account created! Please sign in.</div>
      <?php endif; ?>

      <form method="POST" action="login.php" style="display: flex; flex-direction: column; gap: 18px;">
        <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Email</label>
        <input type="email" name="email" placeholder="you@example.com" required autocomplete="email" style="width: 100%; height: 48px; padding: 0 16px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none; transition: border-color 0.2s; box-sizing: border-box;" onfocus="this.style.borderColor='#FF6B35'" onblur="this.style.borderColor='#ddd'"></div>
        <div><label style="font-size: 0.8rem; font-weight: 600; color: #555; display: block; margin-bottom: 6px;">Password</label>
        <input type="password" name="password" placeholder="••••••••" required minlength="8" autocomplete="current-password" style="width: 100%; height: 48px; padding: 0 16px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; outline: none; transition: border-color 0.2s; box-sizing: border-box;" onfocus="this.style.borderColor='#FF6B35'" onblur="this.style.borderColor='#ddd'"></div>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <button name="login" type="submit" style="height: 48px; background: #FF6B35; color: white; font-weight: 700; font-size: 0.9rem; border: none; border-radius: 10px; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#EE4D2D'" onmouseout="this.style.background='#FF6B35'">Sign In</button>
        <p style="text-align: center; font-size: 0.85rem; color: #999;">Don't have an account? <a href="register.php" style="color: #FF6B35; font-weight: 600; text-decoration: none;">Create one</a></p>
      </form>
    </div>
  </div>
  <!-- Right: Visual -->
  <div style="background: linear-gradient(135deg, #FF6B35 0%, #EE4D2D 100%); display: flex; align-items: center; justify-content: center; padding: 48px;">
    <div style="text-align: center; max-width: 380px;">
      <div style="font-size: 4rem; margin-bottom: 24px;">🛍️</div>
      <h2 style="font-size: 1.8rem; font-weight: 800; color: white; margin: 0 0 12px;">Shop Smarter</h2>
      <p style="color: rgba(255,255,255,0.8); font-size: 1rem; line-height: 1.7;">Thousands of products from trusted sellers. Fast delivery, easy returns, and amazing deals.</p>
      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 40px;">
        <div><div style="font-size: 1.5rem; font-weight: 800; color: white;">1K+</div><div style="font-size: 0.7rem; color: rgba(255,255,255,0.7);">Products</div></div>
        <div><div style="font-size: 1.5rem; font-weight: 800; color: white;">500+</div><div style="font-size: 0.7rem; color: rgba(255,255,255,0.7);">Sellers</div></div>
        <div><div style="font-size: 1.5rem; font-weight: 800; color: white;">4.8★</div><div style="font-size: 0.7rem; color: rgba(255,255,255,0.7);">Rating</div></div>
      </div>
    </div>
  </div>
</div>
<style>@media (max-width: 768px) { .auth-grid { grid-template-columns: 1fr !important; } .auth-grid > div:last-child { display: none !important; } }</style>
</body>
</html>
