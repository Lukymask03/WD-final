<?php
// backend/admin/mark_notification_read.php
session_start();
require_once "../db.php";

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$notification_id = $input['notification_id'] ?? null;

if (!$notification_id) {
    echo json_encode(['success' => false, 'message' => 'Notification ID required']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE notification_id = :notification_id 
        AND account_id = :account_id
    ");
    
    $stmt->execute([
        'notification_id' => $notification_id,
        'account_id' => $_SESSION['account_id']
    ]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}