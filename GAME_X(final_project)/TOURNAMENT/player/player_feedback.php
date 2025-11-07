<?php
require_once '../../backend/config/db.php';
session_start();

if (!isset($_SESSION['account_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = $_SESSION['account_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("
        INSERT INTO messages (sender_id, name, email, message, created_at)
        VALUES (:sender_id, :name, :email, :message, NOW())
    ");
    $stmt->execute([
        ':sender_id' => $account_id,
        ':name' => $name,
        ':email' => $email,
        ':message' => $message
    ]);

    echo "<script>alert('Feedback sent successfully!'); window.location.href='player_dashboard.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Send Feedback</title>
</head>
<body>
<!-- ===== NAVBAR ===== -->
<?php require_once "../includes/player/player_navbar.php"; ?>

  <h2>Send Feedback to Admin</h2>
  <form method="POST">
    <input type="text" name="name" placeholder="Your Name" required><br><br>
    <input type="email" name="email" placeholder="Your Email" required><br><br>
    <textarea name="message" placeholder="Enter your message..." required></textarea><br><br>
    <button type="submit">Send Feedback</button>
  </form>
</body>
</html>
