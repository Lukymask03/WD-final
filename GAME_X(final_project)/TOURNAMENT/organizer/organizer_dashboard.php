<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/helpers/auth_guard.php";
require_once "../backend/helpers/log_activity.php";

//  Ensure only organizers can access this page
checkAuth('organizer');

try {
    // Fetch organizer profile info
    $stmt = $conn->prepare("
        SELECT organizer_id, organization, contact_no, website 
        FROM organizer_profiles 
        WHERE account_id = ?
    ");
    $stmt->execute([$_SESSION['account_id']]);
    $organizer = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Log the activity
logActivity($_SESSION['account_id'], "View Dashboard", "Organizer accessed the dashboard");

// Store username for display
$username = $_SESSION['username'] ?? 'Organizer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Organizer Dashboard | GameX</title>
  <link rel="stylesheet" href="../assets/css/common.css" />
  <link rel="stylesheet" href="../assets/css/organizer_dashboard.css" />
  <style>
    /* --- INLINE DASHBOARD STYLING (can be moved to CSS) --- */
    body {
      background: var(--bg-secondary);
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    header.navbar {
      background: var(--bg-main);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 30px;
      border-bottom: 1px solid #ddd;
    }

    header.navbar h2 {
      color: var(--accent);
      margin-left: 10px;
    }

    nav a {
      margin: 0 10px;
      text-decoration: none;
      color: var(--text-main);
      font-weight: 600;
    }

    nav a:hover {
      color: var(--accent-hover);
    }

    .dashboard-container {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .welcome-card {
      background: var(--bg-main);
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 20px;
    }

    .info-card {
      background: var(--bg-main);
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }

    .cta-btn {
      display: inline-block;
      background: var(--accent);
      color: white;
      padding: 10px 15px;
      border-radius: 8px;
      text-decoration: none;
      margin-top: 10px;
      font-weight: 600;
    }

    .cta-btn:hover {
      background: var(--accent-hover);
    }

    .footer {
      text-align: center;
      padding: 20px;
      color: var(--text-muted);
      margin-top: 40px;
      border-top: 1px solid #ddd;
    }

    .darkmode-btn {
      background: var(--accent);
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 600;
    }

    .darkmode-btn:hover {
      background: var(--accent-hover);
    }
  </style>
</head>

<body>
  <!-- ==== NAVIGATION BAR ==== -->
  <header class="navbar">
    <div class="logo-link">
      <img src="../assets/images/game_x_logo.png" alt="GameX Logo" class="logo-img" style="height: 40px; vertical-align: middle;">
      <h2>GameX Organizer</h2>
    </div>

<nav>
    <a href="organizer_dashboard.php">Dashboard</a>
    <a href="create_tournament.php">Create Tournament</a>
    <a href="view_tournaments.php">Manage Tournaments</a> <!-- correct link -->
    <a href="select_tournament.php">Manage Brackets</a> <!-- separate, safe -->
</nav>

          <div class="nav-actions">
           <a href="../auth/logout.php" class="btn">Logout</a>
        </div>
  </header>

  <!-- ==== DASHBOARD CONTENT ==== -->
  <main class="dashboard-container">
    <!-- Welcome Section -->
    <section class="welcome-card">
      <h1>Welcome, <?= htmlspecialchars($username) ?> 👋</h1>
      <p>Manage your tournaments, brackets, and matches easily from your dashboard.</p>
    </section>

    <!-- Info Grid -->
    <section class="info-grid">
      <!-- Organizer Info -->
      <div class="info-card">
        <h2>Your Organization</h2>
        <p><strong><?= htmlspecialchars($organizer['organization'] ?? 'GAME X') ?></strong></p>
        <p>📞 <?= htmlspecialchars($organizer['contact_no'] ?? '091234567890') ?></p>
        <p>🌐 <?= htmlspecialchars($organizer['website'] ?? 'GAME X.com') ?></p>
      </div>

      <!-- Quick Actions -->
      <div class="info-card">
        <h2>Quick Actions</h2>
        <a href="create_tournament.php" class="cta-btn">+ Create Tournament</a>
        <a href="view_tournaments.php" class="cta-btn">📋  Manage Tournaments</a>
        <a href="select_tournament.php" class="cta-btn">🏆 Manage Brackets</a>
        <a href="manage_matches.php" class="cta-btn">⚔️ Manage Matches</a>
      </div>

      <!-- Activity Log -->
      <div class="info-card">
        <h2>Activity Log</h2>
        <p>Track your latest actions and login history.</p>
        <a href="../admin/view_audit_logs.php" class="cta-btn">📜 View Logs</a>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="footer">
    <p>&copy; <?= date('Y') ?> GameX Tournament System. All rights reserved.</p>
  </footer>

  
</body>
</html>
