<?php
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get and sanitize form data
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    // Validate
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($subject)) {
        $errors[] = "Subject is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, status, created_at) 
                                   VALUES (?, ?, ?, ?, 'unread', NOW())");
            $stmt->execute([$name, $email, $subject, $message]);
            
            $_SESSION['success_message'] = "Thank you for contacting us! We'll get back to you soon.";
            header("Location: ../pages/contact.php?success=1");
            exit();
            
        } catch(PDOException $e) {
            $_SESSION['error_message'] = "Sorry, there was an error sending your message. Please try again.";
            header("Location: ../pages/contact.php?error=1");
            exit();
        }
    } else {
        $_SESSION['error_message'] = implode(", ", $errors);
        header("Location: ../pages/contact.php?error=1");
        exit();
    }
    
} else {
    header("Location: ../pages/contact.php");
    exit();
}
?>
