<?php
/**
 * Auth Middleware
 * Require user to be logged in to access a page.
 * Usage: require_once '../middleware/auth.php';
 */
if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
    $currentUrl = urlencode($_SERVER['REQUEST_URI']);
    header("Location: " . APPURL . "auth/login.php?redirect=" . $currentUrl);
    exit();
}
