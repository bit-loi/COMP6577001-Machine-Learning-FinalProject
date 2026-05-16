<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAdmin = false;
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    $isAdmin = true;
} elseif (isset($_SESSION['username']) && $_SESSION['username'] === 'admin_utama') {
    $isAdmin = true;
}

if (!$isAdmin) {
    header("Location: " . (defined('APPURL') ? APPURL : '/shopmart/') . "auth/login.php");
    exit();
}
?>
