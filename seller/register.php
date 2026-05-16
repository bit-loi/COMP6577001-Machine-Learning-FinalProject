<?php
session_start();
require '../config/config.php';
require '../middleware/auth.php';

// Upgrade user to seller
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("UPDATE users SET is_seller = 1 WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Redirect to seller dashboard with success parameter
        header("Location: index.php?registered=1");
        exit;
    } catch (PDOException $e) {
        // Fallback to error page if something goes wrong
        header("Location: ../error.php?type=500");
        exit;
    }
} else {
    header("Location: ../auth/login.php");
    exit;
}
