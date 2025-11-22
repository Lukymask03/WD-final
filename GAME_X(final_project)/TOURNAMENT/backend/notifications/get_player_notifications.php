<?php
session_start();
require_once "../db.php";

$account_id = $_SESSION['account_id'];

$stmt = $conn->prepare("
    SELECT notification_id, title, message, created_at, is_read
    FROM notifications
    WHERE account_id = ?
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->execute([$account_id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$unread = 0;
foreach ($rows as $r) {
    if ($r['is_read'] == 0) $unread++;
}

echo json_encode([
    "unread" => $unread,
    "notifications" => $rows
]);
?>
