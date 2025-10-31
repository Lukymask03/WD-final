<?php
require_once "../backend/db.php"; // Database connection
session_start();

// Optional: check if admin is logged in
// if(!isset($_SESSION['admin'])) { header("Location: login.php"); exit; }

// Fetch all messages
$stmt = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Inbox</title>
<link rel="stylesheet" href="../assets/css/common.css">
</head>
<body>
<h2>Inbox</h2>

<table border="1" cellpadding="10" cellspacing="0">
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Message</th>
    <th>Date</th>
    <th>Status</th>
</tr>

<?php foreach ($messages as $msg): ?>
<tr>
    <td><?= htmlspecialchars($msg['name']) ?></td>
    <td><?= htmlspecialchars($msg['email']) ?></td>
    <td><?= htmlspecialchars($msg['message']) ?></td>
    <td><?= $msg['created_at'] ?></td>
    <td><?= $msg['status'] ?></td>
</tr>
<?php endforeach; ?>

</table>
</body>
</html>
