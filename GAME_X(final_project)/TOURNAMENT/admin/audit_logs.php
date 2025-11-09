<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../backend/db.php";
require_once "../backend/functions.php";

// Restrict access to admin only
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// Search + filter
$search = $_GET['search'] ?? '';
$query = "
    SELECT al.*, a.username, a.role
    FROM audit_logs al
    JOIN accounts a ON al.account_id = a.account_id
    WHERE a.username LIKE :search_username
       OR al.action LIKE :search_action
       OR al.details LIKE :search_details
    ORDER BY al.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->execute([
    'search_username' => "%$search%",
    'search_action'   => "%$search%",
    'search_details'  => "%$search%"
]);

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* Shift content to the right of sidebar */
        .main-content {
            margin-left: 260px; /* Adjust to match sidebar width */
            padding: 30px 40px;
            background-color: var(--bg-secondary);
            min-height: 100vh;
        }

        h2 { 
            color: var(--accent); 
            text-align:center; 
            margin-bottom:20px; 
        }

        form { 
            text-align:center; 
            margin-bottom:25px; 
        }

        input[type="text"] { 
            width:300px; 
            padding:10px; 
            border:2px solid var(--border); 
            border-radius:6px; 
            background: var(--bg-main); 
            color: var(--text-main); 
        }

        button { 
            background:var(--accent); 
            color:white; 
            border:none; 
            border-radius:6px; 
            padding:10px 18px; 
            margin-left:5px; 
            cursor:pointer; 
            transition:0.3s; 
        }

        button:hover { 
            background: var(--accent-hover); 
        }

        table { 
            width:100%; 
            border-collapse:collapse; 
            margin-top:15px; 
            background: var(--bg-main); 
            color: var(--text-main); 
        }

        th, td { 
            border:1px solid var(--border); 
            padding:12px; 
            text-align:left; 
        }

        th { 
            background: var(--accent); 
            color:white; 
        }

        tr:nth-child(even) { 
            background: var(--bg-secondary); 
        }

        tr:hover { 
            background: var(--highlight-cyan); 
            color:#000; 
            transition:0.3s; 
        }

        .footer { 
            text-align:center; 
            padding:15px; 
            margin-top:30px; 
            color: var(--text-muted); 
            border-top:2px solid var(--border); 
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php require_once "../includes/admin/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="main-content">
        <h2>System Audit Logs</h2>

        <form method="GET">
            <input type="text" name="search" placeholder="Search logs..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($logs) > 0): ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['id']) ?></td>
                            <td><?= htmlspecialchars($log['username']) ?></td>
                            <td><?= htmlspecialchars($log['role']) ?></td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                            <td><?= htmlspecialchars($log['details'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($log['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center; color: var(--text-muted);">
                            No audit logs found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <footer class="footer">
        <p>&copy; <?= date("Y"); ?> Game X Community Admin Panel. All rights reserved.</p>
    </footer>

    <script src="../assets/js/darkmode.js"></script>
</body>
</html>
