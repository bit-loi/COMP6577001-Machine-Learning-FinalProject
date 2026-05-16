<?php
 if (session_status() === PHP_SESSION_NONE) { session_start(); }
 require_once dirname(__DIR__) . '/config/config.php';
 
 if (!defined('APPURL')) {
     define('APPURL', 'http://localhost/shopmart/');
 }

 $cartCount = 0;
 $walletBalance = 0.00;
 $isSeller = 0;
 if(isset($_SESSION['user_id']) && isset($conn)) {
     $stmtCart = $conn->prepare("SELECT SUM(quantity) as total_items FROM cart WHERE user_id = ?");
     $stmtCart->execute([$_SESSION['user_id']]);
     $res = $stmtCart->fetch(PDO::FETCH_OBJ);
     $cartCount = $res->total_items ? $res->total_items : 0;
     
     $stmtUser = $conn->prepare("SELECT wallet_balance, is_seller FROM users WHERE id = ?");
     $stmtUser->execute([$_SESSION['user_id']]);
     $usr = $stmtUser->fetch(PDO::FETCH_OBJ);
     if($usr) {
         $walletBalance = $usr->wallet_balance;
         $isSeller = $usr->is_seller;
     }
 }
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Shopmart — Your favorite online marketplace for electronics, fashion, beauty, and more.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', 'sans-serif'] },
            colors: {
              shopmart: {
                50: '#FFF4ED', 100: '#FFE6D5', 200: '#FECBA6',
                300: '#FCA56C', 400: '#FB8C3E', 500: '#FF6B35',
                600: '#EE4D2D', 700: '#C53D20', 800: '#9C3320', 900: '#7E2D1E',
              }
            }
          }
        }
      }
    </script>

    <!-- Lucide Icons -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.454.1/umd/lucide.min.js"></script>
    <script>
      // Init Lucide with retry and multiple events to ensure icons render
      function initLucide() {
        if (typeof lucide !== 'undefined') {
          lucide.createIcons();
        }
      }
      window.addEventListener('DOMContentLoaded', initLucide);
      window.addEventListener('load', initLucide);
      // Also try immediately
      setTimeout(initLucide, 100);
    </script>

    <title>Shopmart — Your Favorite Marketplace</title>

    <style>
      *, *::before, *::after { box-sizing: border-box; }
      body { font-family: 'Inter', sans-serif; background: #f5f5f5; color: #111827; margin: 0; }

      /* ===== HEADER ===== */
      #main-header {
        background: #fff;
        border-bottom: 1px solid #e8e8e8;
        position: sticky;
        top: 0;
        z-index: 100;
        transition: box-shadow 0.3s ease;
      }
      #main-header.scrolled { box-shadow: 0 2px 16px rgba(0,0,0,0.08); }

      /* Top bar */
      .header-topbar {
        background: #fafafa;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.72rem;
        color: #888;
        height: 30px;
        display: flex;
        align-items: center;
      }
      .header-topbar a:hover { color: #EE4D2D; }

      /* Main nav */
      .header-main {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        max-width: 1280px;
        margin: 0 auto;
      }
      @media (min-width: 768px) { .header-main { padding: 12px 24px; gap: 20px; } }
      @media (min-width: 1024px) { .header-main { padding: 14px 48px; } }

      /* Logo */
      .header-logo img {
        height: 44px;
        width: auto;
        display: block;
        object-fit: contain;
      }
      @media (min-width: 768px) { .header-logo img { height: 52px; } }

      /* Search */
      .header-search {
        flex: 1;
        display: none;
      }
      @media (min-width: 768px) { .header-search { display: flex; } }

      .header-search form {
        display: flex;
        width: 100%;
        border: 2px solid #EE4D2D;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        transition: box-shadow 0.2s;
      }
      .header-search form:focus-within { box-shadow: 0 0 0 3px rgba(238,77,45,0.12); }
      .header-search input {
        flex: 1;
        border: none;
        outline: none;
        padding: 10px 16px;
        font-size: 0.875rem;
        color: #222;
        font-family: 'Inter', sans-serif;
      }
      .header-search button {
        background: #EE4D2D;
        border: none;
        color: #fff;
        padding: 0 20px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
      }
      .header-search button:hover { background: #C53D20; }

      /* Right actions */
      .header-actions {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-left: auto;
        flex-shrink: 0;
      }
      @media (min-width: 768px) { .header-actions { gap: 8px; margin-left: 0; } }

      .header-icon-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        border: none;
        background: transparent;
        color: #444;
        cursor: pointer;
        transition: background 0.18s, color 0.18s;
        text-decoration: none;
        position: relative;
      }
      .header-icon-btn:hover { background: #FFF4ED; color: #EE4D2D; }

      .cart-badge {
        position: absolute;
        top: 1px; right: 1px;
        background: #EE4D2D;
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        min-width: 16px;
        height: 16px;
        border-radius: 8px;
        border: 2px solid #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
      }

      /* User button */
      .user-btn {
        display: flex;
        align-items: center;
        gap: 6px;
        height: 38px;
        padding: 0 10px 0 6px;
        border-radius: 20px;
        border: 1px solid #e8e8e8;
        background: #fff;
        color: #444;
        font-size: 0.82rem;
        font-weight: 500;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        transition: border-color 0.18s, background 0.18s;
      }
      .user-btn:hover { border-color: #EE4D2D; background: #FFF4ED; color: #EE4D2D; }
      .user-avatar {
        width: 26px; height: 26px;
        background: #fee2d5;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #EE4D2D;
        flex-shrink: 0;
      }

      /* Auth buttons */
      .btn-login {
        display: none;
        padding: 7px 16px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #EE4D2D;
        border: 1.5px solid #EE4D2D;
        background: #fff;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.18s;
      }
      .btn-login:hover { background: #FFF4ED; }
      .btn-signup {
        display: none;
        padding: 7px 16px;
        border-radius: 6px;
        font-size: 0.82rem;
        font-weight: 600;
        color: #fff;
        background: #EE4D2D;
        border: 1.5px solid #EE4D2D;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.18s;
      }
      .btn-signup:hover { background: #C53D20; }
      @media (min-width: 480px) { .btn-login, .btn-signup { display: inline-flex; align-items: center; } }

      /* Hamburger */
      .hamburger-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 8px;
        border: none;
        background: transparent;
        color: #444;
        cursor: pointer;
        transition: background 0.18s;
        flex-shrink: 0;
      }
      .hamburger-btn:hover { background: #f5f5f5; }
      @media (min-width: 768px) { .hamburger-btn { display: none !important; } }

      /* Mobile search bar */
      #mobile-search-bar {
        display: none;
        padding: 0 16px 10px;
        background: #fff;
      }
      #mobile-search-bar.open { display: block; }
      #mobile-search-bar form {
        display: flex;
        border: 2px solid #EE4D2D;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
      }
      #mobile-search-bar input {
        flex: 1;
        border: none;
        outline: none;
        padding: 9px 14px;
        font-size: 0.875rem;
        font-family: 'Inter', sans-serif;
      }
      #mobile-search-bar button {
        background: #EE4D2D;
        border: none;
        color: #fff;
        padding: 0 16px;
        cursor: pointer;
        display: flex;
        align-items: center;
      }

      /* Mobile menu */
      #mobile-menu {
        display: none;
        flex-direction: column;
        background: #fff;
        border-top: 1px solid #f0f0f0;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
      }
      #mobile-menu.open { display: flex; }
      .mobile-nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 20px;
        font-size: 0.9rem;
        font-weight: 500;
        color: #333;
        text-decoration: none;
        border-bottom: 1px solid #f7f7f7;
        transition: color 0.15s, background 0.15s;
      }
      .mobile-nav-link:hover { color: #EE4D2D; background: #FFF9F7; }
      .mobile-nav-link svg { color: #888; flex-shrink: 0; }
      .mobile-nav-link:hover svg { color: #EE4D2D; }

      /* Dropdown */
      .nav-dropdown {
        display: block;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-6px);
        transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s;
        pointer-events: none;
      }
      .nav-dropdown.open {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: all;
      }
    </style>
  </head>
  <body>

    <!-- ===== HEADER ===== -->
    <header id="main-header">

      <!-- Top bar (desktop only) -->
      <div class="header-topbar hidden md:flex">
        <div style="max-width:1280px;width:100%;margin:0 auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between;">
          <div style="display:flex;align-items:center;gap:16px;">
            <span style="display:flex;align-items:center;gap:4px;cursor:pointer;transition:color .15s;" onmouseover="this.style.color='#EE4D2D'" onmouseout="this.style.color=''">
              <i data-lucide="bell" style="width:11px;height:11px;"></i> Notifications
            </span>
            <span style="display:flex;align-items:center;gap:4px;cursor:pointer;transition:color .15s;" onmouseover="this.style.color='#EE4D2D'" onmouseout="this.style.color=''">
              <i data-lucide="help-circle" style="width:11px;height:11px;"></i> Help
            </span>
          </div>
          <div style="display:flex;align-items:center;gap:12px;">
            <a href="#" style="text-decoration:none;color:inherit;transition:color .15s;" onmouseover="this.style.color='#EE4D2D'" onmouseout="this.style.color=''">Download App</a>
            <span style="color:#ddd;">|</span>
            <a href="#" style="text-decoration:none;color:inherit;transition:color .15s;" onmouseover="this.style.color='#EE4D2D'" onmouseout="this.style.color=''">Seller Centre</a>
          </div>
        </div>
      </div>

      <!-- Main nav row -->
      <div class="header-main">

        <!-- Hamburger (mobile) -->
        <button class="hamburger-btn" id="hamburger-btn" onclick="toggleMobileMenu()" aria-label="Open menu">
          <i data-lucide="menu" style="width:22px;height:22px;"></i>
        </button>

        <!-- Logo -->
        <a href="<?php echo APPURL; ?>" class="header-logo" style="text-decoration:none;flex-shrink:0;">
          <img src="<?php echo APPURL; ?>assets/header_logo.png" alt="Shopmart">
        </a>

        <!-- Desktop Search -->
        <div class="header-search">
          <form action="<?php echo APPURL; ?>categories/index.php" method="GET">
            <input type="text" name="search" placeholder="Search for products, brands and shops" autocomplete="off">
            <button type="submit" aria-label="Search" style="display:flex;align-items:center;justify-content:center;">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </button>
          </form>
        </div>

        <!-- Right Actions -->
        <div class="header-actions">

          <!-- Mobile search icon -->
          <button class="header-icon-btn flex items-center justify-center md:hidden" onclick="toggleMobileSearch()" aria-label="Search" id="mobile-search-toggle">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
          </button>

          <!-- Cart -->
          <a href="<?php echo APPURL; ?>shopping/cart.php" class="header-icon-btn" aria-label="Cart">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
            <?php if($cartCount > 0): ?>
            <span class="cart-badge"><?php echo $cartCount > 99 ? '99+' : $cartCount; ?></span>
            <?php endif; ?>
          </a>

          <!-- User / Auth -->
          <?php if(isset($_SESSION['username'])):
            $displayName = $_SESSION['username'];
            if($displayName === 'admin_utama') { $displayName = 'Admin Shopmart'; }
            else { $displayName = ucwords($displayName); }
            $shortName = strlen($displayName) > 12 ? substr($displayName, 0, 12) . '…' : $displayName;
          ?>
            <!-- Wallet Badge (Visible on desktop) -->
            <div class="hidden md:flex" style="align-items:center; gap:6px; background:#f0fdf4; padding:6px 14px; border-radius:20px; border:1px solid #bbf7d0; margin-right:4px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12V7H5a2 2 0 0 1 0-4h14v4"/><path d="M3 5v14a2 2 0 0 0 2 2h16v-5"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                <span style="font-size:0.85rem; font-weight:800; color:#15803d; display:flex; align-items:center;">
                    <?php if($walletBalance < 0): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-top:1px;"><path d="M12 12c-2-2.67-4-4-6-4a4 4 0 1 0 0 8c2 0 4-1.33 6-4Zm0 0c2 2.67 4 4 6 4a4 4 0 1 0 0-8c-2 0-4 1.33-6 4Z"/></svg>
                    <?php else: ?>
                        $<?php echo number_format($walletBalance, 2); ?>
                    <?php endif; ?>
                </span>
            </div>

            <div class="nav-item" style="position:relative;">
              <button class="user-btn" onclick="toggleDropdown('dropdown-user', this)">
                <span class="user-avatar">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <span class="hidden sm:inline"><?php echo htmlspecialchars($shortName); ?></span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity:0.5;"><polyline points="6 9 12 15 18 9"/></svg>
              </button>
              <div id="dropdown-user" class="nav-dropdown" style="position:absolute;top:calc(100% + 8px);right:0;width:268px;background:#fff;border:1px solid #e8e8e8;border-radius:14px;overflow:hidden;box-shadow:0 12px 40px rgba(0,0,0,0.12),0 4px 12px rgba(0,0,0,0.06);">
                <div style="padding:12px 16px;border-bottom:1px solid #f5f5f5;background:#fafafa;">
                  <div style="font-size:0.72rem;color:#999;margin-bottom:2px;">Signed in as</div>
                  <div style="font-size:0.88rem;font-weight:700;color:#111;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo htmlspecialchars($displayName); ?></div>
                  <div style="margin-top:8px;padding-top:8px;border-top:1px dashed #e5e5e5;display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:0.75rem;color:#666;">Wallet Balance:</span>
                    <span style="font-size:0.85rem;font-weight:800;color:#16a34a;display:flex;align-items:center;gap:4px;">
                        <?php if($walletBalance < 0): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12c-2-2.67-4-4-6-4a4 4 0 1 0 0 8c2 0 4-1.33 6-4Zm0 0c2 2.67 4 4 6 4a4 4 0 1 0 0-8c-2 0-4 1.33-6 4Z"/></svg>
                            <span style="font-size:0.75rem;">(Admin)</span>
                        <?php else: ?>
                            $<?php echo number_format($walletBalance, 2); ?>
                        <?php endif; ?>
                    </span>
                  </div>
                </div>
                <div style="padding:6px 0;">
                  <?php if((isset($_SESSION['is_admin'])&&$_SESSION['is_admin']==1)||(isset($_SESSION['role'])&&$_SESSION['role']==='admin')||(isset($_SESSION['username'])&&strtolower($_SESSION['username'])==='admin')): ?>
                  <a href="<?php echo APPURL; ?>admin/index.php" style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:0.84rem;font-weight:700;color:#EE4D2D;text-decoration:none;background:#FFF9F7;transition:background .15s;" onmouseover="this.style.background='#FFE6D5'" onmouseout="this.style.background='#FFF9F7'">
                    <i data-lucide="layout-dashboard" style="width:15px;height:15px;"></i> Admin Dashboard
                  </a>
                  <div style="height:1px;background:#f5f5f5;"></div>
                  <?php endif; ?>
                  
                  <?php if($isSeller): ?>
                  <a href="<?php echo APPURL; ?>seller/index.php" style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:0.84rem;font-weight:700;color:#EE4D2D;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='#FFF9F7'" onmouseout="this.style.background=''">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/><path d="M12 3v6"/></svg>
                    My Shop (Seller)
                  </a>
                  <?php else: ?>
                  <a href="<?php echo APPURL; ?>seller/register.php" style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:0.84rem;color:#3b82f6;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='#eff6ff'" onmouseout="this.style.background=''">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9h18v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9Z"/><path d="m3 9 2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9"/><path d="M12 3v6"/></svg>
                    Become a Seller
                  </a>
                  <?php endif; ?>
                  
                  <div style="height:1px;background:#f0f0f0;margin:6px 12px;"></div>
                  <div style="padding:4px 8px 2px;font-size:0.68rem;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:0.06em;">Akun Saya</div>

                  <a href="<?php echo APPURL; ?>account/wallet.php" style="display:flex;align-items:center;gap:12px;padding:11px 14px;margin:2px 8px;font-size:0.84rem;color:#333;text-decoration:none;border-radius:10px;transition:all .18s;border:1px solid transparent;" onmouseover="this.style.background='#FFF9F7';this.style.borderColor='#FECBA6';this.style.boxShadow='0 2px 10px rgba(238,77,45,0.1)'" onmouseout="this.style.background='';this.style.borderColor='transparent';this.style.boxShadow=''">
                    <span style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#ecfdf5,#d1fae5);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(5,150,105,0.15);">
                      <i data-lucide="wallet" style="width:16px;height:16px;color:#059669;"></i>
                    </span>
                    <span style="flex:1;">
                      <span style="display:block;font-weight:600;color:#222;">Wallet & Topup</span>
                      <span style="display:block;font-size:0.72rem;color:#999;margin-top:1px;">Saldo & isi ulang</span>
                    </span>
                    <i data-lucide="chevron-right" style="width:14px;height:14px;color:#ccc;flex-shrink:0;"></i>
                  </a>
                  <a href="<?php echo APPURL; ?>account/purchases.php" style="display:flex;align-items:center;gap:12px;padding:11px 14px;margin:2px 8px;font-size:0.84rem;color:#333;text-decoration:none;border-radius:10px;transition:all .18s;border:1px solid transparent;" onmouseover="this.style.background='#FFF9F7';this.style.borderColor='#FECBA6';this.style.boxShadow='0 2px 10px rgba(238,77,45,0.1)'" onmouseout="this.style.background='';this.style.borderColor='transparent';this.style.boxShadow=''">
                    <span style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#FFF4ED,#FFE6D5);display:flex;align-items:center;justify-content:center;flex-shrink:0;box-shadow:0 2px 6px rgba(238,77,45,0.15);">
                      <i data-lucide="package" style="width:16px;height:16px;color:#EE4D2D;"></i>
                    </span>
                    <span style="flex:1;">
                      <span style="display:block;font-weight:600;color:#222;">My Purchases</span>
                      <span style="display:block;font-size:0.72rem;color:#999;margin-top:1px;">Riwayat pesanan</span>
                    </span>
                    <i data-lucide="chevron-right" style="width:14px;height:14px;color:#ccc;flex-shrink:0;"></i>
                  </a>
                </div>
                <div style="height:1px;background:#f5f5f5;"></div>
                <div style="padding:6px 0;">
                  <a href="<?php echo APPURL; ?>auth/logout.php" style="display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:0.84rem;color:#ef4444;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='#FEF2F2'" onmouseout="this.style.background=''">
                    <i data-lucide="log-out" style="width:15px;height:15px;"></i> Logout
                  </a>
                </div>
              </div>
            </div>
          <?php else: ?>
            <a href="<?php echo APPURL; ?>auth/login.php" class="btn-login">Login</a>
            <a href="<?php echo APPURL; ?>auth/register.php" class="btn-signup">Sign Up</a>
          <?php endif; ?>
        </div>
      </div>

      <!-- Mobile Search Bar (slide down) -->
      <div id="mobile-search-bar">
        <form action="<?php echo APPURL; ?>categories/index.php" method="GET">
          <input type="text" name="search" placeholder="Cari produk, merek, atau toko..." autocomplete="off" id="mobile-search-input">
          <button type="submit" aria-label="Search">
            <i data-lucide="search" style="width:16px;height:16px;"></i>
          </button>
        </form>
      </div>

      <!-- Mobile Menu -->
      <div id="mobile-menu">
        <a href="<?php echo APPURL; ?>" class="mobile-nav-link">
          <i data-lucide="home" style="width:18px;height:18px;"></i> Home
        </a>
        <a href="<?php echo APPURL; ?>categories/index.php" class="mobile-nav-link">
          <i data-lucide="grid-2x2" style="width:18px;height:18px;"></i> All Categories
        </a>
        <a href="<?php echo APPURL; ?>shopping/cart.php" class="mobile-nav-link">
          <i data-lucide="shopping-cart" style="width:18px;height:18px;"></i> 
          <span>Cart</span>
          <?php if($cartCount > 0): ?>
          <span style="margin-left:auto;background:#EE4D2D;color:#fff;font-size:0.72rem;font-weight:700;padding:2px 8px;border-radius:12px;"><?php echo $cartCount; ?></span>
          <?php endif; ?>
        </a>
        <?php if(isset($_SESSION['username'])): ?>
        <a href="<?php echo APPURL; ?>account/wallet.php" class="mobile-nav-link">
          <i data-lucide="wallet" style="width:18px;height:18px;"></i> Wallet & Topup
        </a>
        <a href="<?php echo APPURL; ?>account/purchases.php" class="mobile-nav-link">
          <i data-lucide="package" style="width:18px;height:18px;"></i> My Purchases
        </a>
        <?php if((isset($_SESSION['is_admin'])&&$_SESSION['is_admin']==1)||(isset($_SESSION['role'])&&$_SESSION['role']==='admin')||(isset($_SESSION['username'])&&strtolower($_SESSION['username'])==='admin')): ?>
        <a href="<?php echo APPURL; ?>admin/index.php" class="mobile-nav-link" style="color:#EE4D2D;font-weight:600;">
          <i data-lucide="layout-dashboard" style="width:18px;height:18px;"></i> Admin Dashboard
        </a>
        <?php endif; ?>
        <div style="padding:12px 20px;border-top:1px solid #f0f0f0;">
          <a href="<?php echo APPURL; ?>auth/logout.php" style="display:block;text-align:center;padding:10px;border-radius:8px;border:1.5px solid #fca5a5;color:#ef4444;font-size:0.88rem;font-weight:600;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='#FEF2F2'" onmouseout="this.style.background=''">
            Logout
          </a>
        </div>
        <?php else: ?>
        <div style="padding:14px 20px;border-top:1px solid #f0f0f0;display:flex;gap:10px;">
          <a href="<?php echo APPURL; ?>auth/login.php" style="flex:1;text-align:center;padding:10px;border-radius:8px;border:1.5px solid #EE4D2D;color:#EE4D2D;font-size:0.88rem;font-weight:600;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='#FFF4ED'" onmouseout="this.style.background=''">Login</a>
          <a href="<?php echo APPURL; ?>auth/register.php" style="flex:1;text-align:center;padding:10px;border-radius:8px;background:#EE4D2D;color:#fff;font-size:0.88rem;font-weight:600;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='#C53D20'" onmouseout="this.style.background='#EE4D2D'">Sign Up</a>
        </div>
        <?php endif; ?>
      </div>
    </header>

    <script>
      // Scroll effect
      window.addEventListener('scroll', function() {
        document.getElementById('main-header').classList.toggle('scrolled', window.scrollY > 10);
      });

      // Hamburger menu
      function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('open');
        // Close search if open
        document.getElementById('mobile-search-bar').classList.remove('open');
      }

      // Mobile search toggle
      function toggleMobileSearch() {
        const bar = document.getElementById('mobile-search-bar');
        bar.classList.toggle('open');
        if (bar.classList.contains('open')) {
          setTimeout(() => document.getElementById('mobile-search-input').focus(), 100);
          // Close hamburger menu if open
          document.getElementById('mobile-menu').classList.remove('open');
        }
      }

      // Dropdown (user menu)
      function toggleDropdown(id, btn) {
        const d = document.getElementById(id);
        const isOpen = d.classList.contains('open');
        document.querySelectorAll('.nav-dropdown.open').forEach(el => el.classList.remove('open'));
        if (!isOpen) d.classList.add('open');
      }
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item')) {
          document.querySelectorAll('.nav-dropdown.open').forEach(el => el.classList.remove('open'));
        }
      });
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          document.querySelectorAll('.nav-dropdown.open').forEach(el => el.classList.remove('open'));
          document.getElementById('mobile-menu').classList.remove('open');
          document.getElementById('mobile-search-bar').classList.remove('open');
        }
      });

      // Lucide icons already initialized in <head> after script load
    </script>
