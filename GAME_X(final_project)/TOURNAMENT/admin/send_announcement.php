<?php
session_start();

// Admin restriction
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// Load DB + Notification Engine
require_once __DIR__ . "/../backend/db.php";
require_once __DIR__ . "/../backend/notifications/notification_engine.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $type    = $_POST['announcement_type'];
    $title   = trim($_POST['title']);
    $message = trim($_POST['message']);

    if ($type === "system") {

        triggerNotification("system_update", [
            "title"   => $title,
            "content" => $message
        ]);

        $_SESSION["success_message"] = "System announcement sent.";

    } else if ($type === "tournament") {

        triggerNotification("tournament_created", [
            "title"       => $title,
            "description" => $message
        ]);

        $_SESSION["success_message"] = "Tournament announcement sent.";
    }

    header("Location: admin_announcement.php");
    exit;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Send Announcement</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .announcement-container {
            max-width: 650px;
            margin: auto;
            padding: 25px;
            background: #ffffff;
            margin-top: 50px;
            border-radius: 10px;
            box-shadow: 0 0 10px #aaa;
        }
        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }
        button {
            padding: 12px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .success {
            background: #dff0d8;
            color: #3c763d;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<?php include "../includes/admin/admin_header.php"; ?>
<?php include "../includes/admin/sidebar.php"; ?>


<div class="announcement-container">
    <h2>📢 Send Announcement</h2>

    <?php if (isset($_SESSION["success_message"])): ?>
        <div class="success">
            <?= $_SESSION["success_message"]; ?>
        </div>
    <?php unset($_SESSION["success_message"]); endif; ?>

    <form method="POST">
        <label>Announcement Type</label>
        <select name="announcement_type" required>
            <option value="system">System Update</option>
            <option value="tournament">Tournament Announcement</option>
        </select>

        <label>Title</label>
        <input type="text" name="title" placeholder="Enter announcement title..." required>

        <label>Message</label>
        <textarea name="message" placeholder="Write your announcement..." rows="6" required></textarea>

        <br><br>
        <button type="submit" name="send_announcement">Send Announcement</button>
    </form>
</div>

</body>
</html>
