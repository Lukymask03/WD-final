<?php
// Ensure user is authenticated
if (!isset($_SESSION['account_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Get user's first letter for avatar
$avatar_letter = strtoupper(substr($_SESSION['username'] ?? 'P', 0, 1));
?>

<aside class="org-sidebar">
    <!-- Brand Logo -->
    <div class="org-brand">
        <a href="../index.php" class="org-brand-link">
            <img src="../assets/images/logo.png" alt="Game X Logo" class="org-logo" onerror="this.style.display='none'">
            <div class="org-brand-text">
                <h1><span class="org-brand-orange">GAME</span> <span class="org-brand-red">X</span></h1>
                <div class="org-brand-subtitle">Player Portal</div>
            </div>
        </a>
    </div>

    <!-- Profile Section -->
    <div class="org-profile">
        <div class="org-profile-avatar">
            <?php echo $avatar_letter; ?>
        </div>
        <div class="org-profile-info">
            <h4><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
            <div class="org-profile-role">
                <i class="fas fa-user"></i> Player
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="org-nav">
        <!-- Overview Section -->
        <div class="org-nav-section">
            <div class="org-nav-title">
                <i class="fas fa-home"></i> Overview
            </div>
            <a href="player_dashboard.php" class="org-nav-item <?php echo $current_page == 'player_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Tournament Section -->
        <div class="org-nav-section">
            <div class="org-nav-title">
                <i class="fas fa-trophy"></i> Tournaments
            </div>
            <a href="register_tournament.php" class="org-nav-item <?php echo $current_page == 'register_tournament.php' ? 'active' : ''; ?>">
                <i class="fas fa-clipboard-list"></i>
                <span>Register Tournament</span>
            </a>
            <a href="tournament_player.php" class="org-nav-item <?php echo $current_page == 'tournament_player.php' ? 'active' : ''; ?>">
                <i class="fas fa-gamepad"></i>
                <span>My Tournaments</span>
            </a>
        </div>

        <!-- Teams Section -->
        <div class="org-nav-section">
            <div class="org-nav-title">
                <i class="fas fa-users"></i> Teams
            </div>
            <a href="teams.php" class="org-nav-item <?php echo $current_page == 'teams.php' ? 'active' : ''; ?>">
                <i class="fas fa-compass"></i>
                <span>Browse Teams</span>
            </a>
            <a href="my_teams.php" class="org-nav-item <?php echo $current_page == 'my_teams.php' ? 'active' : ''; ?>">
                <i class="fas fa-shield-alt"></i>
                <span>My Teams</span>
            </a>
            <a href="invitations.php" class="org-nav-item <?php echo $current_page == 'invitations.php' ? 'active' : ''; ?>">
                <i class="fas fa-envelope"></i>
                <span>Invitations</span>
            </a>
        </div>

        <!-- Notifications Section -->
        <div class="org-nav-section">
            <div class="org-nav-title">
                <i class="fas fa-bell"></i> Activity
            </div>
            <a href="player_notifications.php" class="org-nav-item <?php echo $current_page == 'player_notifications.php' ? 'active' : ''; ?>">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
        </div>

        <!-- Support Section -->
        <div class="org-nav-section">
            <div class="org-nav-title">
                <i class="fas fa-life-ring"></i> Support
            </div>
            <a href="player_contact.php" class="org-nav-item <?php echo $current_page == 'player_contact.php' ? 'active' : ''; ?>">
                <i class="fas fa-headset"></i>
                <span>Contact Support</span>
            </a>
        </div>
    </nav>

    <!-- Footer/Logout -->
    <div class="org-sidebar-footer" style="padding: 1rem; border-top: 1px solid rgba(255, 255, 255, 0.05);">
        <a href="../auth/logout.php" class="org-nav-item logout-btn" style="margin: 0 0.75rem;">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>