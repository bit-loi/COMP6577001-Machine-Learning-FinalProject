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
  <title>Create Account — Shopmart</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body { font-family: 'Inter', sans-serif; background: #f5f5f5; color: #333; margin: 0; }
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus {
      -webkit-text-fill-color: #333;
      -webkit-box-shadow: 0 0 0px 1000px #fff inset;
      transition: background-color 5000s ease-in-out 0s;
    }
  </style>
</head>
<body>
<div style="display: grid; grid-template-columns: 1fr 1fr; min-height: 100vh;" class="auth-grid">

  <!-- Left: Visual Panel -->
  <div style="background: linear-gradient(135deg, #FF6B35 0%, #EE4D2D 100%); display: flex; align-items: center; justify-content: center; padding: 48px;">
    <div style="text-align: center; max-width: 380px;">
      <div style="font-size: 4rem; margin-bottom: 24px;">🛒</div>
      <h2 style="font-size: 1.8rem; font-weight: 800; color: white; margin: 0 0 12px;">Join Shopmart</h2>
      <p style="color: rgba(255,255,255,0.8); font-size: 1rem; line-height: 1.7;">Create your account and start shopping with thousands of products at great prices.</p>
    </div>
  </div>

  <!-- Right: Form Panel -->
  <div style="display: flex; align-items: center; justify-content: center; padding: 48px; background: #fff; position: relative;">
    
    <!-- Back link -->
    <a href="<?php echo APPURL; ?>index.php" style="position: absolute; top: 32px; right: 32px; display: flex; align-items: center; gap: 6px; color: #aaa; text-decoration: none; font-size: 0.8rem; transition: color 0.2s;" onmouseover="this.style.color='#FF6B35'" onmouseout="this.style.color='#aaa'">
      Back to store →
    </a>

    <div style="width: 100%; max-width: 400px;">
      <!-- Logo -->
      <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 40px;">
        <span style="font-size: 1.3rem; font-weight: 800; color: #FF6B35;">🛒 Shopmart</span>
      </div>

      <h1 style="font-size: 2rem; font-weight: 800; color: #222; margin: 0 0 8px 0;">Create account</h1>
      <p style="color: #999; font-size: 0.875rem; margin: 0 0 32px 0;">Join Shopmart and start shopping today.</p>

      <?php if ($errorMessage): ?>
      <div style="margin-bottom: 20px; padding: 12px 16px; border-radius: 10px; font-size: 0.85rem; color: #DC2626; background: #FEE2E2;">
        <?php echo htmlspecialchars($errorMessage); ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="register.php" style="display: flex; flex-direction: column; gap: 18px;">
        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label for="username" style="font-size: 0.8rem; font-weight: 600; color: #555;">Username</label>
          <input type="text" id="username" name="username" placeholder="johndoe" required
            style="height: 48px; width: 100%; border-radius: 10px; border: 1px solid #ddd; background: #fff; padding: 0 16px; font-size: 0.9rem; color: #333; outline: none; box-sizing: border-box; transition: border-color 0.2s;"
            onfocus="this.style.borderColor='#FF6B35'" onblur="this.style.borderColor='#ddd'">
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label for="email" style="font-size: 0.8rem; font-weight: 600; color: #555;">Email address</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required
            style="height: 48px; width: 100%; border-radius: 10px; border: 1px solid #ddd; background: #fff; padding: 0 16px; font-size: 0.9rem; color: #333; outline: none; box-sizing: border-box; transition: border-color 0.2s;"
            onfocus="this.style.borderColor='#FF6B35'" onblur="this.style.borderColor='#ddd'">
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px;">
          <label for="password" style="font-size: 0.8rem; font-weight: 600; color: #555;">Password</label>
          <input type="password" id="password" name="password" placeholder="Min. 8 characters (letters + numbers)" required minlength="8" autocomplete="new-password"
            pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,}"
            title="Must be at least 8 characters with at least one letter and one number"
            style="height: 48px; width: 100%; border-radius: 10px; border: 1px solid #ddd; background: #fff; padding: 0 16px; font-size: 0.9rem; color: #333; outline: none; box-sizing: border-box; transition: border-color 0.2s;"
            onfocus="this.style.borderColor='#FF6B35'" onblur="this.style.borderColor='#ddd'">
        </div>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <button name="register" type="submit"
          style="height: 48px; width: 100%; border-radius: 10px; background: #FF6B35; color: white; font-size: 0.9rem; font-weight: 700; border: none; cursor: pointer; transition: background 0.2s; margin-top: 4px;"
          onmouseover="this.style.background='#EE4D2D'" onmouseout="this.style.background='#FF6B35'">
          Create account →
        </button>


        <p style="text-align: center; font-size: 0.85rem; color: #999; margin-top: 8px;">
          Already have an account?
          <a href="login.php" style="color: #FF6B35; text-decoration: none; font-weight: 600; margin-left: 4px;">Sign in</a>
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