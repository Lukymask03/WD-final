<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/helpers/auth_guard.php";

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Logs - Game X</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modern Organizer CSS -->
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
</head>

<body>
    <?php include '../includes/organizer/organizer_sidebar.php'; ?>

    <main class="org-main">
        <!-- Hero Section -->
        <section class="org-hero">
            <div class="org-hero-content">
                <div class="org-hero-badge">
                    <i class="fas fa-history"></i>
                    Activity Logs
                </div>
                <h1>Track Your Activities 📜</h1>
                <p>Monitor all your actions and system interactions - <?= htmlspecialchars($username) ?></p>
            </div>
        </section>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <div class="org-alert org-alert-error" style="margin-bottom: 30px;">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <section class="org-stats-grid" style="margin-bottom: 40px;">
            <div class="org-stat-card">
                <div class="org-stat-icon">
                    <i class="fas fa-list"></i>
                </div>
                <div class="org-stat-value"><?= number_format($totalLogs) ?></div>
                <div class="org-stat-label">Total Activities</div>
            </div>

            <div class="org-stat-card">
                <div class="org-stat-icon">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="org-stat-value"><?= $totalPages ?></div>
                <div class="org-stat-label">Total Pages</div>
            </div>

            <div class="org-stat-card">
                <div class="org-stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="org-stat-value"><?= count($logs) ?></div>
                <div class="org-stat-label">Current Page Logs</div>
            </div>
        </section>

        <!-- Logs Table -->
        <div class="org-table-container">
            <div class="org-table-header">
                <h3 class="org-table-title"><i class="fas fa-history"></i> Activity Log History</h3>
            </div>

            <?php if (!empty($logs)): ?>
                <table class="org-data-table">
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
                            $badgeClass = 'org-badge-info';
                            $action = strtolower($log['action']);
                            if (strpos($action, 'create') !== false) $badgeClass = 'org-badge-success';
                            elseif (strpos($action, 'update') !== false || strpos($action, 'edit') !== false) $badgeClass = 'org-badge-info';
                            elseif (strpos($action, 'delete') !== false) $badgeClass = 'org-badge-danger';
                            elseif (strpos($action, 'login') !== false) $badgeClass = 'org-badge-warning';
                            ?>
                            <tr>
                                <td><?= $offset + $index + 1 ?></td>
                                <td>
                                    <span class="org-badge <?= $badgeClass ?>">
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
                    <div class="org-pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=1" class="org-page-link">« First</a>
                            <a href="?page=<?= $page - 1 ?>" class="org-page-link">‹ Previous</a>
                        <?php else: ?>
                            <span class="org-page-link" style="opacity: 0.5; cursor: not-allowed;">« First</span>
                            <span class="org-page-link" style="opacity: 0.5; cursor: not-allowed;">‹ Previous</span>
                        <?php endif; ?>

                        <span class="org-page-active">Page <?= $page ?> of <?= $totalPages ?></span>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>" class="org-page-link">Next ›</a>
                            <a href="?page=<?= $totalPages ?>" class="org-page-link">Last »</a>
                        <?php else: ?>
                            <span class="org-page-link" style="opacity: 0.5; cursor: not-allowed;">Next ›</span>
                            <span class="org-page-link" style="opacity: 0.5; cursor: not-allowed;">Last »</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <div class="org-empty-state">
                    <i class="fas fa-history"></i>
                    <h3>No Activity Logs Found</h3>
                    <p>Your activities will appear here once you start using the system.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>