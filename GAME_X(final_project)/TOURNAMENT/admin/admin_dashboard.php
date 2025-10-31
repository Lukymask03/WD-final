<?php
require_once "../backend/helpers/auth_guard.php";
require_once "../backend/db.php";
require_once "../backend/helpers/log_activity.php";

// Only allow admins
checkAuth('admin');

// --- ADMIN INFO ---
$username = $_SESSION['user_name'] ?? 'Admin';

// --- FETCH DASHBOARD STATISTICS ---
try {
    // Total accounts
    $accounts = $conn->query("SELECT COUNT(*) FROM accounts")->fetchColumn();

    // Total tournaments
    $tournaments = $conn->query("SELECT COUNT(*) FROM tournaments")->fetchColumn();

    // Total audit logs
    $logs = $conn->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();

    // Total players
    $players = $conn->query("
        SELECT COUNT(*) 
        FROM user_roles ur
        JOIN roles r ON ur.role_id = r.role_id
        WHERE r.role_name = 'player'
    ")->fetchColumn();

    // Total organizers
    $organizers = $conn->query("
        SELECT COUNT(*) 
        FROM user_roles ur
        JOIN roles r ON ur.role_id = r.role_id
        WHERE r.role_name = 'organizer'
    ")->fetchColumn();

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Log dashboard visit
logActivity($_SESSION['user_id'], "View Dashboard", "Admin accessed the dashboard");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard | GameX</title>
  <link rel="stylesheet" href="../assets/css/common.css">
  <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>

<!-- === NAVBAR === -->
<header class="navbar">
  <a href="admin_dashboard.php" class="logo-link">
    <img src="../assets/images/logo.png" alt="GameX Logo" class="logo-img">
    <span>Game<span>X</span> Admin</span>
  </a>

  <nav>
    <a href="admin_dashboard.php" class="active">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="manage_tournaments.php">Manage Tournaments</a>
    <a href="view_logs.php">Audit Logs</a>
  </nav>

  <div class="nav-actions">
    <button id="darkModeToggle" class="darkmode-btn">Dark Mode</button>
    <a href="../auth/logout.php" class="btn">Logout</a>
  </div>
</header>

<!-- === MAIN CONTENT === -->
<main class="dashboard-container">
  <section class="welcome-box">
    <h1>Welcome, <span class="highlight"><?= htmlspecialchars($username) ?></span> 👋</h1>
    <p>Your role: <strong>Admin</strong></p>
  </section>

  <section class="stats-section">
    <div class="stat-card">
      <h2><?= htmlspecialchars($accounts) ?></h2>
      <p>Total Accounts</p>
    </div>
    <div class="stat-card">
      <h2><?= htmlspecialchars($players) ?></h2>
      <p>Total Players</p>
    </div>
    <div class="stat-card">
      <h2><?= htmlspecialchars($organizers) ?></h2>
      <p>Total Organizers</p>
    </div>
    <div class="stat-card">
      <h2><?= htmlspecialchars($tournaments) ?></h2>
      <p>Total Tournaments</p>
    </div>
    <div class="stat-card">
      <h2><?= htmlspecialchars($logs) ?></h2>
      <p>Audit Logs Recorded</p>
    </div>
  </section>

  <section class="info-section">
    <h2>Admin Controls</h2>
    <p>Use the navigation menu above to manage users, tournaments, and monitor system logs.</p>
  </section>
</main>

<!-- === FOOTER === -->
<footer class="footer">
  <p>© <?= date('Y') ?> GameX Tournament Platform. All rights reserved.</p>
</footer>

<script src="../assets/js/darkmode_toggle.js"></script>
<script src="../assets/js/index.js"></script>
</body>
</html>
