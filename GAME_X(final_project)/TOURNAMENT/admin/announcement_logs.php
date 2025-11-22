<?php
session_start();

// Restrict admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . "/../backend/db.php";

// Fetch logs
$stmt = $conn->query("
    SELECT al.*, a.username AS admin_name
    FROM announcement_logs al
    JOIN accounts a ON al.admin_id = a.account_id
    ORDER BY al.sent_at DESC
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Announcement Logs</title>

<!-- Main admin styles -->
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="stylesheet" href="../assets/css/admin_sidebar.css">
<link rel="stylesheet" href="../assets/css/common.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
/* Match admin theme */
body {
    background: var(--bg-secondary);
    font-family: Arial, sans-serif;
}

.main-container {
    margin-left: 260px; 
    padding: 30px;
}

/* Table container */
.table-container {
    background: var(--bg-main);
    padding: 25px;
    border-radius: 12px;
    box-shadow: var(--shadow);
    border: 1px solid var(--border);
}

/* Title */
h1 {
    color: var(--accent);
    margin-bottom: 20px;
    font-size: 26px;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
}

th {
    background: var(--accent);
    color: white;
    padding: 12px;
    font-size: 14px;
    text-align: left;
    border-right: 1px solid rgba(255,255,255,0.2);
}

td {
    padding: 12px;
    border-bottom: 1px solid var(--border);
    color: var(--text-main);
}

tr:hover {
    background: var(--highlight-cyan);
    color: black;
}

/* View button */
.view-btn {
    background: var(--accent);
    border: none;
    padding: 8px 14px;
    color: white;
    border-radius: 5px;
    cursor: pointer;
    font-size: 13px;
}
.view-btn:hover {
    background: var(--accent-hover);
}

/* Modal */
.modal-bg {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
}

.modal-box {
    background: var(--bg-main);
    border: 1px solid var(--border);
    width: 500px;
    max-width: 90%;
    padding: 20px;
    margin: 80px auto;
    border-radius: 10px;
    box-shadow: var(--shadow);
}

.close-btn {
    background: rgb(255, 70, 70);
    color: white;
    padding: 8px 14px;
    float: right;
    border-radius: 6px;
    cursor: pointer;
    border: none;
}
</style>

</head>
<body>

<?php include "../includes/admin/sidebar.php"; ?>
<?php include "../includes/admin/admin_header.php"; ?>

<div class="main-container">
    <h1>📜 Announcement Logs</h1>

    <div class="table-container">
        <table>
            <tr>
                <th>ID</th>
                <th>Admin</th>
                <th>Type</th>
                <th>Title</th>
                <th>Sent At</th>
                <th>View</th>
            </tr>

            <?php if (count($logs) > 0): ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= $log['id'] ?></td>
                    <td><?= htmlspecialchars($log['admin_name']) ?></td>
                    <td><?= ucfirst($log['type']) ?></td>
                    <td><?= htmlspecialchars($log['title']) ?></td>
                    <td><?= $log['sent_at'] ?></td>
                    <td>
                        <button class="view-btn" onclick="openModal(`<?= htmlspecialchars($log['content'], ENT_QUOTES) ?>`)">
                            View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>

            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; padding:20px; color:var(--text-muted);">
                        No announcements found.
                    </td>
                </tr>
            <?php endif; ?>

        </table>
    </div>
</div>

<!-- MODAL -->
<div class="modal-bg" id="modal">
    <div class="modal-box">
        <button class="close-btn" onclick="closeModal()">Close</button>
        <h2 style="color:var(--accent);">Announcement Message</h2>
        <p id="modal-content" style="white-space: pre-line; color:var(--text-main);"></p>
    </div>
</div>

<script>
function openModal(message) {
    document.getElementById("modal-content").innerText = message;
    document.getElementById("modal").style.display = "block";
}
function closeModal() {
    document.getElementById("modal").style.display = "none";
}
</script>

</body>
</html>
