<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/functions.php";

//Restrict access to admin or organizer
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'organizer'])) {
    header("Location: ../index.php");
    exit();
}

// Search + filter
$search = $_GET['search'] ?? '';
$query = "
    SELECT al.*, a.username, a.role
    FROM audit_logs al
    JOIN accounts a ON al.account_id = a.account_id
    WHERE a.username LIKE :search
       OR al.action LIKE :search
       OR al.details LIKE :search
    ORDER BY al.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->execute(['search' => "%$search%"]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs | Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .container {
            background-color: var(--bg-secondary);
            color: var(--text-main);
            border-radius: 12px;
            padding: 25px;
            margin: 40px auto;
            width: 90%;
            box-shadow: 0 0 15px rgba(0,0,0,0.15);
        }
        h2 {
            color: var(--accent);
            text-align: center;
            margin-bottom: 15px;
        }
        form {
            text-align: center;
            margin-bottom: 20px;
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
            transition: background 0.3s ease;
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
        th, td {
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
    <!--  NAVBAR -->
    <header class="navbar">
        <div class="logo">
            <a href="admin_dashboard.php" class="logo-link">
                <img src="../assets/images/game_x_logo.png" alt="Game X Community" class="logo-img">
                <h1><span class="highlight-orange">GAME</span><span class="highlight-red"> X</span> Admin</h1>
            </a>
        </div>

        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="tournaments.php">Tournaments</a>
            <a href="messages.php">Messages</a>
            <a href="audit_logs.php" class="active">Audit Logs</a>
            <a href="reports.php">Reports</a>
        </nav>

        <div class="nav-actions">
            <button id="darkModeToggle" class="darkmode-btn">Dark Mode</button>
            <a href="../auth/logout.php" class="cta-btn">Logout</a>
        </div>
    </header>

    <!--  MAIN CONTENT -->
    <div class="container">
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
                            <td><?= htmlspecialchars($log['log_id']) ?></td>
                            <td><?= htmlspecialchars($log['username']) ?></td>
                            <td><?= htmlspecialchars($log['role']) ?></td>
                            <td><?= htmlspecialchars($log['action']) ?></td>
                            <td><?= htmlspecialchars($log['details'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($log['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; color: var(--text-muted);">No audit logs found.</td></tr>
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
