<?php
session_start();
require '../config/config.php'; // Diperlukan untuk APPURL

// Hapus semua session data
$_SESSION = [];

// Hapus session cookie dari browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Regenerate ID sebelum destroy (anti session fixation)
session_regenerate_id(true);
session_destroy();

// Redirect ke homepage
header('Location: ' . APPURL . 'index.php');
exit;
?>