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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <style>
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
    /* Top Bar */
    .top-bar {
      background: var(--bg-main);
      border-bottom: 1px solid var(--border);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .top-bar-left h2 {
      font-size: 1.5rem;
      color: var(--text-main);
    }

    .top-bar-left p {
      color: var(--text-muted);
      font-size: 0.9rem;
      margin-top: 5px;
    }

    .top-bar-right {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .top-bar-btn {
      background: var(--bg-secondary);
      border: 1px solid var(--border);
      padding: 8px 16px;
      border-radius: 8px;
      color: var(--text-main);
      cursor: pointer;
      transition: all 0.3s;
      font-weight: 500;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .top-bar-btn:hover {
      background: var(--accent);
      color: white;
      border-color: var(--accent);
    }

    .user-profile {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 8px 12px;
      background: var(--bg-secondary);
      border-radius: 8px;
      border: 1px solid var(--border);
    }

    .user-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--accent-hover));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
    }

    /* Dashboard Content */
    .dashboard-content {
      padding: 30px;
    }

    /* Stats Grid */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: var(--bg-main);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
      transition: all 0.3s;
      position: relative;
      overflow: hidden;
    }

    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(180deg, var(--accent), var(--accent-hover));
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .stat-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .stat-title {
      color: var(--text-muted);
      font-size: 0.85rem;
      font-weight: 600;
      text-transform: uppercase;
    }

    .stat-icon {
      width: 45px;
      height: 45px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
    }

    .stat-icon.blue {
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
    }

    .stat-icon.green {
      background: rgba(34, 197, 94, 0.1);
      color: #22c55e;
    }

    .stat-icon.orange {
      background: rgba(255, 94, 0, 0.1);
      color: var(--accent);
    }

    .stat-icon.purple {
      background: rgba(168, 85, 247, 0.1);
      color: #a855f7;
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 700;
      color: var(--text-main);
      margin-bottom: 5px;
    }

    .stat-change {
      font-size: 0.85rem;
      color: var(--text-muted);
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .stat-change.positive {
      color: #22c55e;
    }

    .stat-change.negative {
      color: #ef4444;
    }

    /* Content Grid */
    .content-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 20px;
      margin-bottom: 30px;
    }

    /* Cards */
    .card {
      background: var(--bg-main);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 20px;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--border);
    }

    .card-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: var(--text-main);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .card-action {
      color: var(--accent);
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 500;
      transition: opacity 0.3s;
    }

    .card-action:hover {
      opacity: 0.7;
    }

    /* Activity List */
    .activity-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }

    .activity-item {
      display: flex;
      gap: 15px;
      padding: 12px;
      background: var(--bg-secondary);
      border-radius: 8px;
      transition: all 0.3s;
    }

    .activity-item:hover {
      background: var(--border);
    }

    .activity-icon {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--accent-hover));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .activity-content {
      flex: 1;
    }

    .activity-title {
      font-weight: 600;
      color: var(--text-main);
      margin-bottom: 3px;
    }

    .activity-desc {
      font-size: 0.85rem;
      color: var(--text-muted);
    }

    .activity-time {
      font-size: 0.75rem;
      color: var(--text-muted);
      margin-top: 5px;
    }

    /* Users List */
    .users-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .user-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 10px;
      background: var(--bg-secondary);
      border-radius: 8px;
      transition: all 0.3s;
    }

    .user-item:hover {
      background: var(--border);
    }

    .user-item-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent), var(--accent-hover));
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      flex-shrink: 0;
    }

    .user-item-info {
      flex: 1;
    }

    .user-item-name {
      font-weight: 600;
      color: var(--text-main);
      margin-bottom: 2px;
    }

    .user-item-email {
      font-size: 0.85rem;
      color: var(--text-muted);
    }

    .user-item-badge {
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
    }

    .user-item-badge.player {
      background: rgba(59, 130, 246, 0.1);
      color: #3b82f6;
    }

    .user-item-badge.organizer {
      background: rgba(168, 85, 247, 0.1);
      color: #a855f7;
    }

    /* Quick Actions */
    .quick-actions {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
    }

    .quick-action-btn {
      background: var(--bg-main);
      border: 2px dashed var(--border);
      border-radius: 12px;
      padding: 20px;
      text-align: center;
      text-decoration: none;
      color: var(--text-main);
      transition: all 0.3s;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }

    .quick-action-btn:hover {
      border-color: var(--accent);
      background: var(--bg-secondary);
    }

    .quick-action-btn i {
      font-size: 2rem;
      color: var(--accent);
    }

    .quick-action-btn span {
      font-weight: 600;
    }

    /* Responsive */
    @media (max-width: 1024px) {
      .content-grid {
        grid-template-columns: 1fr;
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }

      .sidebar.active {
        transform: translateX(0);
      }

      .main-content {
        margin-left: 0;
      }

      .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      }
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 40px 20px;
      color: var(--text-muted);
    }

    .empty-state i {
      font-size: 3rem;
      margin-bottom: 15px;
      opacity: 0.5;
    }
  </style>
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