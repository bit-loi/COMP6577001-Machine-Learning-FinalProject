<?php
 session_start();
 define('APPURL', 'http://localhost/bookstore/');
?>
<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/5c5946fe44.js" crossorigin="anonymous"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    
    <!-- Custom CSS with cache buster -->
    <link href="<?php echo APPURL; ?>assets/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <title>Bookstore - Your Premium Online Book Store</title>
  </head>
  <body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5), 0 0 40px rgba(0, 217, 255, 0.1); border-bottom: 1px solid rgba(0, 217, 255, 0.2);">
    <div class="container">
        <a class="navbar-brand" href="<?php echo APPURL; ?>" style="font-family: 'Playfair Display', serif; font-size: 1.75rem; font-weight: 700; background: linear-gradient(135deg, #00D9FF 0%, #00E5CC 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; transition: all 0.3s;">
            <i class="fas fa-book-reader me-2"></i>Bookstore
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" style="color: #00D9FF;">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 gap-2">
                <li class="nav-item">
                    <a class="nav-link px-3 py-2" href="<?php echo APPURL; ?>" style="color: rgba(255, 255, 255, 0.85); font-weight: 500; border-radius: 8px; transition: all 0.3s; position: relative;">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2" href="<?php echo APPURL; ?>pages/about.php" style="color: rgba(255, 255, 255, 0.85); font-weight: 500; border-radius: 8px; transition: all 0.3s; position: relative;">
                        <i class="fas fa-info-circle me-1"></i>About
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2" href="<?php echo APPURL; ?>pages/contact.php" style="color: rgba(255, 255, 255, 0.85); font-weight: 500; border-radius: 8px; transition: all 0.3s; position: relative;">
                        <i class="fas fa-envelope me-1"></i>Contact
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 py-2" href="<?php echo APPURL; ?>categories/index.php" style="color: rgba(255, 255, 255, 0.85); font-weight: 500; border-radius: 8px; transition: all 0.3s; position: relative;">
                        <i class="fas fa-th-large me-1"></i>Categories
                    </a>
                </li>

                <?php if(isset($_SESSION['username'])) : ?>

                <li class="nav-item">
                    <a class="nav-link px-3 py-2 position-relative" href="<?php echo APPURL; ?>shopping/cart.php" style="color: rgba(255, 255, 255, 0.85); font-weight: 500; border-radius: 8px; transition: all 0.3s;">
                        <i class="fas fa-shopping-cart me-1"></i>Cart
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); font-size: 0.65rem;">
                            2
                        </span>
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle px-3 py-2" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="color: rgba(255, 255, 255, 0.85); font-weight: 500; border-radius: 8px; transition: all 0.3s;">
                        <i class="fas fa-user-circle me-1"></i><?php echo $_SESSION['username']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown" style="background: rgba(30, 41, 59, 0.95); backdrop-filter: blur(10px); border: 1px solid rgba(0, 217, 255, 0.2); border-radius: 12px; padding: 0.5rem;">
                        <li><a class="dropdown-item" href="#" style="color: rgba(255, 255, 255, 0.85); border-radius: 8px; transition: all 0.3s; padding: 0.5rem 1rem;"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="#" style="color: rgba(255, 255, 255, 0.85); border-radius: 8px; transition: all 0.3s; padding: 0.5rem 1rem;"><i class="fas fa-box me-2"></i>My Orders</a></li>
                        <li><hr class="dropdown-divider" style="border-color: rgba(0, 217, 255, 0.2); margin: 0.5rem 0;"></li>
                        <li><a class="dropdown-item" href="<?php echo APPURL; ?>auth/logout.php" style="color: #EF4444; border-radius: 8px; transition: all 0.3s; padding: 0.5rem 1rem;"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else : ?>
                <li class="nav-item ms-2">
                    <a class="nav-link px-3 py-2" href="<?php echo APPURL; ?>auth/login.php" style="color: rgba(255, 255, 255, 0.85); font-weight: 500; border-radius: 8px; transition: all 0.3s; border: 1px solid rgba(0, 217, 255, 0.3);">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                </li>
                <li class="nav-item ms-1">
                    <a class="nav-link px-3 py-2" href="<?php echo APPURL; ?>auth/register.php" style="background: linear-gradient(135deg, #06B6D4 0%, #00D9FF 100%); color: #0F172A; font-weight: 600; border-radius: 8px; transition: all 0.3s; box-shadow: 0 4px 12px rgba(0, 217, 255, 0.3);">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Enhanced Navigation Styles -->
<style>
/* Navbar Brand Hover */
.navbar-brand:hover {
    transform: scale(1.05);
    filter: drop-shadow(0 0 10px rgba(0, 217, 255, 0.5));
}

/* Nav Link Hover Effects */
.nav-link:hover {
    color: #00D9FF !important;
    background: rgba(0, 217, 255, 0.1) !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 217, 255, 0.2);
}

/* Active Nav Link */
.nav-link.active {
    color: #00D9FF !important;
    background: rgba(0, 217, 255, 0.15) !important;
}

/* Nav Link Underline Effect */
.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, #00D9FF 0%, #00E5CC 100%);
    transition: width 0.3s ease;
}

.nav-link:hover::after {
    width: 80%;
}

/* Register Button Hover */
.nav-link[href*="register"]:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 217, 255, 0.4) !important;
}

/* Login Button Hover */
.nav-link[href*="login"]:hover {
    background: rgba(0, 217, 255, 0.15) !important;
    border-color: rgba(0, 217, 255, 0.6) !important;
}

/* Dropdown Menu Styles */
.dropdown-menu {
    animation: fadeInDown 0.3s ease;
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-item:hover {
    background: rgba(0, 217, 255, 0.15) !important;
    color: #00D9FF !important;
    transform: translateX(5px);
}

.dropdown-item[href*="logout"]:hover {
    background: rgba(239, 68, 68, 0.15) !important;
    color: #EF4444 !important;
}

/* Cart Badge Pulse */
.badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Sticky Navbar Shadow on Scroll */
.navbar.scrolled {
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.7), 0 0 50px rgba(0, 217, 255, 0.2) !important;
}

/* Mobile Menu */
@media (max-width: 991px) {
    .navbar-collapse {
        background: rgba(15, 23, 42, 0.98);
        padding: 1rem;
        border-radius: 12px;
        margin-top: 1rem;
        border: 1px solid rgba(0, 217, 255, 0.2);
    }
    
    .nav-link::after {
        display: none;
    }
}
</style>

<!-- Navbar Scroll Effect -->
<script>
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});

// Set active nav link based on current page
const currentLocation = window.location.href;
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    if (link.href === currentLocation) {
        link.classList.add('active');
    }
});
</script>
