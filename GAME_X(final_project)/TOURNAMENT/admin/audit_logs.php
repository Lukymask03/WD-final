<?php
session_start();

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

require_once __DIR__ . "/../backend/db.php";

$search = isset($_GET['search']) ? trim($_GET['search']) : "";

// --------------------------------------------------
// FIXED: PDO search query (no more parameter error)
// --------------------------------------------------
if ($search !== "") {

    $sql = "
        SELECT 
            al.id,
            a.username,
            a.role,
            al.action,
            al.details,
            al.created_at
        FROM audit_logs al
        LEFT JOIN accounts a ON a.account_id = al.account_id
        WHERE a.username LIKE :search
           OR al.action LIKE :search
           OR al.details LIKE :search
        ORDER BY al.created_at DESC
    ";

    $stmt = $conn->prepare($sql);

    // one bind, used for all 3 LIKE clauses
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);

    $stmt->execute();
} else {

    $stmt = $conn->query("
        SELECT 
            al.id,
            a.username,
            a.role,
            al.action,
            al.details,
            al.created_at
        FROM audit_logs al
        LEFT JOIN accounts a ON a.account_id = al.account_id
        ORDER BY al.created_at DESC
    ");
}

$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Audit Logs | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <style>
        .main-content {
            margin-left: 260px;
            padding: 30px 40px;
            background-color: var(--bg-secondary);
            min-height: 100vh;
        }

        h2 {
            color: var(--accent);
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            text-align: center;
            margin-bottom: 25px;
        }

        input[type="text"] {
            width: 300px;
            padding: 10px;
            border: 2px solid var(--border);
            border-radius: 6px;
            background: var(--bg-main);
            color: var(--text-main);
        }

        button {
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 10px 18px;
            margin-left: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: var(--accent-hover);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: var(--bg-main);
            color: var(--text-main);
        }

        th,
        td {
            border: 1px solid var(--border);
            padding: 12px;
            text-align: left;
        }

        th {
            background: var(--accent);
            color: white;
        }

        tr:nth-child(even) {
            background: var(--bg-secondary);
        }

        tr:hover {
            background: var(--highlight-cyan);
            color: #000;
            transition: 0.3s;
        }

        .footer {
            text-align: center;
            padding: 15px;
            margin-top: 30px;
            color: var(--text-muted);
            border-top: 2px solid var(--border);
        }
    </style>
</head>

<body>
    <?php include "../includes/admin/admin_header.php"; ?>
    <?php include "../includes/admin/sidebar.php"; ?>

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