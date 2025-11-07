<?php
require_once __DIR__ . "/../backend/config/db.php";
require_once __DIR__ . "/../backend/helpers/auth_guard.php";
checkAuth('organizer'); // only organizer can submit

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organizer_id = $_SESSION['user_id'];
    $subject = $_POST['subject'];
    $description = $_POST['description'];
    $timestamp = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("INSERT INTO reports (organizer_id, subject, description, created_at) VALUES (?, ?, ?, ?)");
    $stmt->execute([$organizer_id, $subject, $description, $timestamp]);

    header("Location: reports.php?success=1");
    exit;
}
?>
