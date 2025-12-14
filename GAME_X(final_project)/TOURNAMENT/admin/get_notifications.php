<?php
// backend/admin/get_notifications.php
session_start();
require_once "../db.php";

header('Content-Type: application/json');

// Check if admin is logged in
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    // Get all notifications for admin (last 50)
    $stmt = $conn->prepare("
        SELECT 
            notification_id,
            title,
            message,
            link,
            is_read,
            created_at
        FROM notifications
        WHERE account_id = :account_id
        ORDER BY created_at DESC
        LIMIT 50
    ");
    
    $stmt->execute(['account_id' => $_SESSION['account_id']]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unread count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_count
        FROM notifications
        WHERE account_id = :account_id AND is_read = 0
    ");
    
    $stmt->execute(['account_id' => $_SESSION['account_id']]);
    $unread = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'unread_count' => (int)$unread['unread_count']
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}