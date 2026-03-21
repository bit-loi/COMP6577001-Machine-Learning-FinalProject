<?php
 session_start();
 if (!defined('APPURL')) {
     define('APPURL', 'http://localhost/bookstore/');
 }
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ['Plus Jakarta Sans', 'sans-serif'],
              serif: ['Playfair Display', 'serif'],
            }
          }
        }
      }
    </script>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    <title>Premeditatio Malorum — Curated Literature</title>

    <style>
      body { font-family: 'Plus Jakarta Sans', sans-serif; background: #050505; color: #fff; margin: 0; }
      .font-serif { font-family: 'Playfair Display', serif; }

      /* Header scroll effect */
      #main-header {
        transition: all 0.3s ease;
      }
      #main-header.scrolled {
        background: rgba(10, 10, 10, 0.98) !important;
        box-shadow: 0 4px 30px rgba(0,0,0,0.5);
      }

      /* Dropdown — click-based */
      .nav-dropdown {
        display: block;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-8px);
        transition: opacity 0.2s ease, transform 0.2s ease, visibility 0.2s;
        pointer-events: none;
      }
      .nav-dropdown.open {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
        pointer-events: all;
      }

      /* Mobile menu */
      #mobile-menu { display: none; }
      #mobile-menu.open { display: flex; }

      /* Smooth hover */
      .nav-link-item {
        transition: color 0.2s, background 0.2s;
      }
      .nav-link-item:hover {
        color: rgba(255,255,255,1) !important;
        background: rgba(255,255,255,0.05);
        border-radius: 8px;
      }
    </style>
  </head>
  <body>

    <!-- ===== NAVBAR ===== -->
    <header id="main-header" class="w-full z-50 fixed top-0 left-0" style="background: rgba(5,5,5,0.85); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255,255,255,0.06);">
      <div class="max-w-7xl mx-auto px-6 lg:px-12 flex items-center justify-between" style="min-height: 72px;">

        <!-- Left: Nav Links (Desktop) -->
        <nav class="hidden lg:flex items-center gap-1">
          <a href="<?php echo APPURL; ?>" class="nav-link-item px-4 py-2 text-sm font-medium" style="color: rgba(255,255,255,0.6);">Home</a>

          <div class="nav-item relative" id="nav-collections">
            <button
              class="nav-link-item px-4 py-2 text-sm font-medium flex items-center gap-1"
              style="color: rgba(255,255,255,0.6); background: none; border: none; cursor: pointer;"
              onclick="toggleDropdown('dropdown-collections', this)"
              aria-expanded="false"
            >
              Collections
              <i data-lucide="chevron-down" id="chevron-collections" style="width:14px; height:14px; opacity:0.5; transition: transform 0.2s;"></i>
            </button>
            <div id="dropdown-collections" class="nav-dropdown absolute top-full left-0 mt-2 w-56 rounded-xl overflow-hidden" style="background: #111; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 20px 60px rgba(0,0,0,0.6);">
              <a href="<?php echo APPURL; ?>categories/index.php" class="flex items-center justify-between px-4 py-3 text-sm hover:bg-white/5 transition-colors" style="color: rgba(255,255,255,0.6);">All Categories <i data-lucide="arrow-right" style="width:14px;height:14px;"></i></a>
              <a href="<?php echo APPURL; ?>categories/index.php" class="flex items-center justify-between px-4 py-3 text-sm hover:bg-white/5 transition-colors" style="color: rgba(255,255,255,0.6);">Best Sellers <i data-lucide="arrow-right" style="width:14px;height:14px;"></i></a>
              <a href="<?php echo APPURL; ?>categories/index.php" class="flex items-center justify-between px-4 py-3 text-sm hover:bg-white/5 transition-colors" style="color: rgba(255,255,255,0.6);">New Arrivals <i data-lucide="arrow-right" style="width:14px;height:14px;"></i></a>
              <a href="<?php echo APPURL; ?>categories/index.php" class="flex items-center justify-between px-4 py-3 text-sm hover:bg-white/5 transition-colors" style="color: rgba(255,255,255,0.6);">Sci-Fi &amp; Tech <i data-lucide="arrow-right" style="width:14px;height:14px;"></i></a>
            </div>
          </div>

          <a href="<?php echo APPURL; ?>pages/about.php" class="nav-link-item px-4 py-2 text-sm font-medium" style="color: rgba(255,255,255,0.6);">About</a>
          <a href="<?php echo APPURL; ?>pages/contact.php" class="nav-link-item px-4 py-2 text-sm font-medium" style="color: rgba(255,255,255,0.6);">Contact</a>
        </nav>

        <!-- Center: Logo -->
        <div class="flex items-center gap-2 absolute left-1/2 -translate-x-1/2">
          <i data-lucide="book-open" style="width:22px; height:22px; color: white;"></i>
          <span style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 0.95rem; font-weight: 700; color: white; letter-spacing: 0.02em;">Premeditatio Malorum</span>
        </div>

        <!-- Right: Auth Buttons (Desktop) -->
        <div class="hidden lg:flex items-center gap-3">
          <?php if(isset($_SESSION['username'])) : ?>
            <a href="<?php echo APPURL; ?>shopping/cart.php" class="nav-link-item px-4 py-2 text-sm font-medium flex items-center gap-2" style="color: rgba(255,255,255,0.6);">
              <i data-lucide="shopping-cart" style="width:16px;height:16px;"></i> Cart
            </a>
            <div class="nav-item relative">
              <button class="nav-link-item px-4 py-2 text-sm font-medium flex items-center gap-2" style="color: rgba(255,255,255,0.6); background: none; border: none; cursor: pointer;">
                <i data-lucide="user" style="width:16px;height:16px;"></i>
                <?php echo htmlspecialchars($_SESSION['username']); ?>
                <i data-lucide="chevron-down" style="width:14px;height:14px; opacity:0.5;"></i>
              </button>
              <div class="nav-dropdown absolute top-full right-0 mt-2 w-48 rounded-xl overflow-hidden" style="background: #111; border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 20px 60px rgba(0,0,0,0.6);">
                <a href="#" class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-white/5 transition-colors" style="color: rgba(255,255,255,0.6);">
                  <i data-lucide="user" style="width:14px;height:14px;"></i> My Profile
                </a>
                <a href="#" class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-white/5 transition-colors" style="color: rgba(255,255,255,0.6);">
                  <i data-lucide="package" style="width:14px;height:14px;"></i> My Orders
                </a>
                <div style="height:1px; background: rgba(255,255,255,0.06); margin: 4px 0;"></div>
                <a href="<?php echo APPURL; ?>auth/logout.php" class="flex items-center gap-2 px-4 py-3 text-sm hover:bg-red-500/10 transition-colors" style="color: rgba(239,68,68,0.8);">
                  <i data-lucide="log-out" style="width:14px;height:14px;"></i> Logout
                </a>
              </div>
            </div>
          <?php else : ?>
            <a href="<?php echo APPURL; ?>auth/login.php" class="px-4 py-2 text-sm font-medium rounded-lg transition-all" style="color: rgba(255,255,255,0.7); border: 1px solid rgba(255,255,255,0.12);">Sign in</a>
            <a href="<?php echo APPURL; ?>auth/register.php" class="px-4 py-2 text-sm font-semibold rounded-lg transition-all" style="background: white; color: #050505;">Get started</a>
          <?php endif; ?>
        </div>

        <!-- Mobile: Hamburger -->
        <button id="hamburger-btn" class="lg:hidden p-2 rounded-lg" style="color: white; background: none; border: none; cursor: pointer;" onclick="toggleMobileMenu()">
          <i data-lucide="menu" id="hamburger-icon" style="width:22px;height:22px;"></i>
        </button>
      </div>

      <!-- Mobile Menu -->
      <div id="mobile-menu" class="lg:hidden flex-col px-6 pb-6 gap-2" style="border-top: 1px solid rgba(255,255,255,0.06);">
        <a href="<?php echo APPURL; ?>" class="block py-3 text-sm font-medium border-b" style="color: rgba(255,255,255,0.6); border-color: rgba(255,255,255,0.06);">Home</a>
        <a href="<?php echo APPURL; ?>categories/index.php" class="block py-3 text-sm font-medium border-b" style="color: rgba(255,255,255,0.6); border-color: rgba(255,255,255,0.06);">Collections</a>
        <a href="<?php echo APPURL; ?>pages/about.php" class="block py-3 text-sm font-medium border-b" style="color: rgba(255,255,255,0.6); border-color: rgba(255,255,255,0.06);">About</a>
        <a href="<?php echo APPURL; ?>pages/contact.php" class="block py-3 text-sm font-medium border-b" style="color: rgba(255,255,255,0.6); border-color: rgba(255,255,255,0.06);">Contact</a>
        <div class="flex gap-3 mt-4">
          <?php if(isset($_SESSION['username'])) : ?>
            <a href="<?php echo APPURL; ?>auth/logout.php" class="flex-1 text-center py-2 text-sm rounded-lg" style="border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.7);">Logout</a>
          <?php else : ?>
            <a href="<?php echo APPURL; ?>auth/login.php" class="flex-1 text-center py-2 text-sm rounded-lg" style="border: 1px solid rgba(255,255,255,0.12); color: rgba(255,255,255,0.7);">Sign in</a>
            <a href="<?php echo APPURL; ?>auth/register.php" class="flex-1 text-center py-2 text-sm font-semibold rounded-lg" style="background: white; color: #050505;">Get started</a>
          <?php endif; ?>
        </div>
      </div>
    </header>

    <!-- Spacer for fixed header -->
    <div style="height: 72px;"></div>

    <script>
      function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('open');
      }

      // Scroll effect
      window.addEventListener('scroll', function() {
        const header = document.getElementById('main-header');
        if (window.scrollY > 50) {
          header.classList.add('scrolled');
        } else {
          header.classList.remove('scrolled');
        }
      });

      // ── Click-based dropdown ──────────────────────────
      function toggleDropdown(dropdownId, btn) {
        const dropdown = document.getElementById(dropdownId);
        const isOpen = dropdown.classList.contains('open');

        // Close all open dropdowns first
        document.querySelectorAll('.nav-dropdown.open').forEach(d => {
          d.classList.remove('open');
          const b = d.previousElementSibling;
          if (b) b.setAttribute('aria-expanded', 'false');
          const ch = b ? b.querySelector('i[data-lucide="chevron-down"]') : null;
          if (ch) ch.style.transform = '';
        });

        if (!isOpen) {
          dropdown.classList.add('open');
          btn.setAttribute('aria-expanded', 'true');
          const chevron = btn.querySelector('i[data-lucide="chevron-down"]');
          if (chevron) chevron.style.transform = 'rotate(180deg)';
        }
      }

      // Close dropdown when clicking outside
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-item')) {
          document.querySelectorAll('.nav-dropdown.open').forEach(d => {
            d.classList.remove('open');
          });
          document.querySelectorAll('[aria-expanded="true"]').forEach(b => {
            b.setAttribute('aria-expanded', 'false');
            const chevron = b.querySelector('i[data-lucide="chevron-down"]');
            if (chevron) chevron.style.transform = '';
          });
        }
      });

      // Close on Escape
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          document.querySelectorAll('.nav-dropdown.open').forEach(d => d.classList.remove('open'));
        }
      });
    </script>
