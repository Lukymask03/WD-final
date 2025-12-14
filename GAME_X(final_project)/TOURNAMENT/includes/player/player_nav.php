<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'player') {
    header("Location: ../auth/login.php");
    exit();
}
?>

<!-- Nav styling is handled by organizer_modern.css -->

<nav class="org-topbar">
    <div class="org-topbar-left">
        <button class="org-menu-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="org-topbar-title">Player Panel</h1>
    </div>

    <div class="org-topbar-right">
        <!-- Notifications -->
        <div class="org-notification">
            <button class="org-notif-btn" id="notif-bell">
                <i class="fas fa-bell"></i>
                <span class="org-notif-badge" id="notif-count">0</span>
            </button>
            <div class="org-notif-dropdown" id="notif-dropdown">
                <div class="org-notif-header">
                    <h4>Notifications</h4>
                </div>
                <div class="org-notif-list" id="notif-list"></div>
                <a href="player_notifications.php" class="org-notif-view-all">View All</a>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="org-profile-dropdown">
            <button class="org-profile-btn">
                <span><?= htmlspecialchars($_SESSION['username'] ?? 'Player') ?></span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="org-profile-menu">
                <a href="../auth/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<script src="../assets/js/player_notifications.js"></script>