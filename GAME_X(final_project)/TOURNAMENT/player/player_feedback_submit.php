<?php
session_start();
require_once __DIR__ . '/../backend/db.php'; // Database connection

// Only allow logged-in players
if (!isset($_SESSION['account_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $message = trim($_POST["message"]);

    if (!empty($message)) {

        // Get player's name and email from accounts table
        $stmt = $conn->prepare("SELECT fullname, email FROM accounts WHERE account_id = ?");
        $stmt->execute([$_SESSION['account_id']]);
        $user = $stmt->fetch();

        // Insert message into messages table (one-way to admin)
        $stmt = $conn->prepare("
            INSERT INTO messages (sender_id, receiver_id, name, email, message, created_at)
            VALUES (:sender_id, NULL, :name, :email, :message, NOW())
        ");

        $stmt->execute([
            ':sender_id' => $_SESSION['account_id'],
            ':name' => $user['fullname'],
            ':email' => $user['email'],
            ':message' => $message
        ]);

        header("Location: player_contact.php?success=1");
        exit;

    } else {
        header("Location: player_contact.php?error=1");
        exit;
    }

} else {
    header("Location: player_contact.php");
    exit;
}
?>
