<?php
// Always start session first
session_start();

// Restrict admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// Load database + notification engine
require_once __DIR__ . "/../backend/db.php";
require_once __DIR__ . "/../backend/notifications/notification_engine.php";

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    $type    = $_POST['type'];   // system_update OR tournament_update

    if (empty($title) || empty($content)) {
        $error = "Please fill out all fields.";
    } else {

        /* ======================================================
            SEND NOTIFICATION TO ALL PLAYERS
        ====================================================== */

        if ($type === "system_update") {

            // SYSTEM ANNOUNCEMENT
            triggerNotification("system_update", [
                "title"   => $title,
                "content" => $content
            ]);

        } else {

            // TOURNAMENT ANNOUNCEMENT
            triggerNotification("tournament_created", [
                "title"       => $title,
                "description" => $content
            ]);
        }

        /* ======================================================
            SAVE ANNOUNCEMENT LOG IN DATABASE
        ====================================================== */

        $stmt = $conn->prepare("
            INSERT INTO announcement_logs (admin_id, type, title, content)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $_SESSION['account_id'], // admin who sent it
            $type,
            $title,
            $content
        ]);

        /* ======================================================
            SUCCESS MESSAGE
        ====================================================== */
        $success = "Announcement sent to all players successfully!";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Announcements</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin_form.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>


    .main-content {
        margin-left: 260px; /* Match sidebar width */
        padding: 40px;
        background: var(--bg-secondary);
        min-height: 100vh;
    }

    .announcement-container {
        max-width: 650px;
        margin: auto;
        padding: 30px 40px;
        background: var(--bg-main);
        border-radius: 14px;
        box-shadow: 0px 5px 20px rgba(0,0,0,0.08);
    }

    .announcement-container h2 {
        text-align: center;
        color: var(--accent);
        margin-bottom: 25px;
        font-size: 24px;
        font-weight: bold;
    }

    label {
        font-weight: 600;
        margin-top: 15px;
        display: block;
        color: var(--text-main);
    }

    select, input, textarea {
        width: 100%;
        padding: 12px;
        margin-top: 6px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--bg-secondary);
        color: var(--text-main);
        font-size: 15px;
    }

    textarea {
        resize: none;
        height: 130px;
    }

    .success {
        background: #d9f8d6;
        color: #1b7d2c;
        padding: 12px 15px;
        border: 1px solid #8bd28a;
        border-radius: 8px;
        margin-bottom: 15px;
    }

    button {
        margin-top: 20px;
        width: 100%;
        padding: 12px;
        background: var(--accent);
        border: none;
        color: white;
        border-radius: 8px;
        cursor: pointer;
        font-size: 16px;
        font-weight: bold;
        transition: 0.3s;
    }

    button:hover {
        background: var(--accent-hover);
    }


    .logout-btn {
    margin-right: 30px; /* Move it slightly left */
}

.logout-btn a {
    padding: 10px 18px;
    background: var(--accent);
    color: white;
    border-radius: 8px;
    font-weight: bold;
}

.logout-btn a:hover {
    background: var(--accent-hover);
}

.main-container {
    margin-left: 270px;       /* because sidebar = 270px */
    margin-top: 85px;         /* push under admin header */
    padding: 30px;
    font-family: Arial;
}

.announcement-form {
    background: #ffffff;
    padding: 20px;
    width: 500px;
    border-radius: 10px;
    box-shadow: 0px 3px 10px rgba(0,0,0,0.1);
}

.announcement-form input,
.announcement-form textarea,
.announcement-form select {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

.send-btn {
    padding: 12px;
    background: #ff5733;
    color: white;
    border: none;
    width: 100%;
    font-size: 16px;
    border-radius: 6px;
    cursor: pointer;
}

.alert {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 6px;
}
.alert.success { background: #c6ffce; color: #1b7d2b; }
.alert.error { background: #ffc6c6; color: #7d2b2b; }
</style>
</head>

<body>

<!-- ⭐ NEW ADMIN HEADER -->
<?php include "../includes/admin/admin_header.php"; ?>

<!-- ⭐ ADMIN SIDEBAR -->
<?php include "../includes/admin/sidebar.php"; ?>

<!-- ⭐ PAGE CONTENT -->

<div class="main-content">

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
        <textarea name="message" placeholder="Write your announcement..." required></textarea>

        <button type="submit" name="send_announcement">Send Announcement</button>
    </form>
</div>

</div>

</body>
</html>
