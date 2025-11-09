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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
/* ===== Admin Dashboard Reports UI - Orange Accent ===== */
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

/* Sidebar icon fix */
.sidebar i, .sidebar svg {
    color: #1f2937; /* dark gray icons for visibility */
}

/* Optional: hover effect for better UX */
.sidebar a:hover i {
    color: #ff9f43; /* orange accent on hover */
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

/* Column widths (optional, adjust if needed) */
table th:nth-child(1), td:nth-child(1) { width: 5%; }
table th:nth-child(2), td:nth-child(2) { width: 12%; }
table th:nth-child(3), td:nth-child(3) { width: 18%; }
table th:nth-child(4), td:nth-child(4) { width: 35%; }
table th:nth-child(5), td:nth-child(5) { width: 10%; }
table th:nth-child(6), td:nth-child(6) { width: 15%; }
table th:nth-child(7), td:nth-child(7) { width: 5%; }

/* Action button - orange gradient */
.resolve-btn {
    background: linear-gradient(135deg, #ff5e00, #ff9f43);
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
}

.resolve-btn:hover {
    background: linear-gradient(135deg, #e05500, #e68a33);
}

/* Filter dropdown styling */
.filter select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    background-color: #fff;
    cursor: pointer;
    font-family: inherit;
    font-size: 14px;
}

/* Responsive for smaller screens */
@media (max-width: 1024px) {
    .main-content {
        margin-left: 0;
        padding: 15px;
    }
}
</style>

</head>
<body>
<?php require_once "../includes/admin/sidebar.php"; ?>

<div class="main-content">
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

    <div class="table-card">
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
    </div>
</div>
</body>
</html>
