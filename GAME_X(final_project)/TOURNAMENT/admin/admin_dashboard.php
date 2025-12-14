<?php
require_once "../backend/helpers/auth_guard.php";
require_once "../backend/db.php";
require_once "../backend/helpers/log_activity.php";

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// Check if user is admin (is_admin = 1)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
  // Redirect based on role
  if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
      case 'organizer':
        header("Location: ../organizer/organizer_dashboard.php");
        break;
      case 'player':
        header("Location: ../player/player_dashboard.php");
        break;
      default:
        header("Location: ../auth/login.php");
        break;
    }
  } else {
    header("Location: ../auth/login.php");
  }
  exit;
}

// --- ADMIN INFO --- 
$username = $_SESSION['username'] ?? 'Admin';

// --- FETCH DASHBOARD STATISTICS ---
try {
  // Total accounts
  $accounts = $conn->query("SELECT COUNT(*) FROM accounts")->fetchColumn();

  // Total tournaments
  $tournaments = $conn->query("SELECT COUNT(*) FROM tournaments")->fetchColumn();

  // Total games
  $games = $conn->query("SELECT COUNT(*) FROM games")->fetchColumn();

  // Total audit logs (if table exists)
  $logs = 0;
  try {
    $logs = $conn->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
  } catch (PDOException $e) {
    // Audit logs table doesn't exist yet
  }

  // Total players
  $players = $conn->query("SELECT COUNT(*) FROM accounts WHERE role = 'player'")->fetchColumn();

  // Total organizers
  $organizers = $conn->query("SELECT COUNT(*) FROM accounts WHERE role = 'organizer'")->fetchColumn();

  // Active tournaments
  $active_tournaments = $conn->query("SELECT COUNT(*) FROM tournaments WHERE status = 'open'")->fetchColumn();

  // Recent registrations (last 7 days)
  $recent_users = $conn->query("SELECT COUNT(*) FROM accounts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();

  // Fetch recent activity
  $recent_activity = [];
  try {
    $stmt = $conn->query("SELECT al.*, a.username FROM audit_logs al 
                            JOIN accounts a ON al.account_id = a.account_id 
                            ORDER BY al.created_at DESC LIMIT 5");
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    // Audit logs table doesn't exist yet
  }

  // Fetch recent users
  $recent_accounts = $conn->query("SELECT username, email, role, created_at FROM accounts ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Log dashboard visit
if ($logs > 0) {
  logActivity($_SESSION['account_id'], "View Dashboard", "Admin accessed the dashboard");
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard | GameX</title>
  <link rel="stylesheet" href="../assets/css/common.css">
  <link rel="stylesheet" href="../assets/css/admin_dash.css">
  <link rel="stylesheet" href="../assets/css/admin_sidebar.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>

  <body>

    <?php include "../includes/admin/admin_header.php"; ?>
    <?php include "../includes/admin/sidebar.php"; ?>

    <!-- your page content below -->


    <!-- Main Content -->
    <div class="main-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="top-bar-left">
          <h2>Dashboard Overview</h2>
          <p><?= date('l, F j, Y') ?></p>
        </div>
        <div class="top-bar-right">
          <button id="darkModeToggle" class="top-bar-btn">
            <i class="fas fa-moon"></i>
            <span>Dark Mode</span>
          </button>
          <div class="user-profile">
            <div class="user-avatar"><?= strtoupper(substr($username, 0, 1)) ?></div>
            <div>
              <div style="font-weight: 600; font-size: 0.9rem;"><?= htmlspecialchars($username) ?></div>
              <div style="font-size: 0.75rem; color: var(--text-muted);">Administrator</div>
            </div>
          </div>
          <a href="../auth/logout.php" class="top-bar-btn">
            <i class="fas fa-sign-out-alt"></i>
          </a>
        </div>
      </div>

      <!-- Dashboard Content -->
      <div class="dashboard-content">
        <!-- Stats Grid -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-header">
              <div class="stat-title">Total Users</div>
              <div class="stat-icon blue">
                <i class="fas fa-users"></i>
              </div>
            </div>
            <div class="stat-value"><?= number_format($accounts) ?></div>
            <div class="stat-change positive">
              <i class="fas fa-arrow-up"></i>
              <span>+<?= $recent_users ?> this week</span>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-header">
              <div class="stat-title">Active Tournaments</div>
              <div class="stat-icon green">
                <i class="fas fa-trophy"></i>
              </div>
            </div>
            <div class="stat-value"><?= number_format($active_tournaments) ?></div>
            <div class="stat-change">
              <span><?= number_format($tournaments) ?> total</span>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-header">
              <div class="stat-title">Total Players</div>
              <div class="stat-icon orange">
                <i class="fas fa-user-friends"></i>
              </div>
            </div>
            <div class="stat-value"><?= number_format($players) ?></div>
            <div class="stat-change">
              <span><?= number_format($organizers) ?> organizers</span>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-header">
              <div class="stat-title">Games Available</div>
              <div class="stat-icon purple">
                <i class="fas fa-gamepad"></i>
              </div>
            </div>
            <div class="stat-value"><?= number_format($games) ?></div>
            <div class="stat-change">
              <span>Active games</span>
            </div>
          </div>
        </div>

        <!-- Content Grid -->
        <div class="content-grid">
          <!-- Recent Activity -->
          <div class="card">
            <div class="card-header">
              <div class="card-title">
                <i class="fas fa-history"></i>
                Recent Activity
              </div>
              <a href="audit_logs.php" class="card-action">View All →</a>
            </div>
            <div class="activity-list">
              <?php if (count($recent_activity) > 0): ?>
                <?php foreach ($recent_activity as $activity): ?>
                  <div class="activity-item">
                    <div class="activity-icon">
                      <i class="fas fa-user"></i>
                    </div>
                    <div class="activity-content">
                      <div class="activity-title"><?= htmlspecialchars($activity['username']) ?></div>
                      <div class="activity-desc"><?= htmlspecialchars($activity['action']) ?>: <?= htmlspecialchars($activity['details'] ?? '') ?></div>
                      <div class="activity-time">
                        <i class="far fa-clock"></i>
                        <?= date('M j, Y g:i A', strtotime($activity['created_at'])) ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="empty-state">
                  <i class="fas fa-inbox"></i>
                  <p>No recent activity</p>
                </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Recent Users -->
          <div class="card">
            <div class="card-header">
              <div class="card-title">
                <i class="fas fa-user-plus"></i>
                New Users
              </div>
              <a href="users.php" class="card-action">View All →</a>
            </div>
            <div class="users-list">
              <?php foreach ($recent_accounts as $account): ?>
                <div class="user-item">
                  <div class="user-item-avatar"><?= strtoupper(substr($account['username'], 0, 1)) ?></div>
                  <div class="user-item-info">
                    <div class="user-item-name"><?= htmlspecialchars($account['username']) ?></div>
                    <div class="user-item-email"><?= htmlspecialchars($account['email']) ?></div>
                  </div>
                  <div class="user-item-badge <?= $account['role'] ?>"><?= $account['role'] ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <i class="fas fa-bolt"></i>
              Quick Actions
            </div>
          </div>
          <div class="quick-actions">
            <a href="users.php" class="quick-action-btn">
              <i class="fas fa-users"></i>
              <span>User Management</span>
            </a>
            <a href="games.php" class="quick-action-btn">
              <i class="fas fa-gamepad"></i>
              <span>Manage Games</span>
            </a>
            <a href="tournaments.php" class="quick-action-btn">
              <i class="fas fa-trophy"></i>
              <span>View Tournaments</span>
            </a>
            <a href="audit_logs.php" class="quick-action-btn">
              <i class="fas fa-clipboard-list"></i>
              <span>System Logs</span>
            </a>
          </div>
        </div>

      </div>
    </div>

    <script src="../assets/js/darkmode.js"></script>
    <script src="../assets/js/index.js"></script>
  </body>

</html>