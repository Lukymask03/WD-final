<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Restrict access to organizer only
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../auth/login.php");
    exit();
}

// Get current page name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="../assets/css/admin_sidebar.css"> <!-- you can reuse the same CSS -->

<!-- Organizer Sidebar -->
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="organizer_dashboard.php" class="sidebar-logo">
            <img src="../assets/images/game_x_logo.png" alt="GameX Logo">
            <h1><span class="highlight-orange">GAME</span><span class="highlight-red"> X</span></h1>
        </a>
    </div>

    <nav class="sidebar-menu">
        <!-- Main Section -->
        <div class="menu-section">
            <div class="menu-section-title">Main</div>
            <a href="organizer_dashboard.php" class="menu-item <?= $current_page === 'organizer_dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Tournament Management Section -->
        <div class="menu-section">
            <div class="menu-section-title">Tournaments</div>
            <a href="create_tournament.php" class="menu-item <?= $current_page === 'create_tournament.php' ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Create Tournament</span>
            </a>
            <a href="view_tournaments.php" class="menu-item <?= $current_page === 'view_tournaments.php' ? 'active' : '' ?>">
                <i class="fas fa-trophy"></i>
                <span>Manage Tournaments</span>
            </a>
            <a href="select_tournament.php" class="menu-item <?= $current_page === 'select_tournament.php' ? 'active' : '' ?>">
                <i class="fas fa-list-ol"></i>
                <span>Manage Brackets</span>
            </a>
        </div>

        <!-- System Section (Optional) -->
        <div class="menu-section">
            <div class="menu-section-title">System</div>
            <a href="organizer_reports.php" class="menu-item <?= $current_page === 'organizer_reports.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </div>
    </nav>
</aside>
