<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}
header('Content-Type: application/json');

if (safe_request_method() !== 'POST' || !verify_csrf_token()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request token.']);
    exit();
}

$custId      = safe_text($_POST['customer_id'] ?? '', 80);
$scoreId     = safe_int($_POST['churn_score_id'] ?? 0);
$actionType  = safe_text($_POST['action_type'] ?? '', 40);
$adminNote   = safe_text($_POST['admin_note'] ?? '', 500);
$actionedBy  = safe_text($_SESSION['username'] ?? 'admin', 80);

$allowed = ['contacted', 'voucher_sent', 'email_sent', 'customer_returned', 'ignored'];

if (!$custId || !in_array($actionType, $allowed)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters.']);
    exit();
}

try {
    $stmt = $conn->prepare("
        INSERT INTO retention_actions
            (customer_id, churn_score_id, action_type, action_status, admin_note, actioned_by)
        VALUES (?, ?, ?, 'done', ?, ?)
    ");
    $stmt->execute([$custId, $scoreId ?: null, $actionType, $adminNote, $actionedBy]);

    $label = match($actionType) {
        'contacted'         => 'Marked as Contacted',
        'voucher_sent'      => 'Voucher Sent',
        'email_sent'        => 'Email Sent',
        'customer_returned' => 'Customer Returned',
        'ignored'           => 'Ignored',
        default             => 'Action Recorded',
    };

    echo json_encode(['success' => true, 'message' => $label, 'action_type' => $actionType]);
} catch (PDOException $e) {
    error_log('retention_action error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
