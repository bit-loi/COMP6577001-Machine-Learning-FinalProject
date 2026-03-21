<?php 
session_start();
require '../config/config.php';
require_once '../middleware/RateLimiter.php';

// Rate limiting: max 5 attempts per 15 minutes
RateLimiter::login();

if (isset($_SESSION['username'])){
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header('Location: '.APPURL.'admin/');
    } else {
        header('Location: '.APPURL.'index.php');
    }
    exit;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errorMessage = '';
if (isset($_POST['login'])) {
    // CSRF Token Validation
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMessage = 'Invalid request. Please try again.';
    } elseif (empty($_POST['email']) || empty($_POST['password'])) {
        $errorMessage = 'All fields are required.';
    } elseif (strlen($_POST['password']) < 8) {
        $errorMessage = 'Invalid email or password.';
    } else {
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Invalid email or password.';
        } else {
            $login = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE email = :email LIMIT 1"); 
            $login->execute(['email' => $email]);
            $fetch = $login->fetch(PDO::FETCH_ASSOC);

            if ($fetch && password_verify($password, $fetch['password'])) {
                // Regenerate session ID after successful login (anti session fixation)
                session_regenerate_id(true);
                $_SESSION['username'] = $fetch['username'];
                $_SESSION['user_id']  = $fetch['id'];
                $_SESSION['is_admin'] = $fetch['is_admin'];

                // Invalidate old CSRF token
                unset($_SESSION['csrf_token']);

                // Admin goes to admin dashboard, regular users go to homepage
                if ($fetch['is_admin'] == 1) {
                    header('Location: '.APPURL.'admin/');
                } else {
                    // Validate redirect URL (prevent open redirect)
                    $redirect = $_GET['redirect'] ?? '';
                    $redirect = urldecode($redirect);
                    if (!empty($redirect) && str_starts_with($redirect, '/bookstore/')) {
                        header('Location: ' . APPURL . ltrim(str_replace('/bookstore/', '', $redirect), '/'));
                    } else {
                        header('Location: '.APPURL.'index.php');
                    }
                }
                exit;
            } else {
                // Generic message — jangan bocorkan apakah email ada atau tidak
                $errorMessage = 'Invalid email or password.';
                // Tambah delay kecil untuk mencegah timing attack
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
  <title>Sign In — Premeditatio Malorum</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: #050505; color: white; margin: 0; }
    input:-webkit-autofill, input:-webkit-autofill:hover, input:-webkit-autofill:focus {
      -webkit-text-fill-color: white;
      -webkit-box-shadow: 0 0 0px 1000px #0a0a0a inset;
      transition: background-color 5000s ease-in-out 0s;
    }
  </style>
</head>
<body>
<div style="display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh;" class="auth-grid">

  <!-- Left: Form Panel -->
  <div style="display: flex; align-items: center; justify-content: center; padding: 48px; background: #050505; position: relative; z-index: 1;">
    
    <a href="<?php echo APPURL; ?>index.php" style="position: absolute; top: 32px; left: 32px; display: flex; align-items: center; gap: 6px; color: rgba(255,255,255,0.3); text-decoration: none; font-size: 0.8rem; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.3)'">
      ← Back to store
    </a>

    <div style="width: 100%; max-width: 400px;">
      <!-- Logo -->
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 40px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        <span style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 1.1rem; font-weight: 700; color: white; letter-spacing: 0.02em;">Premeditatio Malorum</span>
      </div>

      <h1 style="font-size: 2rem; font-weight: 700; color: white; margin: 0 0 8px 0;">Welcome back</h1>
      <p style="color: rgba(255,255,255,0.4); font-size: 0.875rem; margin: 0 0 40px 0;">Sign in to continue your reading journey.</p>

      <?php if ($errorMessage): ?>
      <div style="margin-bottom: 24px; padding: 12px 16px; border-radius: 8px; font-size: 0.875rem; color: #f87171; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);">
        <?php echo htmlspecialchars($errorMessage); ?>
      </div>
      <?php endif; ?>

      <?php if (isset($_GET['registered'])): ?>
      <div style="margin-bottom: 24px; padding: 12px 16px; border-radius: 8px; font-size: 0.875rem; color: #4ade80; background: rgba(74,222,128,0.08); border: 1px solid rgba(74,222,128,0.2);">
        Account created successfully! Please sign in.
      </div>
      <?php endif; ?>

      <form method="POST" action="login.php" style="display: flex; flex-direction: column; gap: 20px;">
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label for="email" style="font-size: 0.8rem; font-weight: 500; color: rgba(255,255,255,0.5);">Email address</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required autocomplete="email"
            style="height: 44px; width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04); padding: 0 14px; font-size: 0.875rem; color: white; outline: none; box-sizing: border-box; transition: border-color 0.2s;"
            onfocus="this.style.borderColor='rgba(255,255,255,0.3)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <label for="password" style="font-size: 0.8rem; font-weight: 500; color: rgba(255,255,255,0.5);">Password</label>
            <a href="#" style="font-size: 0.75rem; color: rgba(255,255,255,0.25); text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.25)'">Forgot password?</a>
          </div>
          <input type="password" id="password" name="password" placeholder="••••••••" required minlength="8" autocomplete="current-password"
            style="height: 44px; width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04); padding: 0 14px; font-size: 0.875rem; color: white; outline: none; box-sizing: border-box; transition: border-color 0.2s;"
            onfocus="this.style.borderColor='rgba(255,255,255,0.3)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
        </div>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <button name="login" type="submit"
          style="height: 44px; width: 100%; border-radius: 8px; background: white; color: #050505; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; transition: background 0.2s; margin-top: 4px;"
          onmouseover="this.style.background='#e5e5e5'" onmouseout="this.style.background='white'">
          Sign in →
        </button>


        <p style="text-align: center; font-size: 0.8rem; color: rgba(255,255,255,0.25); margin-top: 8px;">
          Don't have an account?
          <a href="register.php" style="color: rgba(255,255,255,0.6); text-decoration: none; font-weight: 500; margin-left: 4px; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">Create one</a>
        </p>
      </form>
    </div>
  </div>

  <!-- Right: Visual Panel -->
  <div style="position: relative; overflow: hidden; background: #0a0a0a; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px;">
    <div style="position: absolute; inset: 0; background-image: url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=1200&q=80'); background-size: cover; background-position: center; opacity: 0.12;"></div>
    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(5,5,5,0.7) 0%, rgba(5,5,5,0.3) 100%);"></div>

    <div style="position: relative; z-index: 1; text-align: center; max-width: 400px;">
      <div style="font-family: monospace; font-size: 0.7rem; letter-spacing: 0.3em; color: rgba(255,255,255,0.25); text-transform: uppercase; margin-bottom: 32px;">Premeditatio Malorum</div>
      <blockquote style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 1.5rem; color: white; line-height: 1.6; margin: 0 0 24px 0; opacity: 0.9;">
        "A reader lives a thousand lives before he dies."
      </blockquote>
      <p style="font-size: 0.8rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 0.15em;">— George R.R. Martin</p>

      <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-top: 48px; padding-top: 48px; border-top: 1px solid rgba(255,255,255,0.08);">
        <div><div style="font-size: 1.5rem; font-weight: 700; color: white;">12K+</div><div style="font-size: 0.7rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px;">Books</div></div>
        <div><div style="font-size: 1.5rem; font-weight: 700; color: white;">50K+</div><div style="font-size: 0.7rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px;">Readers</div></div>
        <div><div style="font-size: 1.5rem; font-weight: 700; color: white;">4.9★</div><div style="font-size: 0.7rem; color: rgba(255,255,255,0.3); text-transform: uppercase; letter-spacing: 0.1em; margin-top: 4px;">Rating</div></div>
      </div>
    </div>
  </div>
</div>

<style>
  @media (max-width: 768px) {
    .auth-grid { grid-template-columns: 1fr !important; }
    .auth-grid > div:last-child { display: none !important; }
  }
</style>
</body>
</html>
