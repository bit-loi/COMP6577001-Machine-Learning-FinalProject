<?php 
session_start();
require '../config/config.php';
require_once '../middleware/RateLimiter.php';

// Rate limiting: max 10 attempts per 5 minutes
RateLimiter::register();

if (isset($_SESSION['username'])){
    header('Location: '.APPURL.'index.php');
    exit;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errorMessage = '';
if (isset($_POST['register'])) {
    // CSRF Token Validation
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errorMessage = 'Invalid request. Please try again.';
    } elseif (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
        $errorMessage = 'All fields are required.';
    } else {
        $username = trim(strip_tags($_POST['username']));
        $email    = trim($_POST['email']);
        $password = $_POST['password'];

        // Validasi username
        if (strlen($username) < 3 || strlen($username) > 50) {
            $errorMessage = 'Username must be between 3 and 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errorMessage = 'Username may only contain letters, numbers, and underscores.';
        }
        // Validasi email
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Please enter a valid email address.';
        }
        // Validasi password
        elseif (strlen($password) < 8) {
            $errorMessage = 'Password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errorMessage = 'Password must contain at least one letter and one number.';
        } else {
            // Check if email already exists
            $check = $conn->prepare('SELECT id FROM users WHERE email = :email OR username = :username');
            $check->execute([':email' => $email, ':username' => $username]);
            if ($check->fetch()) {
                $errorMessage = 'An account with this email or username already exists.';
            } else {
                $insert = $conn->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
                $insert->execute([
                    ':username' => $username,
                    ':email'    => $email,
                    ':password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                ]);
                // Invalidate CSRF token after use
                unset($_SESSION['csrf_token']);
                header('Location: login.php?registered=1');
                exit;
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
  <title>Create Account — Premeditatio Malorum</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background: #050505; color: white; margin: 0; }
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus {
      -webkit-text-fill-color: white;
      -webkit-box-shadow: 0 0 0px 1000px #0a0a0a inset;
      transition: background-color 5000s ease-in-out 0s;
    }
  </style>
</head>
<body>
<div style="display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh;" class="auth-grid">

  <!-- Left: Visual Panel -->
  <div style="position: relative; overflow: hidden; background: #0a0a0a; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 48px;">
    <!-- Background image -->
    <div style="position: absolute; inset: 0; background-image: url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?w=1200&q=80'); background-size: cover; background-position: center; opacity: 0.12;"></div>
    <!-- Gradient overlay -->
    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, rgba(5,5,5,0.4) 0%, rgba(5,5,5,0.7) 100%);"></div>

    <!-- Content -->
    <div style="position: relative; z-index: 1; text-align: center; max-width: 400px;">

      <h2 style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 2rem; color: white; margin: 0 0 16px 0; line-height: 1.3;">
        Begin Your Literary Journey
      </h2>
      <p style="color: rgba(255,255,255,0.35); font-size: 0.9rem; line-height: 1.7; margin: 0 0 48px 0;">
        Join a community of passionate readers. Discover rare editions, curated collections, and books that will change how you see the world.
      </p>

      <!-- Features -->
      <div style="display: flex; flex-direction: column; gap: 16px; text-align: left;">
          <!-- Feature: Curated Collections -->
        <div style="display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
          <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
          </div>
          <div>
            <div style="font-size: 0.875rem; font-weight: 600; color: white;">Curated Collections</div>
            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.35); margin-top: 2px;">Hand-picked books across every genre</div>
          </div>
        </div>
        <!-- Feature: Fast Delivery -->
        <div style="display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
          <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="13" x="1" y="3" rx="2"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          </div>
          <div>
            <div style="font-size: 0.875rem; font-weight: 600; color: white;">Fast Delivery</div>
            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.35); margin-top: 2px;">Insured shipping worldwide</div>
          </div>
        </div>
        <!-- Feature: Expert Reviews -->
        <div style="display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 12px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
          <div style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.06);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          </div>
          <div>
            <div style="font-size: 0.875rem; font-weight: 600; color: white;">Expert Reviews</div>
            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.35); margin-top: 2px;">Trusted recommendations from bibliophiles</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Right: Form Panel -->
  <div style="display: flex; align-items: center; justify-content: center; padding: 48px; background: #050505; position: relative;">
    
    <!-- Back link -->
    <a href="<?php echo APPURL; ?>index.php" style="position: absolute; top: 32px; right: 32px; display: flex; align-items: center; gap: 6px; color: rgba(255,255,255,0.3); text-decoration: none; font-size: 0.8rem; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.3)'">
      Back to store →
    </a>

    <div style="width: 100%; max-width: 400px;">
      <!-- Logo -->
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 40px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
        <span style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 1.25rem; font-weight: 700; color: white;">Premeditatio Malorum</span>
      </div>

      <h1 style="font-size: 2rem; font-weight: 700; color: white; margin: 0 0 8px 0;">Create account</h1>
      <p style="color: rgba(255,255,255,0.4); font-size: 0.875rem; margin: 0 0 40px 0;">Join thousands of readers discovering great books.</p>

      <?php if ($errorMessage): ?>
      <div style="margin-bottom: 24px; padding: 12px 16px; border-radius: 8px; font-size: 0.875rem; color: #f87171; background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.2);">
        <?php echo htmlspecialchars($errorMessage); ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="register.php" style="display: flex; flex-direction: column; gap: 18px;">
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label for="username" style="font-size: 0.8rem; font-weight: 500; color: rgba(255,255,255,0.5);">Username</label>
          <input type="text" id="username" name="username" placeholder="johndoe" required
            style="height: 44px; width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04); padding: 0 14px; font-size: 0.875rem; color: white; outline: none; box-sizing: border-box; transition: border-color 0.2s;"
            onfocus="this.style.borderColor='rgba(255,255,255,0.3)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label for="email" style="font-size: 0.8rem; font-weight: 500; color: rgba(255,255,255,0.5);">Email address</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required
            style="height: 44px; width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04); padding: 0 14px; font-size: 0.875rem; color: white; outline: none; box-sizing: border-box; transition: border-color 0.2s;"
            onfocus="this.style.borderColor='rgba(255,255,255,0.3)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label for="password" style="font-size: 0.8rem; font-weight: 500; color: rgba(255,255,255,0.5);">Password</label>
          <input type="password" id="password" name="password" placeholder="Min. 8 characters (letters + numbers)" required minlength="8" autocomplete="new-password"
            pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,}"
            title="Must be at least 8 characters with at least one letter and one number"
            style="height: 44px; width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255,255,255,0.04); padding: 0 14px; font-size: 0.875rem; color: white; outline: none; box-sizing: border-box; transition: border-color 0.2s;"
            onfocus="this.style.borderColor='rgba(255,255,255,0.3)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
        </div>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <button name="register" type="submit"
          style="height: 44px; width: 100%; border-radius: 8px; background: white; color: #050505; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; transition: background 0.2s; margin-top: 4px;"
          onmouseover="this.style.background='#e5e5e5'" onmouseout="this.style.background='white'">
          Create account →
        </button>


        <p style="text-align: center; font-size: 0.8rem; color: rgba(255,255,255,0.25); margin-top: 8px;">
          Already have an account?
          <a href="login.php" style="color: rgba(255,255,255,0.6); text-decoration: none; font-weight: 500; margin-left: 4px; transition: color 0.2s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,0.6)'">Sign in</a>
        </p>
      </form>
    </div>
  </div>
</div>

<style>
  @media (max-width: 768px) {
    .auth-grid {
      grid-template-columns: 1fr !important;
    }
    .auth-grid > div:first-child {
      display: none !important;
    }
  }
</style>

</body>
</html>