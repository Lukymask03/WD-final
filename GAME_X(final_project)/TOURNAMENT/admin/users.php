<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Absolute path to the project root (first GAME_X(final_project))
$projectRoot = realpath(__DIR__ . '/../../..'); // Adjust relative to users.php

// Include backend files
require_once $projectRoot . '/backend/db.php';
require_once $projectRoot . '/backend/helpers/auth_guard.php';
require_once $projectRoot . '/backend/helpers/log_activity.php';

// Protect page for admins only
checkAuth('admin');


// Get total users, active users, suspended users
$totalUsers   = $conn->query("SELECT COUNT(*) AS total FROM accounts")->fetch()['total'];
$activeUsers  = $conn->query("SELECT COUNT(*) AS active FROM accounts WHERE account_status = 'active'")->fetch()['active'];
$suspendedUsers = $conn->query("SELECT COUNT(*) AS suspended FROM accounts WHERE account_status = 'suspended'")->fetch()['suspended'];

// User role counts
$userTypes = $conn->query("SELECT role, COUNT(*) AS count FROM accounts GROUP BY role")->fetchAll(PDO::FETCH_ASSOC);

$roles = [];
$counts = [];
foreach ($userTypes as $row) {
    $roles[]  = ucfirst($row['role']);
    $counts[] = $row['count'];
}

// Log activity
$account_id = $_SESSION['account_id'] ?? 0;
logActivity($conn, $account_id, 'View Users', 'Admin viewed user analytics');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Users Management | Admin</title>
    <link rel="stylesheet" href="<?= $projectRoot ?>/assets/css/common.css"> 
    <link rel="stylesheet" href="<?= $projectRoot ?>/assets/css/admin_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --bg-main: #ffffff;
            --bg-secondary: #f5f6fa;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --accent: #3b82f6;
            --accent-hover: #2563eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--bg-secondary);
            color: var(--text-main);
        }

        main {
            margin-left: 260px;
            padding: 30px;
        }

        .dashboard-header {
            margin-bottom: 25px;
        }

        .dashboard-header h1 {
            font-size: 1.8rem;
            color: var(--text-main);
        }

        .dashboard-header p {
            color: var(--text-muted);
            margin-top: 5px;
            font-size: 0.95rem;
        }

        /* Cards Grid */
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .card h3 {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 10px;
        }

        .card p {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-main);
        }

        /* Chart Section */
        .chart-card {
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 25px;
        }

        .chart-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 20px;
        }

        #userChart {
            width: 100%;
            height: 350px;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            main {
                margin-left: 0;
                padding: 20px;
            }
        }

        @media (max-width: 768px) {
            .cards-container {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            }

            .card p {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <?php include '../includes/admin/sidebar.php'; ?>

    <main>
        <div class="dashboard-header">
            <h1>Users Overview</h1>
            <p>Manage and monitor platform user statistics.</p>
        </div>

        <!-- Summary Cards -->
        <div class="cards-container">
            <div class="card">
                <h3>Total Users</h3>
                <p><?= $totalUsers ?></p>
            </div>

            <div class="card">
                <h3>Active Users</h3>
                <p><?= $activeUsers ?></p>
            </div>

            <div class="card">
                <h3>Offline Users</h3>
                <p><?= $offlineUsers ?></p>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="chart-card">
            <h3>User Distribution</h3>
            <canvas id="userChart"></canvas>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('userChart');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: <?= json_encode($roles) ?>,
                datasets: [{
                    label: 'User Roles',
                    data: <?= json_encode($counts) ?>,
                    backgroundColor: ['#3b82f6', '#a855f7', '#f59e0b'],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Player and Organizer Distribution' }
                }
            }
        });
    </script>
</body>
</html>
