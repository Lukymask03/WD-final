<?php
require_once __DIR__ . '/../db.php';

function notify($account_id, $title, $message, $type = 'system') {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO notifications (account_id, title, message, type)
        VALUES (:account_id, :title, :message, :type)
    ");

    return $stmt->execute([
        ':account_id' => $account_id,
        ':title' => $title,
        ':message' => $message,
        ':type' => $type
    ]);
}
