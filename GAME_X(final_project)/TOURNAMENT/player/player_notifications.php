<?php
session_start();
require_once "../backend/db.php";

$account_id = $_SESSION["account_id"];

$stmt = $conn->prepare("
    SELECT *
    FROM notifications
    WHERE account_id = ?
    ORDER BY created_at DESC
");
$stmt->execute([$account_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark all as read
$conn->prepare("UPDATE notifications SET is_read = 1 WHERE account_id = ?")
     ->execute([$account_id]);
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Notifications</title>
    <link rel="stylesheet" href="../assets/css/common.css">
</head>
<body>

<?php include "../includes/player/player_nav.php"; ?>
<?php include "../includes/player/player_sidebar.php"; ?>


<div class="layout">
<div class="player-content">

<div class="main-content">
    <h1>My Notifications</h1>

    <?php if (empty($notifications)): ?>
        <p>No notifications yet.</p>
    <?php else: ?>
        <?php foreach ($notifications as $n): ?>
            <div class="notif-card">
                <h3><?= htmlspecialchars($n['title']) ?></h3>
                <p><?= htmlspecialchars($n['message']) ?></p>
                <small><?= $n['created_at'] ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</div>
</div>

</body>
</html>
