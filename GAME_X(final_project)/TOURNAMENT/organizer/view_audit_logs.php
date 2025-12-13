<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/helpers/auth_guard.php";

// Ensure only organizers can access this page
checkAuth('organizer');

// Pagination settings
$limit = 20; // Records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

try {
    // Get total count of logs for this organizer
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM activity_logs
        WHERE account_id = ?
    ");
    $countStmt->execute([$_SESSION['account_id']]);
    $totalLogs = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalLogs / $limit);

    // Fetch activity logs for this organizer
    $stmt = $conn->prepare("
        SELECT 
            log_id,
            action,
            description,
            created_at
        FROM activity_logs
        WHERE account_id = ?
        ORDER BY created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$_SESSION['account_id'], $limit, $offset]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $logs = [];
    $totalPages = 0;
    $error = "Error fetching activity logs: " . $e->getMessage();
}

$username = $_SESSION['username'] ?? 'Organizer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activity Logs | GameX</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <style>
        body {
            background: var(--bg-secondary, #f5f5f5);
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header.navbar {
            background: var(--bg-main, white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 30px;
            border-bottom: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        header.navbar .logo-link {
            display: flex;
            align-items: center;
        }

        header.navbar h2 {
            color: var(--accent, #ff6600);
            margin-left: 10px;
        }

        nav a {
            margin: 0 10px;
            text-decoration: none;
            color: var(--text-main, #333);
            font-weight: 600;
        }

        nav a:hover {
            color: var(--accent-hover, #e65c00);
        }

        .btn {
            background: var(--accent, #ff6600);
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .btn:hover {
            background: var(--accent-hover, #e65c00);
        }

        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-header h1 {
            margin: 0 0 10px 0;
            color: var(--accent, #ff6600);
        }

        .page-header p {
            margin: 0;
            color: #666;
        }

        .logs-table-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .logs-table {
            width: 100%;
            border-collapse: collapse;
        }

        .logs-table thead {
            background: var(--accent, #ff6600);
            color: white;
        }

        .logs-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .logs-table td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .logs-table tbody tr:hover {
            background: #f9f9f9;
        }

        .logs-table tbody tr:last-child td {
            border-bottom: none;
        }

        .action-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .action-badge.create {
            background: #4CAF50;
            color: white;
        }

        .action-badge.update {
            background: #2196F3;
            color: white;
        }

        .action-badge.delete {
            background: #f44336;
            color: white;
        }

        .action-badge.view {
            background: #9E9E9E;
            color: white;
        }

        .action-badge.login {
            background: #FF9800;
            color: white;
        }

        .no-logs {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
            padding: 20px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
        }

        .pagination a:hover {
            background: var(--accent, #ff6600);
            color: white;
            border-color: var(--accent, #ff6600);
        }

        .pagination .current {
            background: var(--accent, #ff6600);
            color: white;
            border-color: var(--accent, #ff6600);
        }

        .pagination .disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            margin: 0 0 10px 0;
            color: var(--accent, #ff6600);
            font-size: 32px;
        }

        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <header class="navbar">
        <div class="logo-link">
            <img src="../assets/images/game_x_logo.png" alt="GameX Logo" style="height: 40px; vertical-align: middle;">
            <h2>GameX Organizer</h2>
        </div>

        <nav>
            <a href="organizer_dashboard.php">Dashboard</a>
            <a href="create_tournament.php">Create Tournament</a>
            <a href="view_tournaments.php">Manage Tournaments</a>
            <a href="manage_brackets.php">Manage Brackets</a>
        </nav>

        <div class="nav-actions">
            <a href="../auth/logout.php" class="btn">Logout</a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>📜 Activity Logs</h1>
            <p>Track your actions and login history - <?= htmlspecialchars($username) ?></p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?= number_format($totalLogs) ?></h3>
                <p>Total Activities</p>
            </div>
            <div class="stat-card">
                <h3><?= $totalPages ?></h3>
                <p>Total Pages</p>
            </div>
            <div class="stat-card">
                <h3><?= count($logs) ?></h3>
                <p>Logs on This Page</p>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="logs-table-container">
            <?php if (!empty($logs)): ?>
                <table class="logs-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $index => $log): ?>
                            <?php
                            // Determine badge class based on action
                            $badgeClass = 'view';
                            $action = strtolower($log['action']);
                            if (strpos($action, 'create') !== false) $badgeClass = 'create';
                            elseif (strpos($action, 'update') !== false || strpos($action, 'edit') !== false) $badgeClass = 'update';
                            elseif (strpos($action, 'delete') !== false) $badgeClass = 'delete';
                            elseif (strpos($action, 'login') !== false) $badgeClass = 'login';
                            ?>
                            <tr>
                                <td><?= $offset + $index + 1 ?></td>
                                <td>
                                    <span class="action-badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($log['description']) ?></td>
                                <td><?= date('M d, Y h:i A', strtotime($log['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1">« First</a>
                            <a href="?page=<?= $page - 1 ?>">‹ Previous</a>
                        <?php else: ?>
                            <span class="disabled">« First</span>
                            <span class="disabled">‹ Previous</span>
                        <?php endif; ?>

                        <span class="current">Page <?= $page ?> of <?= $totalPages ?></span>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>">Next ›</a>
                            <a href="?page=<?= $totalPages ?>">Last »</a>
                        <?php else: ?>
                            <span class="disabled">Next ›</span>
                            <span class="disabled">Last »</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-logs">
                    <h2>No Activity Logs Found</h2>
                    <p>Your activities will appear here once you start using the system.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>