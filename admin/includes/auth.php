<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only trust the is_admin flag set during login from the database
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: " . (defined('APPURL') ? APPURL : '/shopmart/') . "auth/login.php");
    exit();
}
?>
