<?php
// backend/admin/mark_all_notifications_read.php
session_start();
require_once "../db.php";

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $conn->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE account_id = :account_id AND is_read = 0
    ");
    
    $stmt->execute(['account_id' => $_SESSION['account_id']]);
    
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}