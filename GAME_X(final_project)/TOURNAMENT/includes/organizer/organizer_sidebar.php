<?php
// Ensure user is authenticated
if (!isset($_SESSION['account_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get current page
$current_page = basename($_SERVER['PHP_SELF']);

// Get organizer profile
try {
    $stmt = $conn->prepare("SELECT * FROM organizer_profiles WHERE account_id = ?");
    $stmt->execute([$_SESSION['account_id']]);
    $sidebarProfile = $stmt->fetch();

    // If profile doesn't exist, create one
    if (!$sidebarProfile) {
        $stmt = $conn->prepare("INSERT INTO organizer_profiles (account_id, organization_name) VALUES (?, ?)");
        $stmt->execute([$_SESSION['account_id'], $_SESSION['username'] . "'s Organization"]);

        $stmt = $conn->prepare("SELECT * FROM organizer_profiles WHERE account_id = ?");
        $stmt->execute([$_SESSION['account_id']]);
        $sidebarProfile = $stmt->fetch();
    }
} catch (PDOException $e) {
    // Fallback profile
    $sidebarProfile = [
        'organization_name' => $_SESSION['username'] . "'s Organization",
        'contact_email' => $_SESSION['email'] ?? ''
    ];
}

// Get user's first letter for avatar
$avatar_letter = strtoupper(substr($_SESSION['username'] ?? 'O', 0, 1));
?>

<aside class="org-sidebar">
    <!-- Brand Logo -->
    <div class="org-brand">
        <a href="../index.php" class="org-brand-link">
            <img src="../assets/images/logo.png" alt="Game X Logo" class="org-logo" onerror="this.style.display='none'">
            <div class="org-brand-text">
                <h1><span class="org-brand-orange">GAME</span> <span class="org-brand-red">X</span></h1>
                <div class="org-brand-subtitle">Organizer Portal</div>
            </div>
        </a>
    </div>

    <!-- Profile Section -->
    <div class="org-profile">
        <div class="org-profile-avatar">
            <?php echo $avatar_letter; ?>
        </div>
        <div class="org-profile-info">
            <h4><?php echo htmlspecialchars($sidebarProfile['organization_name'] ?? $_SESSION['username']); ?></h4>
            <div class="org-profile-role">
                <i class="fas fa-shield-alt"></i> Organizer
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="org-nav">
        <!-- Overview Section -->
        <div class="org-nav-section">
            <div class="org-nav-title">
                <i class="fas fa-chart-line"></i> Overview
            </div>
            <a href="organizer_dashboard.php" class="org-nav-item <?php echo $current_page == 'organizer_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </div>

        <!-- Tournament Management -->
        <div class="org-nav-section">
            <div class="org-nav-title">
                <i class="fas fa-trophy"></i> Tournament Management
            </div>
            <a href="create_tournament.php" class="org-nav-item <?php echo $current_page == 'create_tournament.php' ? 'active' : ''; ?>">
                <i class="fas fa-plus-circle"></i>
                <span>Create Tournament</span>
            </a>
            <a href="view_tournaments.php" class="org-nav-item <?php echo $current_page == 'view_tournaments.php' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i>
                <span>View Tournaments</span>
            </a>
            <a href="manage_brackets.php" class="org-nav-item <?php echo $current_page == 'manage_brackets.php' ? 'active' : ''; ?>">
                <i class="fas fa-project-diagram"></i>
                <span>Manage Brackets</span>
            </a>
            <a href="manage_matches.php" class="org-nav-item <?php echo $current_page == 'manage_matches.php' ? 'active' : ''; ?>">
                <i class="fas fa-gamepad"></i>
                <span>Manage Matches</span>
            </a>
        </div>

        <!-- Analytics & Reports -->
        <div class="org-nav-section">
            <div class="org-nav-title">
                <i class="fas fa-chart-bar"></i> Analytics & Reports
            </div>
            <a href="organizer_reports.php" class="org-nav-item <?php echo $current_page == 'organizer_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>Reports</span>
            </a>
            <a href="view_audit_logs.php" class="org-nav-item <?php echo $current_page == 'view_audit_logs.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>Audit Logs</span>
            </a>
        </div>

        <!-- Settings & Profile -->
        <div class="org-nav-section">
            <div class="org-nav-title">
                <i class="fas fa-cog"></i> Settings
            </div>
            <a href="edit_profile.php" class="org-nav-item <?php echo $current_page == 'edit_profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-edit"></i>
                <span>Edit Profile</span>
            </a>
            <a href="submit_report.php" class="org-nav-item <?php echo $current_page == 'submit_report.php' ? 'active' : ''; ?>">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Submit Report</span>
            </a>
        </div>
    </nav>

    <!-- Footer/Logout -->
    <div class="org-sidebar-footer">
        <a href="../auth/logout.php" class="org-logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</aside>