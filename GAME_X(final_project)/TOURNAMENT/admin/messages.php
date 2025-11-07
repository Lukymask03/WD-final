<?php
require_once "../backend/db.php";
session_start();

// ✅ Optional: check admin login
// if(!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
//     header("Location: ../auth/login.php");
//     exit;
// }

// ✅ Handle admin reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $id = $_POST['id'];
    $reply = $_POST['reply_message'];

    // Update the message with admin reply
    $stmt = $conn->prepare("
        UPDATE messages 
        SET reply_message = :reply_message, 
            replied_at = NOW(),
            status = 'replied'
        WHERE id = :id
    ");
    $stmt->execute([
        ':reply_message' => $reply,
        ':id' => $id
    ]);

    // Auto “thank you” message (optional but nice)
    $thankYou = "Thank you for your feedback! We at Game X appreciate your feedback.";
    $thankStmt = $conn->prepare("
        INSERT INTO messages (name, email, message, created_at, status)
        SELECT name, email, :message, NOW(), 'replied' FROM messages WHERE id = :id
    ");
    $thankStmt->execute([
        ':message' => $thankYou,
        ':id' => $id
    ]);

    echo "<script>alert('Reply sent and thank-you message delivered!'); window.location.href='messages.php';</script>";
    exit;
}

// ✅ Fetch all messages
$stmt = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Inbox</title>
<link rel="stylesheet" href="../assets/css/common.css">
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #f8f9fa;
    margin: 20px;
}
h2 {
    text-align: center;
    color: #333;
}
table {
    border-collapse: collapse;
    width: 100%;
    background-color: white;
    box-shadow: 0 0 8px rgba(0,0,0,0.1);
}
th, td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}
th {
    background-color: #007bff;
    color: white;
}
tr:nth-child(even) { background-color: #f2f2f2; }
textarea {
    width: 100%;
    height: 60px;
    resize: none;
}
button {
    background-color: #28a745;
    color: white;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 4px;
}
button:hover {
    background-color: #218838;
}
.status-new { color: red; font-weight: bold; }
.status-replied { color: green; font-weight: bold; }
</style>
</head>
<body>

<h2>📬 Admin Inbox</h2>

<table>
<tr>
    <th>Name</th>
    <th>Email</th>
    <th>Message</th>
    <th>Reply</th>
    <th>Date</th>
    <th>Status</th>
</tr>

<?php foreach ($messages as $msg): ?>
<tr>
    <td><?= htmlspecialchars($msg['name']) ?></td>
    <td><?= htmlspecialchars($msg['email']) ?></td>
    <td><?= nl2br(htmlspecialchars($msg['message'])) ?></td>

    <td>
        <?php if (empty($msg['reply_message'])): ?>
            <form method="POST" style="margin:0;">
                <input type="hidden" name="id" value="<?= $msg['id'] ?>">
                <textarea name="reply_message" placeholder="Write a reply..." required></textarea><br>
                <button type="submit">Send Reply</button>
            </form>
        <?php else: ?>
            <?= nl2br(htmlspecialchars($msg['reply_message'])) ?>
            <br><small><em>Replied at: <?= $msg['replied_at'] ?></em></small>
        <?php endif; ?>
    </td>

    <td><?= $msg['created_at'] ?></td>
    <td class="status-<?= $msg['status'] ?>">
        <?= ucfirst($msg['status']) ?>
    </td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>
