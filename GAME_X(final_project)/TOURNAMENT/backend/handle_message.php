<?php
require_once "db.php"; // Database connection (PDO)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and trim inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    // Server-side validation
    if (empty($name) || empty($email) || empty($message)) {
        header("Location: ../contact.php?error=empty");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../contact.php?error=invalid_email");
        exit;
    }

    try {
        // Insert message into database
        $stmt = $conn->prepare("INSERT INTO messages (name, email, message, created_at, status) VALUES (?, ?, ?, NOW(), 'unread')");
        $stmt->execute([$name, $email, $message]);

        // Redirect back to contact page with success message
        header("Location: ../contact.php?success=1");
        exit;
    } catch (PDOException $e) {
        // Optional: log error
        error_log($e->getMessage());
        header("Location: ../contact.php?error=server");
        exit;
    }
}
?>
