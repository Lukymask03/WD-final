<?php
session_start();
require_once "../backend/db.php"; 

// Restrict access to admin only
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch user counts
$playerCount = 0;
$organizerCount = 0;

try {
    $stmt = $conn->query("SELECT role, COUNT(*) as total FROM accounts WHERE is_admin = 0 GROUP BY role");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['role'] === 'player') $playerCount = $row['total'];
        elseif ($row['role'] === 'organizer') $organizerCount = $row['total'];
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// ✅ Correct include for sidebar
include('../includes/admin/sidebar.php');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management | Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background-color: #f4f5f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            color: #1f2937;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        .page-header {
            font-size: 28px;
            font-weight: 700;
            color: #ff6600;
            margin-bottom: 25px;
        }

        .card-container {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 25px;
            flex: 1;
            min-width: 280px;
        }

        .card h3 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .count {
            font-size: 40px;
            color: #ff6600;
            font-weight: 700;
            margin-top: 10px;
        }

        canvas {
            margin-top: 25px;
            max-width: 500px;
        }
    </style>
</head>
<body>

<?php include __DIR__ . '/../includes/admin/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header"><i class="fas fa-users"></i> User Management</div>

        <!-- Stats Overview -->
        <div class="card-container">
            <div class="card">
                <h3>Total Players</h3>
                <div class="count"><?= $playerCount ?></div>
            </div>
            <div class="card">
                <h3>Total Organizers</h3>
                <div class="count"><?= $organizerCount ?></div>
            </div>
            <div class="card">
                <h3>Total Users</h3>
                <div class="count"><?= $playerCount + $organizerCount ?></div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="card" style="margin-top: 30px;">
            <h3><i class="fas fa-chart-pie"></i> User Distribution</h3>
            <canvas id="userChart"></canvas>
        </div>
    </main>

    <script>
        const ctx = document.getElementById('userChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Players', 'Organizers'],
                datasets: [{
                    label: 'User Roles',
                    data: [<?= $playerCount ?>, <?= $organizerCount ?>],
                    backgroundColor: ['#ff6600', '#ffb347'],
                    borderColor: ['#ffffff', '#ffffff'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Distribution of User Roles' }
                }
            }
        });
    </script>

</body>
</html>
