<?php
require_once "../backend/db.php";
session_start();

// Handle admin reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $id = $_POST['id'];
    $reply = $_POST['reply_message'];

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

// Fetch all messages
$stmt = $conn->query("SELECT * FROM messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Inbox | GameX</title>
<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
/* ===== Admin Dashboard Table UI - Orange Accent ===== */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f5f7;
    margin: 0;
    color: #1f2937;
}

/* Offset content for sidebar */
.main-content {
    margin-left: 250px; /* match sidebar width */
    padding: 20px;
}

/* Page heading */
h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #1f2937;
}

/* Table card container */
.table-card {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    display: block;
    overflow-x: auto;
    white-space: nowrap;
}

th, td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    text-align: left;
    vertical-align: top;
}

/* Orange gradient for table header */
th {
    background: linear-gradient(135deg, #ff5e00, #ff9f43); /* orange gradient */
    color: #fff;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 2;
}

tr:nth-child(even) {
    background-color: #f9fafb;
}

/* Column widths */
table th:nth-child(1), td:nth-child(1) { width: 12%; }
table th:nth-child(2), td:nth-child(2) { width: 18%; }
table th:nth-child(3), td:nth-child(3) { width: 30%; }
table th:nth-child(4), td:nth-child(4) { width: 25%; }
table th:nth-child(5), td:nth-child(5) { width: 10%; }
table th:nth-child(6), td:nth-child(6) { width: 5%; }

/* Textarea for reply */
textarea {
    width: 100%;
    height: 60px;
    padding: 8px;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    font-family: inherit;
    resize: none;
    margin-bottom: 5px;
}

/* Reply button with orange accent */
button {
    background: linear-gradient(135deg, #ff5e00, #ff9f43);
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
}

button:hover {
    background: linear-gradient(135deg, #e05500, #e68a33);
}

/* Status colors */
.status-new { color: #ef4444; font-weight: bold; }
.status-replied { color: #10b981; font-weight: bold; }

/* Responsive table scroll */
@media (max-width: 1024px) {
    .main-content {
        margin-left: 0;
        padding: 15px;
    }
}
</style>

</head>
<body>
<?php include "../includes/admin/admin_header.php"; ?>
<?php require_once "../includes/admin/sidebar.php"; ?>


 <div class="main-content">
    <h2>📬 Admin Inbox</h2>

    <div class="table-card">
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
                <strong>Replied:</strong> <?= nl2br(htmlspecialchars($msg['reply_message'])) ?>
                <br><small>at <?= $msg['replied_at'] ?></small>
            <?php endif; ?>
        </td>

        <td><?= $msg['created_at'] ?></td>
        <td class="status-<?= $msg['status'] ?>"><?= ucfirst($msg['status']) ?></td>
    </tr>
    <?php endforeach; ?>
    </table>
    </div>
</div>


</body>
</html>
