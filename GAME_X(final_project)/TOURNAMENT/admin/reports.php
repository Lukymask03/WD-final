<?php
require_once "../backend/db.php";
session_start();

// Optional: ensure admin is logged in
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header("Location: ../auth/login.php");
//     exit();
// }

// Handle marking report as resolved
if (isset($_POST['resolve_id'])) {
    $id = $_POST['resolve_id'];
    $stmt = $conn->prepare("UPDATE reports SET status = 'Resolved' WHERE id = ?");
    $stmt->execute([$id]);
}

// Filter reports
$statusFilter = $_GET['status'] ?? 'All';
if ($statusFilter === 'Pending') {
    $stmt = $conn->query("SELECT * FROM reports WHERE status = 'Pending' ORDER BY created_at DESC");
} elseif ($statusFilter === 'Resolved') {
    $stmt = $conn->query("SELECT * FROM reports WHERE status = 'Resolved' ORDER BY created_at DESC");
} else {
    $stmt = $conn->query("SELECT * FROM reports ORDER BY created_at DESC");
}

$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Reports</title>
<link rel="stylesheet" href="../assets/css/common.css">
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f8;
        margin: 20px;
    }
    h2 {
        color: #333;
        margin-bottom: 10px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    th, td {
        border: 1px solid #ddd;
        padding: 10px;
        text-align: left;
    }
    th {
        background: #222;
        color: white;
    }
    tr:nth-child(even) {
        background: #f9f9f9;
    }
    form {
        display: inline;
    }
    .filter {
        margin-bottom: 15px;
    }
    .resolve-btn {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 5px;
    }
    .resolve-btn:hover {
        background-color: #45a049;
    }
</style>
</head>
<body>

<h2>Organizer Reports</h2>

<div class="filter">
    <form method="get" action="">
        <label for="status">Filter by Status:</label>
        <select name="status" id="status" onchange="this.form.submit()">
            <option value="All" <?= $statusFilter === 'All' ? 'selected' : '' ?>>All</option>
            <option value="Pending" <?= $statusFilter === 'Pending' ? 'selected' : '' ?>>Pending</option>
            <option value="Resolved" <?= $statusFilter === 'Resolved' ? 'selected' : '' ?>>Resolved</option>
        </select>
    </form>
</div>

<table>
    <tr>
        <th>ID</th>
        <th>Organizer ID</th>
        <th>Title</th>
        <th>Details</th>
        <th>Status</th>
        <th>Date</th>
        <th>Action</th>
    </tr>

    <?php if (empty($reports)): ?>
        <tr><td colspan="7" style="text-align:center;">No reports found</td></tr>
    <?php else: ?>
        <?php foreach ($reports as $report): ?>
        <tr>
            <td><?= htmlspecialchars($report['id']) ?></td>
            <td><?= htmlspecialchars($report['organizer_id']) ?></td>
            <td><?= htmlspecialchars($report['title']) ?></td>
            <td><?= htmlspecialchars($report['details']) ?></td>
            <td><?= htmlspecialchars($report['status']) ?></td>
            <td><?= htmlspecialchars($report['created_at']) ?></td>
            <td>
                <?php if ($report['status'] === 'Pending'): ?>
                    <form method="post" action="">
                        <input type="hidden" name="resolve_id" value="<?= $report['id'] ?>">
                        <button type="submit" class="resolve-btn">Mark Resolved</button>
                    </form>
                <?php else: ?>
                    ✅
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

</body>
</html>
