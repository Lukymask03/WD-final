//sidebar.php (this is the php of the admin_sidebar)
<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Restrict access to admin only
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../auth/login.php");
    exit();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="../assets/css/admin_sidebar.css">

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="admin_dashboard.php" class="sidebar-logo">
            <img src="../assets/images/game_x_logo.png" alt="GameX Logo">
            <h1><span class="highlight-orange">GAME</span><span class="highlight-red"> X</span></h1>
        </a>
    </div>

    <nav class="sidebar-menu">
        <!-- Main Section -->
        <div class="menu-section">
            <div class="menu-section-title">Main</div>
            <a href="admin_dashboard.php" class="menu-item <?= $current_page === 'admin_dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Management Section -->
        <div class="menu-section">
            <div class="menu-section-title">Management</div>
            <a href="users.php" class="menu-item <?= $current_page === 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="tournaments.php" class="menu-item <?= $current_page === 'tournaments.php' ? 'active' : '' ?>">
                <i class="fas fa-trophy"></i>
                <span>Tournaments</span>
            </a>
            <a href="games.php" class="menu-item <?= $current_page === 'games.php' ? 'active' : '' ?>">
                <i class="fas fa-gamepad"></i>
                <span>Games</span>
            </a>
            <a href="messages.php" class="menu-item <?= $current_page === 'messages.php' ? 'active' : '' ?>">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
            </a>
        </div>

        <!-- System Section -->
       
<div class="menu-section">
    <div class="menu-section-title">System</div>
    
      <a href="audit_logs.php" class="menu-item <?= $current_page === 'audit_logs.php' ? 'active' : '' ?>">
         <i class="fas fa-clipboard-list"></i>
         <span>Audit Logs</span>
      </a>

       <a href="reports.php" class="menu-item <?= $current_page === 'reports.php' ? 'active' : '' ?>">
          <i class="fas fa-chart-bar"></i>
          <span>Reports</span>
       </a>

        <a href="send_announcement.php" class="menu-item <?= $current_page === 'send_announcement.php' ? 'active' : '' ?>">
           <i class="fas fa-bullhorn"></i>
           <span>Send Announcement</span>
        </a>

       <a href="admin_announcement.php" class="menu-item <?= $current_page === 'admin_announcement.php' ? 'active' : '' ?>">
        <i class="fas fa-bullhorn"></i>
        <span>Announcements</span>
       </a>
       
       <a href="announcement_logs.php" class="menu-item <?= $current_page === 'announcement_logs.php' ? 'active' : '' ?>">
          <i class="fas fa-history"></i>
          <span>Announcement Logs</span>
       </a>
    
      </div>
    </nav>
</aside>
